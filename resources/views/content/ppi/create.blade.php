@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah PPI')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah PPI</h5>
        <a href="{{ route('ppi.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('ppi.store') }}">
          @csrf
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="konsultan_id" class="form-label">Konsultan</label>
              <select name="konsultan_id" id="konsultan_id" class="form-select">
                <option value="">Pilih Konsultan (opsional)</option>
                @foreach($konsultans as $k)
                <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->spesialisasi }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="anak_didik_id" class="form-label">Anak Didik</label>
              <select name="anak_didik_id" id="anak_didik_id" class="form-select" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }} ({{ $anak->nis ?? '-' }})</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="nama_program" class="form-label">Nama Program</label>
              <input type="text" name="nama_program" id="nama_program" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label for="periode_mulai" class="form-label">Periode Mulai</label>
              <input type="date" name="periode_mulai" id="periode_mulai" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label for="periode_selesai" class="form-label">Periode Selesai</label>
              <input type="date" name="periode_selesai" id="periode_selesai" class="form-control" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label for="keterangan" class="form-label">Keterangan</label>
              <textarea name="keterangan" id="keterangan" class="form-control" rows="4"></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-start gap-2">
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-2"></i>Simpan</button>
            <a href="{{ route('ppi.index') }}" class="btn btn-outline-danger"><i class="ri-close-line me-2"></i>Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection