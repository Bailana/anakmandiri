<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Notifications\LoginAttemptNotification;
use App\Mail\LoginAttemptMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Services\ActivityService;
use App\Mail\ResetPasswordMail;

class AuthController extends Controller
{
  // Show login form
  public function showLogin()
  {
    return view('content.authentications.auth-login-basic');
  }

  // Handle login request
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    // Prevent login for users whose related Karyawan/Konsultan records are non-active
    try {
      $email = $request->input('email');
      $userForCheck = User::where('email', $email)->first();
      if ($userForCheck) {
        // Check konsultan status when present
        if ($userForCheck->role === 'konsultan') {
          $kons = $userForCheck->konsultan;
          if ($kons && !empty($kons->status_hubungan)) {
            $s = strtolower(trim($kons->status_hubungan));
            if (stripos($s, 'non') !== false || (stripos($s, 'tidak') !== false && stripos($s, 'aktif') !== false)) {
              return back()->withErrors(['email' => 'Akun konsultan Anda tidak aktif. Silakan hubungi administrator.'])->onlyInput('email');
            }
          }
        }

        // Check karyawan status when present
        $karyawan = \App\Models\Karyawan::where('user_id', $userForCheck->id)->first();
        if ($karyawan && !empty($karyawan->status_kepegawaian)) {
          $s2 = strtolower(trim($karyawan->status_kepegawaian));
          if (stripos($s2, 'non') !== false || (stripos($s2, 'tidak') !== false && stripos($s2, 'aktif') !== false)) {
            return back()->withErrors(['email' => 'Akun karyawan Anda tidak aktif. Silakan hubungi administrator.'])->onlyInput('email');
          }
        }
      }
    } catch (\Exception $e) {
      // ignore check errors and proceed to normal auth attempt
    }

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
      $request->session()->regenerate();

      // Log login activity
      ActivityService::logLogin();

      // Reset failed login attempts on successful login
      try {
        $user = Auth::user();
        if ($user) {
          Cache::forget('login_attempts:' . $user->id);
        }
      } catch (\Exception $e) {
        // ignore cache errors
      }

      $user = Auth::user();
      switch ($user->role) {
        case 'admin':
          return redirect()->route('dashboard-admin');
        case 'guru':
          return redirect()->route('dashboard-guru');
        case 'terapis':
          return redirect()->route('dashboard-terapis');
        case 'konsultan':
          return redirect()->route('dashboard-konsultan');
        default:
          Auth::logout();
          return back()->withErrors(['email' => 'Role tidak dikenali.'])->onlyInput('email');
      }
    }

    // Failed login: increment attempt counter and notify if threshold reached
    $email = $request->input('email');
    $user = User::where('email', $email)->first();
    if ($user) {
      $key = 'login_attempts:' . $user->id;
      $attempts = Cache::get($key, 0) + 1;
      Cache::put($key, $attempts, now()->addMinutes(60));

      if ($attempts >= 3) {
        try {
          // create a password reset token so user can quickly reset if needed
          $token = Password::broker()->createToken($user);
          $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

          // Send as Mailable with embedded image (CID) for better email client compatibility
          Mail::to($user->email)->send(new LoginAttemptMail($user->name ?? $user->email, $resetUrl, now()));

          // keep Notification in place as fallback (non-blocking)
          try {
            $user->notify(new LoginAttemptNotification($request->ip(), now(), $token, $user->email));
          } catch (\Exception $e) {
            // ignore
          }
        } catch (\Exception $e) {
          // ignore notification errors
        }
        // reset counter after notifying
        Cache::forget($key);
      }
    }

    return back()->withErrors([
      'email' => 'Email atau password salah.',
    ])->onlyInput('email');
  }

  // Handle logout
  public function logout(Request $request)
  {
    // Log logout activity for current user
    ActivityService::logLogout();

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
  }

  // Show forgot password form
  public function showForgotPassword()
  {
    return view('content.authentications.auth-forgot-password-basic');
  }

  // Send reset link email
  public function sendResetLinkEmail(Request $request)
  {
    $request->validate(['email' => 'required|email']);

    $email = $request->input('email');
    $user = User::where('email', $email)->first();

    if (!$user) {
      return back()->withErrors(['email' => 'Email tidak ditemukan atau terjadi kesalahan.']);
    }

    try {
      $token = Password::broker()->createToken($user);
      $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

      Mail::to($user->email)->send(new ResetPasswordMail($user->name ?? $user->email, $resetUrl, now()));

      return back()->with(['status' => 'Kami telah mengirimkan link reset password ke email Anda.']);
    } catch (\Exception $e) {
      // fallback to default broker sendResetLink if something goes wrong
      $status = Password::sendResetLink($request->only('email'));
      return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => 'Kami telah mengirimkan link reset password ke email Anda.'])
        : back()->withErrors(['email' => 'Email tidak ditemukan atau terjadi kesalahan.']);
    }
  }

  // Show reset password form
  public function showResetPassword(Request $request, $token)
  {
    return view('content.authentications.auth-reset-password', [
      'token' => $token,
      'email' => $request->email
    ]);
  }

  // Handle reset password
  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', 'min:8', 'confirmed'],
    ], [
      'token.required' => 'Token reset password tidak ditemukan.',
      'email.required' => 'Email wajib diisi.',
      'email.email' => 'Format email tidak valid.',
      'password.required' => 'Password wajib diisi.',
      'password.min' => 'Password minimal 8 karakter.',
      'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function ($user, $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    if ($status === Password::PASSWORD_RESET) {
      // Ensure the current session is logged out so guest routes (login) are reachable
      try {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
      } catch (\Exception $e) {
        // ignore logout errors
      }

      return redirect()->route('login')->with('status', 'Password berhasil diubah, silakan login dengan password baru Anda.');
    }

    return back()->withErrors(['email' => ['Terjadi kesalahan saat reset password. Silakan coba lagi.']]);
  }
}
