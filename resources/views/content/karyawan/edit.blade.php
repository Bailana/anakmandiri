@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Karyawan')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Data Karyawan</h5>
        <a href="{{ route('karyawan.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="data-pribadi-tab" data-bs-toggle="tab" data-bs-target="#data-pribadi"
              type="button" role="tab" aria-controls="data-pribadi" aria-selected="true">
              <i class="ri-user-line me-2"></i>Data Pribadi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="data-pekerjaan-tab" data-bs-toggle="tab" data-bs-target="#data-pekerjaan"
              type="button" role="tab" aria-controls="data-pekerjaan" aria-selected="false">
              <i class="ri-briefcase-line me-2"></i>Data Pekerjaan
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="data-pendidikan-tab" data-bs-toggle="tab" data-bs-target="#data-pendidikan"
              type="button" role="tab" aria-controls="data-pendidikan" aria-selected="false">
              <i class="ri-graduation-cap-line me-2"></i>Data Pendidikan
            </button>
          </li>
        </ul>

        <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Data Pribadi Tab -->
            <div class="tab-pane fade show active" id="data-pribadi" role="tabpanel" aria-labelledby="data-pribadi-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                  <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                    placeholder="Masukkan nama lengkap" value="{{ old('nama', $karyawan->nama) }}" required>
                  @error('nama')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">NIK</label>
                  <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                    placeholder="Nomor Identitas Kependudukan" value="{{ old('nik', $karyawan->nik) }}">
                  @error('nik')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">NIP</label>
                  <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                    placeholder="Nomor Induk Pegawai" value="{{ old('nip', $karyawan->nip) }}">
                  @error('nip')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="email@example.com" value="{{ old('email', $karyawan->email) }}">
                  @error('email')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Jenis Kelamin</label>
                  <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="laki-laki" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>
                      Laki-laki
                    </option>
                    <option value="perempuan" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>
                      Perempuan
                    </option>
                  </select>
                  @error('jenis_kelamin')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">No Telepon</label>
                  <input type="text" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror"
                    placeholder="08xxxxxxxxxx" value="{{ old('no_telepon', $karyawan->no_telepon) }}">
                  @error('no_telepon')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                    placeholder="Tempat lahir" value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}">
                  @error('tempat_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tanggal Lahir</label>
                  <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                    value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('Y-m-d') : '') }}">
                  @error('tanggal_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                    rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $karyawan->alamat) }}</textarea>
                  @error('alamat')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" disabled>
                  <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('data-pekerjaan-tab').click()">
                  Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                </button>
              </div>
            </div>

            <!-- Data Pekerjaan Tab -->
            <div class="tab-pane fade" id="data-pekerjaan" role="tabpanel" aria-labelledby="data-pekerjaan-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Posisi</label>
                  <input type="text" name="posisi" class="form-control @error('posisi') is-invalid @enderror"
                    placeholder="Posisi kerja" value="{{ old('posisi', $karyawan->posisi) }}">
                  @error('posisi')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Departemen</label>
                  <input type="text" name="departemen" class="form-control @error('departemen') is-invalid @enderror"
                    placeholder="Departemen" value="{{ old('departemen', $karyawan->departemen) }}">
                  @error('departemen')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Status Kepegawaian</label>
                  <select name="status_kepegawaian" class="form-select @error('status_kepegawaian') is-invalid @enderror">
                    <option value="">Pilih Status</option>
                    <option value="tetap" {{ old('status_kepegawaian', $karyawan->status_kepegawaian) === 'tetap' ? 'selected' : '' }}>
                      Tetap
                    </option>
                    <option value="kontrak" {{ old('status_kepegawaian', $karyawan->status_kepegawaian) === 'kontrak' ? 'selected' : '' }}>
                      Kontrak
                    </option>
                    <option value="honorer" {{ old('status_kepegawaian', $karyawan->status_kepegawaian) === 'honorer' ? 'selected' : '' }}>
                      Honorer
                    </option>
                  </select>
                  @error('status_kepegawaian')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tanggal Bergabung</label>
                  <input type="date" name="tanggal_bergabung" class="form-control @error('tanggal_bergabung') is-invalid @enderror"
                    value="{{ old('tanggal_bergabung', $karyawan->tanggal_bergabung ? $karyawan->tanggal_bergabung->format('Y-m-d') : '') }}">
                  @error('tanggal_bergabung')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-12">
                  <label class="form-label">Keahlian</label>
                  <textarea name="keahlian" class="form-control @error('keahlian') is-invalid @enderror"
                    rows="3" placeholder="Sebutkan keahlian yang dimiliki (pisahkan dengan koma)">{{ old('keahlian', $karyawan->keahlian) }}</textarea>
                  @error('keahlian')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                  <small class="text-muted">Contoh: Manajemen, Leadership, Public Speaking</small>
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
                  <input type="text" name="pendidikan_terakhir" class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
                    placeholder="Contoh: S1, S2, D3" value="{{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir) }}">
                  @error('pendidikan_terakhir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Institusi Pendidikan</label>
                  <input type="text" name="institusi_pendidikan" class="form-control @error('institusi_pendidikan') is-invalid @enderror"
                    placeholder="Nama universitas/sekolah" value="{{ old('institusi_pendidikan', $karyawan->institusi_pendidikan) }}">
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
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('data-pekerjaan-tab').click()">
                  <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                </button>
                <div>
                  <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary me-2">
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
    // Find which tab has errors and activate it
    @foreach($errors->keys() as $field)
    @if(in_array($field, ['nama', 'nik', 'nip', 'email', 'jenis_kelamin', 'no_telepon', 'tempat_lahir', 'tanggal_lahir', 'alamat']))
    document.getElementById('data-pribadi-tab').click();
    @break
    @elseif(in_array($field, ['posisi', 'departemen', 'status_kepegawaian', 'tanggal_bergabung', 'keahlian']))
    document.getElementById('data-pekerjaan-tab').click();
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