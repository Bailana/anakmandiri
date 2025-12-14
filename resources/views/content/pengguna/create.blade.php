@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Tambah Pengguna</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('pengguna.store') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-control" id="role" name="role" required>
          <option value="admin">Admin</option>
          <option value="guru">Guru</option>
          <option value="konsultan">Konsultan</option>
          <option value="terapis">Terapis</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection