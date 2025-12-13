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
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
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
              <i class="ri-file-check-line me-2"></i>Kelengkapan Dokumen
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="terapi-tab" data-bs-toggle="tab" data-bs-target="#terapi"
              type="button" role="tab" aria-controls="terapi" aria-selected="false">
              <i class="ri-hospital-line me-2"></i>Program Terapi
            </button>
          </li>
        </ul>

        <form action="{{ route('anak-didik.update', $anakDidik->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Data Diri Tab -->
            <div class="tab-pane fade show active" id="data-diri" role="tabpanel" aria-labelledby="data-diri-tab">
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
                  <label class="form-label">Guru Fokus <span class="text-danger">*</span></label>
                  <select name="guru_fokus_id" class="form-select @error('guru_fokus_id') is-invalid @enderror" required>
                    <option value="">Pilih Guru Fokus</option>
                    @foreach($guruFokusList as $id => $nama)
                    <option value="{{ $id }}" {{ old('guru_fokus_id', $anakDidik->guru_fokus_id) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                  </select>
                  @error('guru_fokus_id')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
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
                <div class="col-md-6">
                  <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                  <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                    value="{{ old('tanggal_lahir', $anakDidik->tanggal_lahir?->format('Y-m-d')) }}" required>
                  @error('tanggal_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Tempat Lahir</label>
                  <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                    placeholder="Kota/Kabupaten" value="{{ old('tempat_lahir', $anakDidik->tempat_lahir) }}">
                  @error('tempat_lahir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">NIK (Nomor Identitas)</label>
                  <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                    placeholder="Nomor NIK" value="{{ old('nik', $anakDidik->nik) }}">
                  @error('nik')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">Alamat</label>
                  <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"
                    placeholder="Masukkan alamat lengkap">{{ old('alamat', $anakDidik->alamat) }}</textarea>
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
            </div>

            <!-- Data Keluarga Tab -->
            <div class="tab-pane fade" id="data-keluarga" role="tabpanel" aria-labelledby="data-keluarga-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nomor KK (Kartu Keluarga)</label>
                  <input type="text" name="no_kk" class="form-control @error('no_kk') is-invalid @enderror"
                    placeholder="Nomor Kartu Keluarga" value="{{ old('no_kk', $anakDidik->no_kk) }}">
                  @error('no_kk')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">No. Akta Kelahiran</label>
                  <input type="text" name="no_akta_kelahiran" class="form-control @error('no_akta_kelahiran') is-invalid @enderror"
                    placeholder="Nomor Akta Kelahiran" value="{{ old('no_akta_kelahiran', $anakDidik->no_akta_kelahiran) }}">
                  @error('no_akta_kelahiran')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Orang Tua/Wali</label>
                  <input type="text" name="nama_orang_tua" class="form-control @error('nama_orang_tua') is-invalid @enderror"
                    placeholder="Nama orang tua/wali" value="{{ old('nama_orang_tua', $anakDidik->nama_orang_tua) }}">
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

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Jumlah Saudara Kandung</label>
                  <input type="number" name="jumlah_saudara_kandung" class="form-control @error('jumlah_saudara_kandung') is-invalid @enderror"
                    placeholder="Jumlah saudara" value="{{ old('jumlah_saudara_kandung', $anakDidik->jumlah_saudara_kandung) }}"
                    min="0">
                  @error('jumlah_saudara_kandung')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Anak Ke-</label>
                  <input type="number" name="anak_ke" class="form-control @error('anak_ke') is-invalid @enderror"
                    placeholder="Anak ke berapa" value="{{ old('anak_ke', $anakDidik->anak_ke) }}" min="1">
                  @error('anak_ke')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">Tinggal Bersama</label>
                  <input type="text" name="tinggal_bersama" class="form-control @error('tinggal_bersama') is-invalid @enderror"
                    placeholder="Contoh: Orang Tua, Nenek, dll" value="{{ old('tinggal_bersama', $anakDidik->tinggal_bersama) }}">
                  @error('tinggal_bersama')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Data Kesehatan Tab -->
            <div class="tab-pane fade" id="data-kesehatan" role="tabpanel" aria-labelledby="data-kesehatan-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Tinggi Badan (cm)</label>
                  <input type="number" name="tinggi_badan" class="form-control @error('tinggi_badan') is-invalid @enderror"
                    placeholder="Dalam satuan cm" value="{{ old('tinggi_badan', $anakDidik->tinggi_badan) }}" step="0.1" min="0">
                  @error('tinggi_badan')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
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
            </div>

            <!-- Data Pendidikan Tab -->
            <div class="tab-pane fade" id="data-pendidikan" role="tabpanel" aria-labelledby="data-pendidikan-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Pendidikan Terakhir</label>
                  <input type="text" name="pendidikan_terakhir" class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
                    placeholder="Contoh: TK, SD, SMP, dll" value="{{ old('pendidikan_terakhir', $anakDidik->pendidikan_terakhir) }}">
                  @error('pendidikan_terakhir')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Asal Sekolah</label>
                  <input type="text" name="asal_sekolah" class="form-control @error('asal_sekolah') is-invalid @enderror"
                    placeholder="Nama sekolah asal" value="{{ old('asal_sekolah', $anakDidik->asal_sekolah) }}">
                  @error('asal_sekolah')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">Tanggal Pendaftaran</label>
                  <input type="date" name="tanggal_pendaftaran" class="form-control @error('tanggal_pendaftaran') is-invalid @enderror"
                    value="{{ old('tanggal_pendaftaran', $anakDidik->tanggal_pendaftaran?->format('Y-m-d')) }}">
                  @error('tanggal_pendaftaran')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
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
            </div>

            <!-- Program Terapi Tab -->
            <div class="tab-pane fade" id="terapi" role="tabpanel" aria-labelledby="terapi-tab">
              <div class="alert alert-info mb-4" role="alert">
                <i class="ri-information-line me-2"></i>
                <strong>Program Terapi:</strong> Informasi tentang program terapi yang sedang diikuti oleh anak.
              </div>

              @if($anakDidik->therapyPrograms && $anakDidik->therapyPrograms->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>Jenis Terapi</th>
                      <th>Tanggal Mulai</th>
                      <th>Tanggal Selesai</th>
                      <th>Status</th>
                      <th>Catatan</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($anakDidik->therapyPrograms as $therapy)
                    <tr>
                      <td>
                        @if($therapy->type_therapy === 'si')
                        <span class="badge bg-label-primary">Sensori Integrasi</span>
                        @elseif($therapy->type_therapy === 'wicara')
                        <span class="badge bg-label-success">Terapi Wicara</span>
                        @elseif($therapy->type_therapy === 'perilaku')
                        <span class="badge bg-label-info">Terapi Perilaku</span>
                        @endif
                      </td>
                      <td>{{ $therapy->tanggal_mulai?->format('d/m/Y') ?? '-' }}</td>
                      <td>{{ $therapy->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                      <td>
                        @if($therapy->is_active)
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-danger">Tidak Aktif</span>
                        @endif
                      </td>
                      <td><small>{{ $therapy->notes ?? '-' }}</small></td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @else
              <div class="alert alert-warning" role="alert">
                <i class="ri-alert-line me-2"></i>
                Belum ada program terapi yang terdaftar untuk anak ini.
              </div>
              @endif
            </div>
          </div>

          <hr class="my-4">

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