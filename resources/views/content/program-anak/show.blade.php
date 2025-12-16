@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Program Anak')

@section('content')
<div class="row">
  <div class="col-12 col-md-8 col-lg-6 mx-auto">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Detail Program Anak</h5>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Nama Anak Didik</dt>
          <dd class="col-sm-8">{{ $program->anakDidik->nama ?? '-' }}</dd>
          <dt class="col-sm-4">Nama Program</dt>
          <dd class="col-sm-8">{{ $program->nama_program }}</dd>
          <dt class="col-sm-4">Periode</dt>
          <dd class="col-sm-8">{{ $program->periode_mulai }} s/d {{ $program->periode_selesai }}</dd>
          <dt class="col-sm-4">Status</dt>
          <dd class="col-sm-8">
            <span class="badge bg-label-{{ $program->status == 'aktif' ? 'success' : ($program->status == 'selesai' ? 'info' : 'secondary') }}">
              {{ ucfirst($program->status) }}
            </span>
          </dd>
          <dt class="col-sm-4">Keterangan</dt>
          <dd class="col-sm-8">{{ $program->keterangan ?? '-' }}</dd>
        </dl>
        <div class="d-flex justify-content-end mt-4">
          <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection