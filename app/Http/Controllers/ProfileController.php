<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityService;

class ProfileController extends Controller
{
  /**
   * Show the profile edit form
   */
  public function show()
  {
    $user = Auth::user();
    return view('content.profile.profile-settings', ['user' => $user]);
  }

  /**
   * Update user profile
   */
  public function update(Request $request)
  {
    $user = Auth::user();

    // Validate based on role
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:users,email,' . $user->id,
      'phone' => 'nullable|string|max:20',
      'address' => 'nullable|string|max:255',
      'city' => 'nullable|string|max:100',
      'state' => 'nullable|string|max:100',
      'zip_code' => 'nullable|string|max:10',
      'country' => 'nullable|string|max:100',
      'bio' => 'nullable|string|max:500',
      'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Handle avatar upload
    if ($request->hasFile('avatar')) {
      // Delete old avatar if exists
      if ($user->avatar && \Storage::exists('public/' . $user->avatar)) {
        \Storage::delete('public/' . $user->avatar);
      }

      // Store new avatar
      $path = $request->file('avatar')->store('avatars', 'public');
      $validated['avatar'] = $path;
    }

    // Update user
    $user->update($validated);

    // Log activity
    ActivityService::logUpdate('User', $user->id, 'Mengupdate profil user: ' . $user->name);

    return redirect()->route('profile.show')
      ->with('success', 'Profil berhasil diperbarui');
  }

  /**
   * Update password
   */
  public function updatePassword(Request $request)
  {
    $user = Auth::user();

    $validated = $request->validate([
      'current_password' => 'required',
      'new_password' => 'required|string|min:8|confirmed',
    ]);

    // Check current password
    if (!Hash::check($validated['current_password'], $user->password)) {
      return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai']);
    }

    // Update password
    $user->update([
      'password' => Hash::make($validated['new_password']),
    ]);

    // Log activity
    ActivityService::logUpdate('User', $user->id, 'Mengubah password user: ' . $user->name);

    return back()->with('success', 'Password berhasil diperbarui');
  }
}
