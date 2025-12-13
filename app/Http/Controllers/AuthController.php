<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

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

      return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
      'email' => 'Email atau password salah.',
    ])->onlyInput('email');
  }

  // Handle logout
  public function logout(Request $request)
  {
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
      ? back()->with(['status' => __($status)])
      : back()->withErrors(['email' => __($status)]);
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
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
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
      ? redirect()->route('login')->with('status', __($status))
      : back()->withErrors(['email' => [__($status)]]);
  }
}
