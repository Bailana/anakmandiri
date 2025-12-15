@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Konsultan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Data Konsultan</h5>
        <a href="{{ route('konsultan.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('konsultan.update', $konsultan->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="data-pribadi-tab" data-bs-toggle="tab" data-bs-target="#data-pribadi"
                type="button" role="tab" aria-controls="data-pribadi" aria-selected="true">
                <i class="ri-user-line me-2"></i>Data Pribadi
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="data-profesional-tab" data-bs-toggle="tab" data-bs-target="#data-profesional"
                type="button" role="tab" aria-controls="data-profesional" aria-selected="false">
                <i class="ri-briefcase-line me-2"></i>Data Profesional
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="data-pendidikan-tab" data-bs-toggle="tab" data-bs-target="#data-pendidikan"
                type="button" role="tab" aria-controls="data-pendidikan" aria-selected="false">
                <i class="ri-graduation-cap-line me-2"></i>Data Pendidikan
              </button>
            </li>
          </ul>

          <div class="tab-content">
            <!-- Data Pribadi Tab -->
            <div class="tab-pane fade show active" id="data-pribadi" role="tabpanel" aria-labelledby="data-pribadi-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Konsultan <span class="text-danger">*</span></label>
                  <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama konsultan" value="{{ old('nama', $konsultan->nama) }}" required>
                  @error('nama')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">NIK</label>
                  <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" placeholder="Nomor Identitas Kependudukan" value="{{ old('nik', $konsultan->nik) }}">
                  @error('nik')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com" value="{{ old('email', $konsultan->email) }}">
                  @error('email')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">No Telepon</label>
                  <input type="text" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('no_telepon', $konsultan->no_telepon) }}">
                  @error('no_telepon')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Jenis Kelamin</label>
                  <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="laki-laki" {{ old('jenis_kelamin', $konsultan->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="perempuan" {{ old('jenis_kelamin', $konsultan->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                  </select>
                  @error('jenis_kelamin')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tanggal Lahir</label>
                  <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $konsultan->tanggal_lahir ? $konsultan->tanggal_lahir->format('Y-m-d') : '') }}">
                  @error('tanggal_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" placeholder="Tempat lahir" value="{{ old('tempat_lahir', $konsultan->tempat_lahir) }}">
                  @error('tempat_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $konsultan->alamat) }}</textarea>
                  @error('alamat')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" disabled>
                  <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('data-profesional-tab').click()">
                  Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                </button>
              </div>
            </div>
            <!-- Data Profesional Tab -->
            <div class="tab-pane fade" id="data-profesional" role="tabpanel" aria-labelledby="data-profesional-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                  <input type="text" name="spesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" placeholder="Contoh: Psikologi, Terapi Fisik" value="{{ old('spesialisasi', $konsultan->spesialisasi) }}" required>
                  @error('spesialisasi')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Pengalaman (Tahun)</label>
                  <input type="number" name="pengalaman_tahun" class="form-control @error('pengalaman_tahun') is-invalid @enderror" placeholder="Jumlah tahun pengalaman" value="{{ old('pengalaman_tahun', $konsultan->pengalaman_tahun) }}">
                  @error('pengalaman_tahun')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Bidang Keahlian</label>
                  <textarea name="bidang_keahlian" class="form-control @error('bidang_keahlian') is-invalid @enderror" rows="2" placeholder="Sebutkan bidang keahlian yang dimiliki">{{ old('bidang_keahlian', $konsultan->bidang_keahlian) }}</textarea>
                  @error('bidang_keahlian')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Sertifikasi</label>
                  <textarea name="sertifikasi" class="form-control @error('sertifikasi') is-invalid @enderror" rows="2" placeholder="Sertifikasi yang dimiliki">{{ old('sertifikasi', $konsultan->sertifikasi) }}</textarea>
                  @error('sertifikasi')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Status Hubungan</label>
                  <select name="status_hubungan" class="form-select @error('status_hubungan') is-invalid @enderror">
                    <option value="">Pilih Status</option>
                    <option value="aktif" {{ old('status_hubungan', $konsultan->status_hubungan) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="non-aktif" {{ old('status_hubungan', $konsultan->status_hubungan) === 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                  </select>
                  @error('status_hubungan')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tanggal Registrasi</label>
                  <input type="date" name="tanggal_registrasi" class="form-control @error('tanggal_registrasi') is-invalid @enderror" value="{{ old('tanggal_registrasi', $konsultan->tanggal_registrasi ? $konsultan->tanggal_registrasi->format('Y-m-d') : '') }}">
                  @error('tanggal_registrasi')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('data-pribadi-tab').click()">
                  <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('data-pendidikan-tab').click()">
                  Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                </button>
              </div>
            </div>
            <!-- Data Pendidikan Tab -->
            <div class="tab-pane fade" id="data-pendidikan" role="tabpanel" aria-labelledby="data-pendidikan-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Pendidikan Terakhir</label>
                  <input type="text" name="pendidikan_terakhir" class="form-control @error('pendidikan_terakhir') is-invalid @enderror" placeholder="Contoh: S1, S2, S3" value="{{ old('pendidikan_terakhir', $konsultan->pendidikan_terakhir) }}">
                  @error('pendidikan_terakhir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Institusi Pendidikan</label>
                  <input type="text" name="institusi_pendidikan" class="form-control @error('institusi_pendidikan') is-invalid @enderror" placeholder="Nama universitas/institusi" value="{{ old('institusi_pendidikan', $konsultan->institusi_pendidikan) }}">
                  @error('institusi_pendidikan')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="alert alert-info" role="alert">
                <i class="ri-information-line me-2"></i>
                Pastikan semua data yang Anda masukkan sudah benar sebelum menyimpan.
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('data-profesional-tab').click()">
                  <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                </button>
                <div>
                  <a href="{{ route('konsultan.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ri-close-line me-2"></i>Batal
                  </a>
                  <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-2"></i>Simpan Perubahan
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@if ($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    @foreach($errors-> keys() as $field)
    @if(in_array($field, ['nama', 'nik', 'email', 'no_telepon', 'jenis_kelamin', 'tanggal_lahir', 'tempat_lahir', 'alamat']))
    document.getElementById('data-pribadi-tab').click();
    @break
    @elseif(in_array($field, ['spesialisasi', 'pengalaman_tahun', 'bidang_keahlian', 'sertifikasi', 'status_hubungan', 'tanggal_registrasi']))
    document.getElementById('data-profesional-tab').click();
    @break
    @elseif(in_array($field, ['pendidikan_terakhir', 'institusi_pendidikan']))
    document.getElementById('data-pendidikan-tab').click();
    @break
    @endif
    @endforeach
  });
</script>
@endif
@endsection