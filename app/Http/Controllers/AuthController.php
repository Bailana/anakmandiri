<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Services\ActivityService;

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

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
      $request->session()->regenerate();

      // Log login activity
      ActivityService::logLogin();

      return redirect('/dashboard');
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

    $status = Password::sendResetLink(
      $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
      ? back()->with(['status' => 'Kami telah mengirimkan link reset password ke email Anda.'])
      : back()->withErrors(['email' => 'Email tidak ditemukan atau terjadi kesalahan.']);
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

    return $status === Password::PASSWORD_RESET
      ? redirect()->route('login')->with('status', 'Password berhasil diubah, silakan login dengan password baru Anda.')
      : back()->withErrors(['email' => ['Terjadi kesalahan saat reset password. Silakan coba lagi.']]);
  }
}
