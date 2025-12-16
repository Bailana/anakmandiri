@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Program Anak')

@section('content')
<div class="row">
  <div class="col-12 col-md-8 col-lg-6 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Tambah Program Anak</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('program-anak.store') }}">
          @csrf
          <div class="mb-3">
            <label for="anak_didik_id" class="form-label">Nama Anak Didik</label>
            <select name="anak_didik_id" id="anak_didik_id" class="form-select" required>
              <option value="">Pilih Anak Didik</option>
              @foreach($anakDidiks as $anak)
              <option value="{{ $anak->id }}">{{ $anak->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="nama_program" class="form-label">Nama Program</label>
            <input type="text" name="nama_program" id="nama_program" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="periode_mulai" class="form-label">Periode Mulai</label>
            <input type="date" name="periode_mulai" id="periode_mulai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="periode_selesai" class="form-label">Periode Selesai</label>
            <input type="date" name="periode_selesai" id="periode_selesai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
              <option value="aktif">Aktif</option>
              <option value="selesai">Selesai</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
          </div>
          <div class="d-flex justify-content-end">
            <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection