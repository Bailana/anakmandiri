@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Anak Didik')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Detail Anak Didik</h5>
        <div>
          <a href="{{ route('anak-didik.edit', $anakDidik->id) }}" class="btn btn-warning btn-sm me-2">
            <i class="ri-edit-line me-2"></i>Edit
          </a>
          <a href="{{ route('anak-didik.index') }}" class="btn btn-secondary btn-sm">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Nama Lengkap</h6>
            <p class="mb-0"><strong>{{ $anakDidik->nama }}</strong></p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-2">NIS</h6>
            <p class="mb-0"><strong>{{ $anakDidik->nis }}</strong></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Jenis Kelamin</h6>
            <p class="mb-0">
              <span class="badge {{ $anakDidik->jenis_kelamin === 'laki-laki' ? 'bg-info' : 'bg-pink' }}">
                {{ ucfirst($anakDidik->jenis_kelamin) }}
              </span>
            </p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Tanggal Lahir</h6>
            <p class="mb-0"><strong>{{ $anakDidik->tanggal_lahir ? \Carbon\Carbon::parse($anakDidik->tanggal_lahir)->format('d-m-Y') : '-' }}</strong></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="text-muted mb-2">Alamat</h6>
            <p class="mb-0">{{ $anakDidik->alamat ?? '-' }}</p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-2">No. Telepon</h6>
            <p class="mb-0">{{ $anakDidik->no_telepon ?? '-' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Email</h6>
            <p class="mb-0">{{ $anakDidik->email ?? '-' }}</p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-2">Nama Orang Tua/Wali</h6>
            <p class="mb-0">{{ $anakDidik->nama_orang_tua ?? '-' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-2">No. Telepon Orang Tua</h6>
            <p class="mb-0">{{ $anakDidik->no_telepon_orang_tua ?? '-' }}</p>
          </div>
        </div>

        <hr class="my-4">

        <div class="row">
          <div class="col-12">
            <h6 class="text-muted mb-3">Aksi</h6>
            <a href="{{ route('anak-didik.edit', $anakDidik->id) }}" class="btn btn-warning me-2">
              <i class="ri-edit-line me-2"></i>Edit Data
            </a>
            <form action="{{ route('anak-didik.destroy', $anakDidik->id) }}" method="POST" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                <i class="ri-delete-bin-line me-2"></i>Hapus
              </button>
            </form>
            <a href="{{ route('anak-didik.index') }}" class="btn btn-secondary">
              <i class="ri-arrow-left-line me-2"></i>Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection