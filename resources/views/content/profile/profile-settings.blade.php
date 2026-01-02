@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

<!-- Page Scripts -->
@section('page-script')
<script>
  // Image reset
  document.addEventListener('DOMContentLoaded', function() {
    const uploadInput = document.getElementById('uploadInput');
    const uploadedAvatar = document.getElementById('uploadedAvatar');
    const uploadFileName = document.getElementById('uploadFileName');

    if (uploadInput) {
      uploadInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(event) {
            uploadedAvatar.src = event.target.result;
          };
          reader.readAsDataURL(file);
          if (uploadFileName) uploadFileName.textContent = file.name;
        }
      });
    }

    // Reset button handler removed per request; no reset action available.
  });
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <!-- Alert Messages -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Perhatian!</strong> Terjadi kesalahan:
      <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-6">
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-6">
          <img
            src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
            alt="user-avatar"
            class="d-block w-px-100 h-px-100 rounded"
            id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="uploadInput" class="btn btn-sm btn-primary me-3 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="icon-base ri ri-upload-2-line d-block d-sm-none"></i>
              <input type="file" id="uploadInput" form="profileForm" class="account-file-input" name="avatar" hidden accept="image/png, image/jpeg,image/jpg,image/gif" />
            </label>
            <!-- Reset button removed -->
            <div>Allowed JPG, GIF or PNG. Max size of 2MB</div>
            <div class="text-muted small mt-1" id="uploadFileName"></div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="card-body pt-0">
        <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mt-1 g-5">
            <!-- Name -->
            <div class="col-md-12 form-control-validation">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('name') is-invalid @enderror"
                  type="text"
                  id="name"
                  name="name"
                  value="{{ old('name', isset($karyawan) && $karyawan->nama ? $karyawan->nama : (isset($konsultan) && $konsultan->nama ? $konsultan->nama : Auth::user()->name)) }}"
                  required />
                <label for="name">Nama Lengkap</label>
                @error('name')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('email') is-invalid @enderror"
                  type="email"
                  id="email"
                  name="email_display"
                  value="{{ old('email', isset($karyawan) && $karyawan->email ? $karyawan->email : (isset($konsultan) && $konsultan->email ? $konsultan->email : Auth::user()->email)) }}"
                  disabled />
                <input type="hidden" name="email" value="{{ old('email', isset($karyawan) && $karyawan->email ? $karyawan->email : (isset($konsultan) && $konsultan->email ? $konsultan->email : Auth::user()->email)) }}" />
                <label for="email">Email</label>
                @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Role hidden per request -->

            <!-- Phone -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('phone') is-invalid @enderror"
                  type="text"
                  id="phone"
                  name="phone"
                  value="{{ old('phone', isset($karyawan) && $karyawan->no_telepon ? $karyawan->no_telepon : (isset($konsultan) && $konsultan->no_telepon ? $konsultan->no_telepon : (Auth::user()->phone ?? ''))) }}"
                  placeholder="Nomor Telepon" />
                <label for="phone">Nomor Telepon</label>
                @error('phone')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Address -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('address') is-invalid @enderror"
                  type="text"
                  id="address"
                  name="address"
                  value="{{ old('address', isset($karyawan) && $karyawan->alamat ? $karyawan->alamat : (isset($konsultan) && $konsultan->alamat ? $konsultan->alamat : (Auth::user()->address ?? ''))) }}"
                  placeholder="Alamat" />
                <label for="address">Alamat</label>
                @error('address')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- City -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('city') is-invalid @enderror"
                  type="text"
                  id="city"
                  name="city"
                  value="{{ old('city', Auth::user()->city ?? '') }}"
                  placeholder="Kota" />
                <label for="city">Kota</label>
                @error('city')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- State -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('state') is-invalid @enderror"
                  type="text"
                  id="state"
                  name="state"
                  value="{{ old('state', Auth::user()->state ?? '') }}"
                  placeholder="Provinsi" />
                <label for="state">Provinsi</label>
                @error('state')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Zip Code -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('zip_code') is-invalid @enderror"
                  type="text"
                  id="zipCode"
                  name="zip_code"
                  value="{{ old('zip_code', Auth::user()->zip_code ?? '') }}"
                  placeholder="Kode Pos" />
                <label for="zipCode">Kode Pos</label>
                @error('zip_code')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Country -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('country') is-invalid @enderror"
                  type="text"
                  id="country"
                  name="country"
                  value="{{ old('country', Auth::user()->country ?? '') }}"
                  placeholder="Negara" />
                <label for="country">Negara</label>
                @error('country')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Keahlian (moved beside Country) -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('karyawan.keahlian') is-invalid @enderror"
                  type="text"
                  id="keahlian"
                  name="karyawan[keahlian]"
                  value="{{ old('karyawan.keahlian', isset($karyawan) ? ($karyawan->keahlian ?? '') : '') }}"
                  placeholder="Keahlian" />
                <label for="keahlian">Keahlian</label>
                @error('karyawan.keahlian')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Bio -->
            <div class="col-md-12">
              <div class="form-floating form-floating-outline">
                <textarea
                  class="form-control @error('bio') is-invalid @enderror"
                  id="bio"
                  name="bio"
                  placeholder="Deskripsi singkat tentang diri Anda"
                  rows="3">{{ old('bio', Auth::user()->bio ?? '') }}</textarea>
                <label for="bio">Biografi</label>
                @error('bio')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Konsultan section removed -->

            <!-- Karyawan section removed per request -->

            <!-- Avatar Input Hidden removed (using uploadInput with form association) -->

            <!-- Submit Button -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ri ri-save-line me-2"></i>
                Simpan Perubahan
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Change Password Section -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title mb-0">Ubah Password</h4>
      </div>
      <div class="card-body pt-0">
        <form method="POST" action="{{ route('profile.updatePassword') }}">
          @csrf
          @method('PUT')

          <div class="row g-5">
            <!-- Current Password -->
            <div class="col-md-12">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('current_password') is-invalid @enderror"
                  type="password"
                  id="currentPassword"
                  name="current_password"
                  required />
                <label for="currentPassword">Password Saat Ini</label>
                @error('current_password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- New Password -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control @error('new_password') is-invalid @enderror"
                  type="password"
                  id="newPassword"
                  name="new_password"
                  required />
                <label for="newPassword">Password Baru</label>
                @error('new_password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input
                  class="form-control"
                  type="password"
                  id="confirmPassword"
                  name="new_password_confirmation"
                  required />
                <label for="confirmPassword">Konfirmasi Password Baru</label>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ri ri-lock-line me-2"></i>
                Ubah Password
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection