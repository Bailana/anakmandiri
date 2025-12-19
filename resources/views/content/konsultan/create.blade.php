@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Konsultan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Tambah Konsultan Baru</h4>
            <p class="text-body-secondary mb-0">Isi form di bawah untuk menambah data konsultan baru</p>
          </div>
          <a href="{{ route('konsultan.index') }}" class="btn btn-secondary btn-sm">
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
        <form action="{{ route('konsultan.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="nama">Nama Konsultan <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" placeholder="Masukkan nama konsultan" value="{{ old('nama') }}" required>
              @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nik">NIK</label>
              <input type="text" inputmode="numeric" maxlength="16" pattern="\d*" oninput="this.value=this.value.replace(/\D/g,'').slice(0,16)" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" placeholder="Nomor Identitas Kependudukan" value="{{ old('nik') }}">
              @error('nik')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="email@example.com" value="{{ old('email') }}">
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="no_telepon">No Telepon</label>
              <input type="text" inputmode="numeric" maxlength="13" pattern="\d*" oninput="this.value=this.value.replace(/\D/g,'').slice(0,13)" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" placeholder="08xxxxxxxxxx" value="{{ old('no_telepon') }}">
              @error('no_telepon')
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

          <h6 class="mb-3">Informasi Profesional</h6>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="spesialisasi">Spesialisasi <span class="text-danger">*</span></label>
              <select class="form-select @error('spesialisasi') is-invalid @enderror" id="spesialisasi" name="spesialisasi" required>
                <option value="">Pilih Spesialisasi</option>
                <option value="Pendidikan" {{ old('spesialisasi') == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
                <option value="Psikologi" {{ old('spesialisasi') == 'Psikologi' ? 'selected' : '' }}>Psikologi</option>
                <option value="Wicara" {{ old('spesialisasi') == 'Wicara' ? 'selected' : '' }}>Wicara</option>
                <option value="Sensori Integrasi" {{ old('spesialisasi') == 'Sensori Integrasi' ? 'selected' : '' }}>Sensori Integrasi</option>
              </select>
              @error('spesialisasi')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="pengalaman_tahun">Pengalaman (Tahun)</label>
              <input type="number" class="form-control @error('pengalaman_tahun') is-invalid @enderror" id="pengalaman_tahun" name="pengalaman_tahun" placeholder="Jumlah tahun pengalaman" value="{{ old('pengalaman_tahun') }}">
              @error('pengalaman_tahun')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label" for="bidang_keahlian">Bidang Keahlian</label>
              <textarea class="form-control @error('bidang_keahlian') is-invalid @enderror" id="bidang_keahlian" name="bidang_keahlian" rows="2" placeholder="Sebutkan bidang keahlian yang dimiliki">{{ old('bidang_keahlian') }}</textarea>
              @error('bidang_keahlian')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label" for="sertifikasi">Sertifikasi</label>
              <textarea class="form-control @error('sertifikasi') is-invalid @enderror" id="sertifikasi" name="sertifikasi" rows="2" placeholder="Sertifikasi yang dimiliki">{{ old('sertifikasi') }}</textarea>
              @error('sertifikasi')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label" for="status_hubungan">Status</label>
              <select class="form-select @error('status_hubungan') is-invalid @enderror" id="status_hubungan" name="status_hubungan">
                <option value="">Pilih Status</option>
                <option value="aktif" {{ old('status_hubungan') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="non-aktif" {{ old('status_hubungan') === 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
              </select>
              @error('status_hubungan')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tanggal_registrasi">Tanggal Registrasi</label>
              <input type="date" class="form-control @error('tanggal_registrasi') is-invalid @enderror" id="tanggal_registrasi" name="tanggal_registrasi" value="{{ old('tanggal_registrasi') }}">
              @error('tanggal_registrasi')
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
              <input type="text" class="form-control @error('institusi_pendidikan') is-invalid @enderror" id="institusi_pendidikan" name="institusi_pendidikan" placeholder="Nama universitas/institusi" value="{{ old('institusi_pendidikan') }}">
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
              <a href="{{ route('konsultan.index') }}" class="btn btn-outline-secondary">
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