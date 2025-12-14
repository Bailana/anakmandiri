@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Pengguna')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Edit Pengguna</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('pengguna.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
        <input type="password" class="form-control" id="password" name="password">
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-control" id="role" name="role" required>
          <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
          <option value="guru" @if($user->role=='guru') selected @endif>Guru</option>
          <option value="konsultan" @if($user->role=='konsultan') selected @endif>Konsultan</option>
          <option value="terapis" @if($user->role=='terapis') selected @endif>Terapis</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection