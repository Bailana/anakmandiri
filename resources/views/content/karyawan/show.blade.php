@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Karyawan')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Detail Karyawan</h5>
        <div>
          <a href="{{ route('karyawan.edit', $karyawan->id) }}" class="btn btn-warning btn-sm me-2">
            <i class="ri-edit-line me-2"></i>Edit
          </a>
          <a href="{{ route('karyawan.index') }}" class="btn btn-secondary btn-sm">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Data Pribadi Section -->
        <div class="mb-4">
          <h6 class="text-primary mb-3">
            <i class="ri-user-line me-2"></i>Data Pribadi
          </h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Nama Lengkap</h6>
              <p class="mb-0"><strong>{{ $karyawan->nama }}</strong></p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">NIK</h6>
              <p class="mb-0"><strong>{{ $karyawan->nik ?? '-' }}</strong></p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">NIP</h6>
              <p class="mb-0"><strong>{{ $karyawan->nip ?? '-' }}</strong></p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Jenis Kelamin</h6>
              <p class="mb-0">
                @if($karyawan->jenis_kelamin)
                <span class="badge {{ $karyawan->jenis_kelamin === 'laki-laki' ? 'bg-info' : 'bg-warning' }}">
                  {{ ucfirst($karyawan->jenis_kelamin) }}
                </span>
                @else
                -
                @endif
              </p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Tempat, Tanggal Lahir</h6>
              <p class="mb-0">
                {{ $karyawan->tempat_lahir ?? '-' }}{{ $karyawan->tempat_lahir && $karyawan->tanggal_lahir ? ', ' : '' }}{{ $karyawan->tanggal_lahir ? \Carbon\Carbon::parse($karyawan->tanggal_lahir)->format('d-m-Y') : '' }}
              </p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">No. Telepon</h6>
              <p class="mb-0">{{ $karyawan->no_telepon ?? '-' }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Email</h6>
              <p class="mb-0">{{ $karyawan->email ?? '-' }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-muted mb-2">Alamat</h6>
              <p class="mb-0">{{ $karyawan->alamat ?? '-' }}</p>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <!-- Data Pekerjaan Section -->
        <div class="mb-4">
          <h6 class="text-primary mb-3">
            <i class="ri-briefcase-line me-2"></i>Data Pekerjaan
          </h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Posisi</h6>
              <p class="mb-0"><strong>{{ $karyawan->posisi ?? '-' }}</strong></p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Departemen</h6>
              <p class="mb-0"><strong>{{ $karyawan->departemen ?? '-' }}</strong></p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Status Kepegawaian</h6>
              <p class="mb-0">
                @if($karyawan->status_kepegawaian)
                <span class="badge 
                    @if($karyawan->status_kepegawaian === 'tetap') bg-success
                    @elseif($karyawan->status_kepegawaian === 'kontrak') bg-warning
                    @else bg-secondary
                    @endif
                  ">
                  {{ ucfirst($karyawan->status_kepegawaian) }}
                </span>
                @else
                -
                @endif
              </p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Tanggal Bergabung</h6>
              <p class="mb-0">{{ $karyawan->tanggal_bergabung ? \Carbon\Carbon::parse($karyawan->tanggal_bergabung)->format('d-m-Y') : '-' }}</p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-muted mb-2">Keahlian</h6>
              <p class="mb-0">{{ $karyawan->keahlian ?? '-' }}</p>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <!-- Data Pendidikan Section -->
        <div class="mb-4">
          <h6 class="text-primary mb-3">
            <i class="ri-graduation-cap-line me-2"></i>Data Pendidikan
          </h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Pendidikan Terakhir</h6>
              <p class="mb-0"><strong>{{ $karyawan->pendidikan_terakhir ?? '-' }}</strong></p>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-2">Institusi Pendidikan</h6>
              <p class="mb-0"><strong>{{ $karyawan->institusi_pendidikan ?? '-' }}</strong></p>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <!-- Action Buttons -->
        <div class="row">
          <div class="col-12">
            <h6 class="text-muted mb-3">Aksi</h6>
            <a href="{{ route('karyawan.edit', $karyawan->id) }}" class="btn btn-warning me-2">
              <i class="ri-edit-line me-2"></i>Edit Data
            </a>
            <form action="{{ route('karyawan.destroy', $karyawan->id) }}" method="POST" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger me-2" onclick="return confirm('Apakah Anda yakin ingin menghapus data karyawan ini?')">
                <i class="ri-delete-bin-line me-2"></i>Hapus
              </button>
            </form>
            <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">
              <i class="ri-arrow-left-line me-2"></i>Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@if ($message = Session::get('success'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Show success toast/alert
    alert('{{ $message }}');
  });
</script>
@endif
@endsection