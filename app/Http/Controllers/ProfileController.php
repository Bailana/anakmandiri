<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Konsultan;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Schema;
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
    // Try to load related konsultan / karyawan records for this user (prefer user_id when column exists)
    if (Schema::hasColumn('konsultans', 'user_id')) {
      $konsultan = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
    } else {
      $konsultan = Konsultan::where('email', $user->email)->first();
    }

    if (Schema::hasColumn('karyawans', 'user_id')) {
      $karyawan = Karyawan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
    } else {
      $karyawan = Karyawan::where('email', $user->email)->first();
    }

    return view('content.profile.profile-settings', ['user' => $user, 'konsultan' => $konsultan, 'karyawan' => $karyawan]);
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

    // Validate nested konsultan/karyawan data if present
    $konsultanRules = [
      'konsultan.nama' => 'nullable|string|max:191',
      'konsultan.nik' => 'nullable|string|max:100',
      'konsultan.jenis_kelamin' => 'nullable|string|max:20',
      'konsultan.tanggal_lahir' => 'nullable|date',
      'konsultan.tempat_lahir' => 'nullable|string|max:191',
      'konsultan.alamat' => 'nullable|string|max:255',
      'konsultan.no_telepon' => 'nullable|string|max:50',
      'konsultan.email' => 'nullable|email|max:255',
      'konsultan.spesialisasi' => 'nullable|string|max:255',
      'konsultan.bidang_keahlian' => 'nullable|string|max:255',
      'konsultan.sertifikasi' => 'nullable|string|max:255',
      'konsultan.pengalaman_tahun' => 'nullable|integer',
      'konsultan.pendidikan_terakhir' => 'nullable|string|max:255',
      'konsultan.institusi_pendidikan' => 'nullable|string|max:255',
    ];

    $karyawanRules = [
      'karyawan.nama' => 'nullable|string|max:191',
      'karyawan.nik' => 'nullable|string|max:100',
      'karyawan.nip' => 'nullable|string|max:100',
      'karyawan.jenis_kelamin' => 'nullable|string|max:20',
      'karyawan.tanggal_lahir' => 'nullable|date',
      'karyawan.tempat_lahir' => 'nullable|string|max:191',
      'karyawan.alamat' => 'nullable|string|max:255',
      'karyawan.no_telepon' => 'nullable|string|max:50',
      'karyawan.email' => 'nullable|email|max:255',
      'karyawan.posisi' => 'nullable|string|max:191',
      'karyawan.departemen' => 'nullable|string|max:191',
      'karyawan.status_kepegawaian' => 'nullable|string|max:191',
      'karyawan.tanggal_bergabung' => 'nullable|date',
      'karyawan.pendidikan_terakhir' => 'nullable|string|max:255',
      'karyawan.institusi_pendidikan' => 'nullable|string|max:255',
    ];

    $request->validate(array_merge($konsultanRules, $karyawanRules));

    // Handle avatar upload
    if ($request->hasFile('avatar')) {
      // Delete old avatar if exists
      if ($user->avatar && \Storage::exists('public/' . $user->avatar)) {
        \Storage::delete('public/' . $user->avatar);
      }

      // Store new avatar
      $path = $request->file('avatar')->store('avatars', 'public');
      $validated['avatar'] = $path;
      // Keep avatar path to also persist into related Konsultan/Karyawan records
      $avatarPath = $path;
    }

    // Update user
    $user->update($validated);

    // Update or create Konsultan / Karyawan data when provided
    $konsultanData = $request->input('konsultan', []);
    $karyawanData = $request->input('karyawan', []);

    // Load related models (prefer user_id when column exists)
    if (Schema::hasColumn('konsultans', 'user_id')) {
      $konsultan = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
    } else {
      $konsultan = Konsultan::where('email', $user->email)->first();
    }

    if (Schema::hasColumn('karyawans', 'user_id')) {
      $karyawan = Karyawan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
    } else {
      $karyawan = Karyawan::where('email', $user->email)->first();
    }

    // If top-level fields were used to populate the form (name/email/phone/address),
    // copy them into konsultan/karyawan payloads when those records should be updated.
    // Do not overwrite explicit nested inputs.
    $topName = $request->input('name');
    $topEmail = $request->input('email');
    $topPhone = $request->input('phone');
    $topAddress = $request->input('address');

    $konsultanData = array_merge([
      'nama' => null,
      'email' => null,
      'no_telepon' => null,
      'alamat' => null,
    ], $konsultanData);
    if (empty($konsultanData['nama']) && $topName) $konsultanData['nama'] = $topName;
    if (empty($konsultanData['email']) && $topEmail) $konsultanData['email'] = $topEmail;
    if (empty($konsultanData['no_telepon']) && $topPhone) $konsultanData['no_telepon'] = $topPhone;
    if (empty($konsultanData['alamat']) && $topAddress) $konsultanData['alamat'] = $topAddress;

    $karyawanData = array_merge([
      'nama' => null,
      'email' => null,
      'no_telepon' => null,
      'alamat' => null,
    ], $karyawanData);
    if (empty($karyawanData['nama']) && $topName) $karyawanData['nama'] = $topName;
    if (empty($karyawanData['email']) && $topEmail) $karyawanData['email'] = $topEmail;
    if (empty($karyawanData['no_telepon']) && $topPhone) $karyawanData['no_telepon'] = $topPhone;
    if (empty($karyawanData['alamat']) && $topAddress) $karyawanData['alamat'] = $topAddress;

    if (!empty(array_filter($konsultanData))) {
      if ($konsultan) {
        $konsultan->update($konsultanData);
        if (!empty($avatarPath)) {
          $konsultan->foto_konsultan = $avatarPath;
          $konsultan->save();
        }
      } elseif ($user->role === 'konsultan') {
        $new = new Konsultan($konsultanData);
        if (Schema::hasColumn('konsultans', 'user_id')) {
          $new->user_id = $user->id;
        }
        $new->email = $user->email;
        if (!empty($avatarPath)) $new->foto_konsultan = $avatarPath;
        $new->save();
      }
    }
    if (!empty(array_filter($karyawanData))) {
      if ($karyawan) {
        $karyawan->update($karyawanData);
        if (!empty($avatarPath)) {
          $karyawan->foto_karyawan = $avatarPath;
          $karyawan->save();
        }
      } elseif ($user->role === 'karyawan') {
        $new = new Karyawan($karyawanData);
        if (Schema::hasColumn('karyawans', 'user_id')) {
          $new->user_id = $user->id;
        }
        $new->email = $user->email;
        if (!empty($avatarPath)) $new->foto_karyawan = $avatarPath;
        $new->save();
      }
    }

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
