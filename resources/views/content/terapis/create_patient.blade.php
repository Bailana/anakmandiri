@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Pasien Terapis')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Tambah Pasien Terapis</h4>
        <form method="POST" action="{{ route('terapis.pasien.store') }}">
          @csrf
          <div class="row g-3">
            @if(isset($user) && $user->role === 'admin')
            <div class="col-md-6">
              <label class="form-label">Terapis</label>
              <select name="user_id" class="form-select">
                <option value="">-- Pilih Terapis --</option>
                @foreach($therapists as $t)
                <option value="{{ $t->id }}" @if((string)$t->id === (string)($selectedTherapisId ?? '')) selected @endif>{{ $t->name }}</option>
                @endforeach
              </select>
            </div>
            @endif

            <div class="col-md-6">
              <label class="form-label">Anak Didik</label>
              <select name="anak_didik_id" class="form-select" required>
                <option value="">-- Pilih Anak Didik --</option>
                @foreach($anakDidiks as $a)
                <option value="{{ $a->id }}">{{ $a->nama }} ({{ $a->tanggal_lahir?->format('Y-m-d') ?? '-' }})</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <input type="text" name="status" class="form-control" value="{{ old('status', 'aktif') }}">
            </div>

            <div class="col-12">
              <button class="btn btn-primary">Simpan</button>
              <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection