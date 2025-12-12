@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Anak Didik')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Anak Didik</h5>
        <a href="{{ route('anak-didik.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('anak-didik.update', $anakDidik->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                placeholder="Masukkan nama lengkap" value="{{ old('nama', $anakDidik->nama) }}" required>
              @error('nama')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">NIS <span class="text-danger">*</span></label>
              <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                placeholder="Nomor Induk Siswa" value="{{ old('nis', $anakDidik->nis) }}" required>
              @error('nis')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
              <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                <option value="">Pilih Jenis Kelamin</option>
                <option value="laki-laki" {{ old('jenis_kelamin', $anakDidik->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                <option value="perempuan" {{ old('jenis_kelamin', $anakDidik->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
              </select>
              @error('jenis_kelamin')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                value="{{ old('tanggal_lahir', $anakDidik->tanggal_lahir) }}" required>
              @error('tanggal_lahir')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                rows="3" placeholder="Masukkan alamat">{{ old('alamat', $anakDidik->alamat) }}</textarea>
              @error('alamat')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">No. Telepon</label>
              <input type="tel" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror"
                placeholder="08xx-xxxx-xxxx" value="{{ old('no_telepon', $anakDidik->no_telepon) }}">
              @error('no_telepon')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="example@email.com" value="{{ old('email', $anakDidik->email) }}">
              @error('email')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama Orang Tua/Wali</label>
              <input type="text" name="nama_orang_tua" class="form-control @error('nama_orang_tua') is-invalid @enderror"
                placeholder="Nama orang tua" value="{{ old('nama_orang_tua', $anakDidik->nama_orang_tua) }}">
              @error('nama_orang_tua')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">No. Telepon Orang Tua</label>
              <input type="tel" name="no_telepon_orang_tua" class="form-control @error('no_telepon_orang_tua') is-invalid @enderror"
                placeholder="08xx-xxxx-xxxx" value="{{ old('no_telepon_orang_tua', $anakDidik->no_telepon_orang_tua) }}">
              @error('no_telepon_orang_tua')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="ri-save-line me-2"></i>Perbarui
              </button>
              <a href="{{ route('anak-didik.index') }}" class="btn btn-outline-secondary">
                <i class="ri-close-line me-2"></i>Batal
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection