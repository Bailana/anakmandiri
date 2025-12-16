@extends('layouts/contentNavbarLayout')

@section('title', 'Program Anak')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Program Anak</h4>
          <p class="text-body-secondary mb-0">Kelola program anak didik</p>
        </div>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
        <a href="{{ route('program-anak.create') }}" class="btn btn-primary">
          <i class="ri-add-line me-2"></i>Tambah Program Anak
        </a>
        @endif
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="programAnakTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama Anak</th>
              <th>Program</th>
              <th>Periode</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {{-- Contoh data dummy, ganti dengan @foreach($programAnak as $index => $program) --}}
            <tr>
              <td>1</td>
              <td>Rizky Ramadhan</td>
              <td>Terapi Wicara</td>
              <td>Jan 2025 - Mar 2025</td>
              <td><span class="badge bg-label-success">Aktif</span></td>
              <td>
                <a href="#" class="btn btn-sm btn-outline-info" title="Lihat"><i class="ri-eye-line"></i></a>
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
                <a href="#" class="btn btn-sm btn-outline-warning" title="Edit"><i class="ri-edit-line"></i></a>
                <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="ri-delete-bin-line"></i></a>
                @endif
              </td>
            </tr>
            {{-- End contoh data --}}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection