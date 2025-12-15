<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
  public function index(Request $request)
  {
    $query = User::query();
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%$search%")
          ->orWhere('email', 'like', "%$search%")
          ->orWhere('role', 'like', "%$search%");
      });
    }
    if ($request->filled('role')) {
      $query->where('role', $request->role);
    }
    $users = $query->paginate(15)->appends($request->all());
    $roleOptions = ['admin', 'guru', 'konsultan', 'terapis'];
    return view('content.pengguna.index', compact('users', 'roleOptions'));
  }

  public function create()
  {
    $karyawanList = \App\Models\Karyawan::pluck('nama')->toArray();
    $konsultanList = \App\Models\Konsultan::pluck('nama')->toArray();
    $namaList = array_unique(array_merge($karyawanList, $konsultanList));
    sort($namaList);
    return view('content.pengguna.create', compact('namaList'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|string|min:6',
      'role' => 'required|string',
    ]);
    $validated['password'] = bcrypt($validated['password']);
    User::create($validated);
    return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
  }

  public function edit($id)
  {
    $user = User::findOrFail($id);
    return view('content.pengguna.edit', compact('user'));
  }

  public function update(Request $request, $id)
  {
    $user = User::findOrFail($id);
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email,' . $user->id,
      'role' => 'required|string',
    ]);
    if ($request->filled('password')) {
      $validated['password'] = bcrypt($request->password);
    }
    $user->update($validated);
    return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diupdate.');
  }

  public function destroy($id)
  {
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
  }
}
