@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Karyawan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Tambah Karyawan Baru</h4>
            <p class="text-body-secondary mb-0">Isi form di bawah untuk menambah data karyawan baru</p>
          </div>
          <a href="{{ route('karyawan.index') }}" class="btn btn-secondary btn-sm">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('karyawan.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="nama">Nama Karyawan <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" placeholder="Masukkan nama karyawan" value="{{ old('nama') }}" required>
              @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nip">NIP</label>
              <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" placeholder="Nomor Induk Pegawai" value="{{ old('nip') }}">
              @error('nip')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="nik">NIK</label>
              <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" placeholder="Nomor Identitas Kependudukan" value="{{ old('nik') }}">
              @error('nik')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="email@example.com" value="{{ old('email') }}">
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
              <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="laki-laki" {{ old('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                <option value="perempuan" {{ old('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
              </select>
              @error('jenis_kelamin')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
              <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
              @error('tanggal_lahir')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
              <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" name="tempat_lahir" placeholder="Tempat lahir" value="{{ old('tempat_lahir') }}">
              @error('tempat_lahir')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="no_telepon">No Telepon</label>
              <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" placeholder="08xxxxxxxxxx" value="{{ old('no_telepon') }}">
              @error('no_telepon')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label" for="alamat">Alamat</label>
              <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
              @error('alamat')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Informasi Pekerjaan</h6>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="posisi">Posisi <span class="text-danger">*</span></label>
              <select class="form-select @error('posisi') is-invalid @enderror" id="posisi" name="posisi" required>
                <option value="">Pilih Posisi</option>
                <option value="Kepala Sekolah" {{ old('posisi') == 'Kepala Sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                <option value="Bendahara" {{ old('posisi') == 'Bendahara' ? 'selected' : '' }}>Bendahara</option>
                <option value="Guru Fokus" {{ old('posisi') == 'Guru Fokus' ? 'selected' : '' }}>Guru Fokus</option>
                <option value="Administrasi" {{ old('posisi') == 'Administrasi' ? 'selected' : '' }}>Administrasi</option>
                <option value="Wakil Kepala Kurikulum" {{ old('posisi') == 'Wakil Kepala Kurikulum' ? 'selected' : '' }}>Wakil Kepala Kurikulum</option>
                <option value="Wakil Kepala Sarana Prasarana" {{ old('posisi') == 'Wakil Kepala Sarana Prasarana' ? 'selected' : '' }}>Wakil Kepala Sarana Prasarana</option>
                <option value="Kepala Klinik" {{ old('posisi') == 'Kepala Klinik' ? 'selected' : '' }}>Kepala Klinik</option>
                <option value="Kepala IT" {{ old('posisi') == 'Kepala IT' ? 'selected' : '' }}>Kepala IT</option>
                <option value="Staff IT" {{ old('posisi') == 'Staff IT' ? 'selected' : '' }}>Staff IT</option>
                <option value="Terapis" {{ old('posisi') == 'Terapis' ? 'selected' : '' }}>Terapis</option>
              </select>
              @error('posisi')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="departemen">Departemen <span class="text-danger">*</span></label>
              <select class="form-select @error('departemen') is-invalid @enderror" id="departemen" name="departemen" required>
                <option value="">Pilih Departemen</option>
                <option value="Sekolah" {{ old('departemen') == 'Sekolah' ? 'selected' : '' }}>Sekolah</option>
                <option value="Klinik" {{ old('departemen') == 'Klinik' ? 'selected' : '' }}>Klinik</option>
                <option value="Vokasi" {{ old('departemen') == 'Vokasi' ? 'selected' : '' }}>Vokasi</option>
              </select>
              @error('departemen')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="status_kepegawaian">Status Kepegawaian</label>
              <select class="form-select @error('status_kepegawaian') is-invalid @enderror" id="status_kepegawaian" name="status_kepegawaian">
                <option value="">Pilih Status</option>
                <option value="Tetap" {{ old('status_kepegawaian') == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                <option value="Training" {{ old('status_kepegawaian') == 'Training' ? 'selected' : '' }}>Training</option>
                <!-- Removed Honorer option -->
              </select>
              @error('status_kepegawaian')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tanggal_bergabung">Tanggal Bergabung</label>
              <input type="date" class="form-control @error('tanggal_bergabung') is-invalid @enderror" id="tanggal_bergabung" name="tanggal_bergabung" value="{{ old('tanggal_bergabung') }}">
              @error('tanggal_bergabung')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label" for="keahlian">Keahlian</label>
              <textarea class="form-control @error('keahlian') is-invalid @enderror" id="keahlian" name="keahlian" rows="2" placeholder="Sebutkan keahlian yang dimiliki">{{ old('keahlian') }}</textarea>
              @error('keahlian')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Informasi Pendidikan</h6>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="pendidikan_terakhir">Pendidikan Terakhir</label>
              <select class="form-select @error('pendidikan_terakhir') is-invalid @enderror" id="pendidikan_terakhir" name="pendidikan_terakhir">
                <option value="">Pilih Pendidikan Terakhir</option>
                <option value="SMA" {{ old('pendidikan_terakhir') == 'SMA' ? 'selected' : '' }}>SMA</option>
                <option value="D3" {{ old('pendidikan_terakhir') == 'D3' ? 'selected' : '' }}>D3</option>
                <option value="D4" {{ old('pendidikan_terakhir') == 'D4' ? 'selected' : '' }}>D4</option>
                <option value="S1" {{ old('pendidikan_terakhir') == 'S1' ? 'selected' : '' }}>S1</option>
                <option value="S2" {{ old('pendidikan_terakhir') == 'S2' ? 'selected' : '' }}>S2</option>
                <option value="S3" {{ old('pendidikan_terakhir') == 'S3' ? 'selected' : '' }}>S3</option>
              </select>
              @error('pendidikan_terakhir')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="institusi_pendidikan">Institusi Pendidikan</label>
              <input type="text" class="form-control @error('institusi_pendidikan') is-invalid @enderror" id="institusi_pendidikan" name="institusi_pendidikan" placeholder="Nama universitas/sekolah" value="{{ old('institusi_pendidikan') }}">
              @error('institusi_pendidikan')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row pt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="ri-save-line me-2"></i>Simpan Data
              </button>
              <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary">
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