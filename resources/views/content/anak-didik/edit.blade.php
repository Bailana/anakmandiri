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
        <div class="d-flex align-items-center gap-2">
          <form id="toggleStatusForm" onsubmit="return false;" class="me-2">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="statusToggle" name="status" value="aktif" {{ old('status', $anakDidik->status) === 'aktif' ? 'checked' : '' }}>
              <label class="form-check-label" for="statusToggle" style="user-select:none;cursor:pointer;">
                <span id="statusLabel">{{ old('status', $anakDidik->status) === 'aktif' ? 'Aktif' : 'Non Aktif' }}</span>
              </label>
            </div>
          </form>
          <a href="{{ route('anak-didik.index') }}" class="btn p-0 border-0 bg-transparent d-inline-flex d-sm-none align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-arrow-left-circle-fill" style="font-size:2em;font-weight:bold;"></i>
          </a>
          <a href="{{ route('anak-didik.index') }}" class="btn btn-secondary btn-sm d-none d-sm-inline-flex align-items-center">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Nav Tabs -->
        <div style="overflow-x:auto; white-space:nowrap;">
          <ul class="nav nav-tabs mb-3 flex-nowrap" role="tablist" style="min-width:600px;">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="data-diri-tab" data-bs-toggle="tab" data-bs-target="#data-diri"
                type="button" role="tab" aria-controls="data-diri" aria-selected="true">
                <i class="ri-user-line me-2"></i>Data Diri
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="data-keluarga-tab" data-bs-toggle="tab" data-bs-target="#data-keluarga"
                type="button" role="tab" aria-controls="data-keluarga" aria-selected="false">
                <i class="ri-home-heart-line me-2"></i>Data Keluarga
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="data-kesehatan-tab" data-bs-toggle="tab" data-bs-target="#data-kesehatan"
                type="button" role="tab" aria-controls="data-kesehatan" aria-selected="false">
                <i class="ri-heart-pulse-line me-2"></i>Data Kesehatan
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="data-pendidikan-tab" data-bs-toggle="tab" data-bs-target="#data-pendidikan"
                type="button" role="tab" aria-controls="data-pendidikan" aria-selected="false">
                <i class="ri-book-line me-2"></i>Data Pendidikan
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#dokumen"
                type="button" role="tab" aria-controls="dokumen" aria-selected="false">
                <i class="ri-file-list-3-line me-2"></i>Kelengkapan Dokumen
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="vokasi-tab" data-bs-toggle="tab" data-bs-target="#vokasi"
                type="button" role="tab" aria-controls="vokasi" aria-selected="false">
                <i class="ri-award-line me-2"></i>Vokasi
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <!-- Program Terapi tab removed -->
            </li>
          </ul>
        </div>

        <form id="anakDidikEditForm" action="{{ route('anak-didik.update', $anakDidik->id) }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          @method('PUT')

          <input type="hidden" name="status" id="statusInput" value="{{ old('status', $anakDidik->status) }}">

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Data Diri Tab -->
            <div class="tab-pane fade show active" id="data-diri" role="tabpanel" aria-labelledby="data-diri-tab">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                  <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                    placeholder="Masukkan nama lengkap" value="{{ old('nama', $anakDidik->nama) }}" required>
                  @error('nama')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">NIS <span class="text-danger">*</span></label>
                  <input type="text" name="nis" id="nis" inputmode="numeric" pattern="\\d*" maxlength="20"
                    oninput="this.value=this.value.replace(/\\D/g,'').slice(0,20)"
                    class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Siswa" value="{{ old('nis', $anakDidik->nis) }}" required>
                  @error('nis')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Guru Fokus</label>
                  <select name="guru_fokus_id" class="form-select @error('guru_fokus_id') is-invalid @enderror">
                    <option value="">Pilih Guru Fokus</option>
                    @foreach($guruFokusList as $id => $nama)
                    <option value="{{ $id }}" {{ old('guru_fokus_id', $anakDidik->guru_fokus_id) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                  </select>
                  @error('guru_fokus_id')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Guru Vokasi</label>
                  <input type="text" class="form-control" value="-" readonly>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                  <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror"
                    required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="laki-laki" {{ old('jenis_kelamin', $anakDidik->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>
                      Laki-laki
                    </option>
                    <option value="perempuan" {{ old('jenis_kelamin', $anakDidik->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>
                      Perempuan
                    </option>
                  </select>
                  @error('jenis_kelamin')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                    value="{{ old('tanggal_lahir', $anakDidik->tanggal_lahir?->format('Y-m-d')) }}" required>
                  @error('tanggal_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                    placeholder="Kota/Kabupaten" value="{{ old('tempat_lahir', $anakDidik->tempat_lahir) }}">
                  @error('tempat_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">NIK (Nomor Identitas)</label>
                  <input type="text" name="nik" id="nik" inputmode="numeric" pattern="\\d*" maxlength="16"
                    oninput="this.value=this.value.replace(/\\D/g,'').slice(0,16)"
                    class="form-control @error('nik') is-invalid @enderror" placeholder="Nomor NIK" value="{{ old('nik', $anakDidik->nik) }}">
                  @error('nik')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"
                    placeholder="Masukkan alamat lengkap">{{ old('alamat', $anakDidik->alamat) }}</textarea>
                  @error('alamat')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>


              <div class="d-flex justify-content-between mt-4">
                <div>
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" disabled>
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" disabled aria-hidden="true">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-primary d-none d-sm-inline" onclick="document.getElementById('data-keluarga-tab').click()">
                    Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-keluarga-tab').click()" aria-label="Selanjutnya">
                    <i class="ri-arrow-right-line"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Data Keluarga Tab -->
            <div class="tab-pane fade" id="data-keluarga" role="tabpanel" aria-labelledby="data-keluarga-tab">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nomor KK (Kartu Keluarga)</label>
                  <input type="text" name="no_kk" id="no_kk" inputmode="numeric" pattern="\\d*" maxlength="16"
                    oninput="this.value=this.value.replace(/\\D/g,'').slice(0,16)"
                    class="form-control @error('no_kk') is-invalid @enderror" placeholder="Nomor Kartu Keluarga" value="{{ old('no_kk', $anakDidik->no_kk) }}">
                  @error('no_kk')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">No. Akta Kelahiran</label>
                  <input type="text" name="no_akta_kelahiran" class="form-control @error('no_akta_kelahiran') is-invalid @enderror"
                    placeholder="Nomor Akta Kelahiran" value="{{ old('no_akta_kelahiran', $anakDidik->no_akta_kelahiran) }}">
                  @error('no_akta_kelahiran')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Orang Tua/Wali</label>
                  <input type="text" name="nama_orang_tua" class="form-control @error('nama_orang_tua') is-invalid @enderror"
                    placeholder="Nama orang tua/wali" value="{{ old('nama_orang_tua', $anakDidik->nama_orang_tua) }}">
                  @error('nama_orang_tua')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">No. Telepon Orang Tua</label>
                  <input type="tel" name="no_telepon_orang_tua" id="no_telepon_orang_tua" inputmode="tel" pattern="\\d*" maxlength="13"
                    oninput="this.value=this.value.replace(/\\D/g,'').slice(0,13)"
                    class="form-control @error('no_telepon_orang_tua') is-invalid @enderror" placeholder="08xx-xxxx-xxxx" value="{{ old('no_telepon_orang_tua', $anakDidik->no_telepon_orang_tua) }}">
                  @error('no_telepon_orang_tua')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Jumlah Saudara Kandung</label>
                  <input type="number" name="jumlah_saudara_kandung" class="form-control @error('jumlah_saudara_kandung') is-invalid @enderror"
                    placeholder="Jumlah saudara" value="{{ old('jumlah_saudara_kandung', $anakDidik->jumlah_saudara_kandung) }}"
                    min="0">
                  @error('jumlah_saudara_kandung')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Anak Ke-</label>
                  <input type="number" name="anak_ke" class="form-control @error('anak_ke') is-invalid @enderror"
                    placeholder="Anak ke berapa" value="{{ old('anak_ke', $anakDidik->anak_ke) }}" min="1">
                  @error('anak_ke')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Tinggal Bersama</label>
                  <input type="text" name="tinggal_bersama" class="form-control @error('tinggal_bersama') is-invalid @enderror"
                    placeholder="Contoh: Orang Tua, Nenek, dll" value="{{ old('tinggal_bersama', $anakDidik->tinggal_bersama) }}">
                  @error('tinggal_bersama')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <div>
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" onclick="document.getElementById('data-diri-tab').click()">
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-diri-tab').click()" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-primary d-none d-sm-inline" onclick="document.getElementById('data-kesehatan-tab').click()">
                    Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-kesehatan-tab').click()" aria-label="Selanjutnya">
                    <i class="ri-arrow-right-line"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Data Kesehatan Tab -->
            <div class="tab-pane fade" id="data-kesehatan" role="tabpanel" aria-labelledby="data-kesehatan-tab">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tinggi Badan (cm)</label>
                  <input type="number" name="tinggi_badan" class="form-control @error('tinggi_badan') is-invalid @enderror"
                    placeholder="Dalam satuan cm" value="{{ old('tinggi_badan', $anakDidik->tinggi_badan) }}" step="0.1" min="0">
                  @error('tinggi_badan')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Berat Badan (kg)</label>
                  <input type="number" name="berat_badan" class="form-control @error('berat_badan') is-invalid @enderror"
                    placeholder="Dalam satuan kg" value="{{ old('berat_badan', $anakDidik->berat_badan) }}" step="0.1" min="0">
                  @error('berat_badan')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="alert alert-info" role="alert">
                <i class="ri-information-line me-2"></i>
                <strong>Catatan:</strong> Data kesehatan dan pengukuran dapat diperbarui secara berkala seiring perkembangan anak.
              </div>
              <div class="d-flex justify-content-between mt-4">
                <div>
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" onclick="document.getElementById('data-keluarga-tab').click()">
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-keluarga-tab').click()" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-primary d-none d-sm-inline" onclick="document.getElementById('data-pendidikan-tab').click()">
                    Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-pendidikan-tab').click()" aria-label="Selanjutnya">
                    <i class="ri-arrow-right-line"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Data Pendidikan Tab -->
            <div class="tab-pane fade" id="data-pendidikan" role="tabpanel" aria-labelledby="data-pendidikan-tab">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pendidikan Terakhir</label>
                  <select name="pendidikan_terakhir" class="form-select @error('pendidikan_terakhir') is-invalid @enderror">
                    <option value="">Pilih Pendidikan</option>
                    <option value="TK" {{ old('pendidikan_terakhir', $anakDidik->pendidikan_terakhir) === 'TK' ? 'selected' : '' }}>TK</option>
                    <option value="SD" {{ old('pendidikan_terakhir', $anakDidik->pendidikan_terakhir) === 'SD' ? 'selected' : '' }}>SD</option>
                    <option value="SMP" {{ old('pendidikan_terakhir', $anakDidik->pendidikan_terakhir) === 'SMP' ? 'selected' : '' }}>SMP</option>
                    <option value="SMA" {{ old('pendidikan_terakhir', $anakDidik->pendidikan_terakhir) === 'SMA' ? 'selected' : '' }}>SMA</option>
                  </select>
                  @error('pendidikan_terakhir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Asal Sekolah</label>
                  <input type="text" name="asal_sekolah" class="form-control @error('asal_sekolah') is-invalid @enderror"
                    placeholder="Nama sekolah asal" value="{{ old('asal_sekolah', $anakDidik->asal_sekolah) }}">
                  @error('asal_sekolah')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Tanggal Pendaftaran</label>
                  <input type="date" name="tanggal_pendaftaran" class="form-control @error('tanggal_pendaftaran') is-invalid @enderror"
                    value="{{ old('tanggal_pendaftaran', $anakDidik->tanggal_pendaftaran?->format('Y-m-d')) }}">
                  @error('tanggal_pendaftaran')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <div>
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" onclick="document.getElementById('data-kesehatan-tab').click()">
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-kesehatan-tab').click()" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-primary d-none d-sm-inline" onclick="document.getElementById('dokumen-tab').click()">
                    Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('dokumen-tab').click()" aria-label="Selanjutnya">
                    <i class="ri-arrow-right-line"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Dokumen Tab -->
            <div class="tab-pane fade" id="dokumen" role="tabpanel" aria-labelledby="dokumen-tab">
              <div class="alert alert-info mb-4" role="alert">
                <i class="ri-information-line me-2"></i>
                <strong>Kelengkapan Dokumen:</strong> Centang setiap dokumen yang telah diterima dan disimpan.
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="kk" name="kk" value="1"
                      {{ old('kk', $anakDidik->kk) ? 'checked' : '' }}>
                    <label class="form-check-label" for="kk">
                      Kartu Keluarga (KK)
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="ktp_orang_tua" name="ktp_orang_tua" value="1"
                      {{ old('ktp_orang_tua', $anakDidik->ktp_orang_tua) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ktp_orang_tua">
                      KTP Orang Tua
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="akta_kelahiran" name="akta_kelahiran" value="1"
                      {{ old('akta_kelahiran', $anakDidik->akta_kelahiran) ? 'checked' : '' }}>
                    <label class="form-check-label" for="akta_kelahiran">
                      Akta Kelahiran
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="foto_anak" name="foto_anak" value="1"
                      {{ old('foto_anak', $anakDidik->foto_anak) ? 'checked' : '' }}>
                    <label class="form-check-label" for="foto_anak">
                      Foto Anak
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="pemeriksaan_tes_rambut" name="pemeriksaan_tes_rambut"
                      value="1" {{ old('pemeriksaan_tes_rambut', $anakDidik->pemeriksaan_tes_rambut) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pemeriksaan_tes_rambut">
                      Pemeriksaan Tes Rambut
                    </label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="anamnesa" name="anamnesa" value="1"
                      {{ old('anamnesa', $anakDidik->anamnesa) ? 'checked' : '' }}>
                    <label class="form-check-label" for="anamnesa">
                      Anamnesa
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="tes_iq" name="tes_iq" value="1"
                      {{ old('tes_iq', $anakDidik->tes_iq) ? 'checked' : '' }}>
                    <label class="form-check-label" for="tes_iq">
                      Tes IQ
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="pemeriksaan_dokter_lab" name="pemeriksaan_dokter_lab"
                      value="1" {{ old('pemeriksaan_dokter_lab', $anakDidik->pemeriksaan_dokter_lab) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pemeriksaan_dokter_lab">
                      Pemeriksaan Dokter / Lab
                    </label>
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="surat_pernyataan" name="surat_pernyataan" value="1"
                      {{ old('surat_pernyataan', $anakDidik->surat_pernyataan) ? 'checked' : '' }}>
                    <label class="form-check-label" for="surat_pernyataan">
                      Surat Pernyataan
                    </label>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <div>
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" onclick="document.getElementById('data-pendidikan-tab').click()">
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('data-pendidikan-tab').click()" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-primary d-none d-sm-inline" onclick="document.getElementById('vokasi-tab').click()">
                    Selanjutnya<i class="ri-arrow-right-line ms-2"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('vokasi-tab').click()" aria-label="Selanjutnya">
                    <i class="ri-arrow-right-line"></i>
                  </button>
                </div>
              </div>

            </div>

            <!-- Program Terapi removed -->
            <!-- Vokasi Tab -->
            <div class="tab-pane fade" id="vokasi" role="tabpanel" aria-labelledby="vokasi-tab">
              @php
              $selectedJenis = old('jenis_vokasi', $anakDidik->vokasi_diikuti ?? []);
              if (is_string($selectedJenis)) {
              $decoded = json_decode($selectedJenis, true);
              $selectedJenis = is_array($decoded) ? $decoded : [];
              }
              @endphp
              <div class="alert alert-info mb-4" role="alert">
                <i class="ri-information-line me-2"></i>
                <strong>Vokasi:</strong> Pilih jenis vokasi yang relevan untuk anak didik.
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_painting_e" name="jenis_vokasi[]" value="Painting" {{ (is_array($selectedJenis) && in_array('Painting', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_painting_e">Painting</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_cooking_e" name="jenis_vokasi[]" value="Cooking" {{ (is_array($selectedJenis) && in_array('Cooking', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_cooking_e">Cooking</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_craft_e" name="jenis_vokasi[]" value="Craft" {{ (is_array($selectedJenis) && in_array('Craft', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_craft_e">Craft</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_computer_e" name="jenis_vokasi[]" value="Computer" {{ (is_array($selectedJenis) && in_array('Computer', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_computer_e">Computer</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_gardening_e" name="jenis_vokasi[]" value="Gardening" {{ (is_array($selectedJenis) && in_array('Gardening', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_gardening_e">Gardening</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_beauty_e" name="jenis_vokasi[]" value="Beauty" {{ (is_array($selectedJenis) && in_array('Beauty', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_beauty_e">Beauty</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_autowash_e" name="jenis_vokasi[]" value="Auto Wash" {{ (is_array($selectedJenis) && in_array('Auto Wash', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_autowash_e">Auto Wash</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="jenis_housekeeping_e" name="jenis_vokasi[]" value="House Keeping" {{ (is_array($selectedJenis) && in_array('House Keeping', $selectedJenis)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="jenis_housekeeping_e">House Keeping</label>
                  </div>
                </div>
              </div>
              <div class="d-flex mt-4">
                <div class="me-2">
                  <button type="button" class="btn btn-outline-secondary d-none d-sm-inline" onclick="document.getElementById('dokumen-tab').click()">
                    <i class="ri-arrow-left-line me-2"></i>Sebelumnya
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-inline d-sm-none rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;" onclick="document.getElementById('dokumen-tab').click()" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-line"></i>
                  </button>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2 flex-nowrap" style="min-width:0;">
                  <a href="{{ route('anak-didik.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-close-line me-2"></i>Batal
                  </a>
                  <button id="anakDidikEditSaveBtnBottom" type="submit" class="btn btn-primary btn-sm">
                    <i class="ri-save-line me-2"></i>Simpan
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

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const statusToggle = document.getElementById('statusToggle');
    const statusLabel = document.getElementById('statusLabel');
    const statusInput = document.getElementById('statusInput');

    if (!statusToggle) return;

    // Initialize UI from hidden input (source of truth)
    const initialStatus = (statusInput && statusInput.value) ? statusInput.value : '{{ $anakDidik->status ?? "nonaktif" }}';
    const initialChecked = initialStatus === 'aktif';
    statusToggle.checked = initialChecked;
    statusLabel.textContent = initialChecked ? 'Aktif' : 'Non Aktif';

    let pending = false;

    statusToggle.addEventListener('change', function() {
      if (pending) return; // avoid duplicate requests

      // desired status after this interaction
      const desiredStatus = statusToggle.checked ? 'aktif' : 'nonaktif';

      // disable while request in progress
      pending = true;
      statusToggle.disabled = true;

      const body = new URLSearchParams();
      body.append('status', desiredStatus);

      fetch("{{ route('anak-didik.toggle-status', $anakDidik->id) }}", {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: body
        })
        .then(async response => {
          let data = {};
          try {
            data = await response.json();
          } catch (e) {}
          if (!response.ok) {
            if (response.status === 419 || response.status === 401) {
              throw new Error('Sesi berakhir atau token CSRF tidak valid. Silakan refresh halaman dan coba lagi.');
            }
            throw new Error((data && data.message) ? data.message : 'Gagal memperbarui status');
          }

          const newStatus = (data && data.status) ? data.status : desiredStatus;
          if (statusInput) statusInput.value = newStatus;

          // reflect server state in UI
          const isActive = newStatus === 'aktif';
          statusToggle.checked = isActive;
          statusLabel.textContent = isActive ? 'Aktif' : 'Non Aktif';
          toastr.success('Status anak didik berhasil diperbarui!');
        })
        .catch(err => {
          // revert to value from statusInput (server/source of truth)
          const serverStatus = (statusInput && statusInput.value) ? statusInput.value : initialStatus;
          const serverChecked = serverStatus === 'aktif';
          statusToggle.checked = serverChecked;
          statusLabel.textContent = serverChecked ? 'Aktif' : 'Non Aktif';
          console.error('Toggle status error:', err);
          toastr.error(err.message || 'Gagal memperbarui status!');
        })
        .finally(() => {
          pending = false;
          statusToggle.disabled = false;
        });
    });
  });
</script>
<script>
  // Ensure edit form submits even if a hidden invalid control would block native submit
  (function() {
    var btn = document.getElementById('anakDidikEditSaveBtn');
    var form = document.getElementById('anakDidikEditForm');
    if (btn && form) {
      btn.addEventListener('click', function(e) {
        form.noValidate = true;
        if (!form.__submitted) {
          form.__submitted = true;
          form.submit();
        }
      });
    }
  })();
</script>
@endpush
@endsection