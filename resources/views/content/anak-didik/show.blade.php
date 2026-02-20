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
        <div class="d-flex gap-2">
          <a href="{{ route('anak-didik.export-pdf', $anakDidik->id) }}" class="d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:50%;min-width:44px;min-height:44px;background:transparent;">
            <i class="ri-file-pdf-line" style="font-size:1.7em;color:#F44336;"></i>
          </a>
          <a href="{{ route('anak-didik.export-pdf', $anakDidik->id) }}" class="btn btn-danger btn-sm d-none d-sm-inline-flex align-items-center" target="_blank">
            <i class="ri-file-pdf-line me-1"></i> Export PDF
          </a>
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
        <ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto" role="tablist" style="scrollbar-width:thin;">
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
            <button class="nav-link" id="vokasi-tab" data-bs-toggle="tab" data-bs-target="#vokasi"
              type="button" role="tab" aria-controls="vokasi" aria-selected="false">
              <i class="ri-award-line me-2"></i>Vokasi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#dokumen"
              type="button" role="tab" aria-controls="dokumen" aria-selected="false">
              <i class="ri-file-list-3-line me-2"></i>Kelengkapan Dokumen
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Data Diri Tab -->
          <div class="tab-pane fade show active" id="data-diri" role="tabpanel" aria-labelledby="data-diri-tab">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" value="{{ $anakDidik->nama }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">NIS</label>
                <input type="text" class="form-control" value="{{ $anakDidik->nis ?: '-' }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Guru Fokus</label>
                <input type="text" class="form-control" value="{{ $anakDidik->guruFokus ? $anakDidik->guruFokus->nama : '-' }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Jenis Kelamin</label>
                <input type="text" class="form-control" value="{{ ucfirst($anakDidik->jenis_kelamin) }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tanggal Lahir</label>
                <input type="text" class="form-control" value="{{ $anakDidik->tanggal_lahir ? $anakDidik->tanggal_lahir->format('d-m-Y') : '-' }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control" value="{{ $anakDidik->tempat_lahir }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">NIK (Nomor Identitas)</label>
                <input type="text" class="form-control" value="{{ $anakDidik->nik }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Alamat</label>
                <input type="text" class="form-control" value="{{ $anakDidik->alamat }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">No. Telepon</label>
                <input type="text" class="form-control" value="{{ $anakDidik->no_telepon }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="{{ $anakDidik->email }}" readonly>
              </div>
            </div>
          </div>

          <!-- Data Keluarga Tab -->
          <div class="tab-pane fade" id="data-keluarga" role="tabpanel" aria-labelledby="data-keluarga-tab">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nomor KK (Kartu Keluarga)</label>
                <input type="text" class="form-control" value="{{ $anakDidik->no_kk }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">No. Akta Kelahiran</label>
                <input type="text" class="form-control" value="{{ $anakDidik->no_akta_kelahiran }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nama Orang Tua/Wali</label>
                <input type="text" class="form-control" value="{{ $anakDidik->nama_orang_tua }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">No. Telepon Orang Tua</label>
                <input type="text" class="form-control" value="{{ $anakDidik->no_telepon_orang_tua }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Jumlah Saudara Kandung</label>
                <input type="text" class="form-control" value="{{ $anakDidik->jumlah_saudara_kandung }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Anak Ke-</label>
                <input type="text" class="form-control" value="{{ $anakDidik->anak_ke }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Tinggal Bersama</label>
                <input type="text" class="form-control" value="{{ $anakDidik->tinggal_bersama }}" readonly>
              </div>
            </div>
          </div>

          <!-- Data Kesehatan Tab -->
          <div class="tab-pane fade" id="data-kesehatan" role="tabpanel" aria-labelledby="data-kesehatan-tab">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Tinggi Badan (cm)</label>
                <input type="text" class="form-control" value="{{ $anakDidik->tinggi_badan }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Berat Badan (kg)</label>
                <input type="text" class="form-control" value="{{ $anakDidik->berat_badan }}" readonly>
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
                <input type="text" class="form-control" value="{{ $anakDidik->pendidikan_terakhir }}" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Asal Sekolah</label>
                <input type="text" class="form-control" value="{{ $anakDidik->asal_sekolah }}" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-12">
                <label class="form-label">Tanggal Pendaftaran</label>
                <input type="text" class="form-control" value="{{ $anakDidik->tanggal_pendaftaran ? $anakDidik->tanggal_pendaftaran->format('d-m-Y') : '-' }}" readonly>
              </div>
            </div>
          </div>

          <!-- Vokasi Tab -->
          <div class="tab-pane fade" id="vokasi" role="tabpanel" aria-labelledby="vokasi-tab">
            @php
            $selectedJenis = $anakDidik->vokasi_diikuti ?? [];
            if (is_string($selectedJenis)) {
            $decoded = json_decode($selectedJenis, true);
            $selectedJenis = is_array($decoded) ? $decoded : [];
            }
            @endphp
            <div class="alert alert-info mb-4" role="alert">
              <i class="ri-information-line me-2"></i>
              <strong>Vokasi:</strong> Jenis vokasi yang dipilih untuk anak didik.
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_painting_s" disabled value="Painting" {{ (is_array($selectedJenis) && in_array('Painting', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_painting_s">Painting</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_cooking_s" disabled value="Cooking" {{ (is_array($selectedJenis) && in_array('Cooking', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_cooking_s">Cooking</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_craft_s" disabled value="Craft" {{ (is_array($selectedJenis) && in_array('Craft', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_craft_s">Craft</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_computer_s" disabled value="Computer" {{ (is_array($selectedJenis) && in_array('Computer', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_computer_s">Computer</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_gardening_s" disabled value="Gardening" {{ (is_array($selectedJenis) && in_array('Gardening', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_gardening_s">Gardening</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_beauty_s" disabled value="Beauty" {{ (is_array($selectedJenis) && in_array('Beauty', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_beauty_s">Beauty</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_autowash_s" disabled value="Auto Wash" {{ (is_array($selectedJenis) && in_array('Auto Wash', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_autowash_s">Auto Wash</label>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="jenis_housekeeping_s" disabled value="House Keeping" {{ (is_array($selectedJenis) && in_array('House Keeping', $selectedJenis)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="jenis_housekeeping_s">House Keeping</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Dokumen Tab -->
          <div class="tab-pane fade" id="dokumen" role="tabpanel" aria-labelledby="dokumen-tab">
            <!-- <div class="alert alert-info mb-4" role="alert">
              <i class="ri-information-line me-2"></i>
              <strong>Kelengkapan Dokumen:</strong> Centang setiap dokumen yang telah diterima dan disimpan.
            </div> -->

            <div class="row">
              <div class="col-md-6">
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="kk" disabled {{ $anakDidik->kk ? 'checked' : '' }}>
                  <label class="form-check-label" for="kk">Kartu Keluarga (KK)</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="ktp_orang_tua" disabled {{ $anakDidik->ktp_orang_tua ? 'checked' : '' }}>
                  <label class="form-check-label" for="ktp_orang_tua">KTP Orang Tua</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="akta_kelahiran" disabled {{ $anakDidik->akta_kelahiran ? 'checked' : '' }}>
                  <label class="form-check-label" for="akta_kelahiran">Akta Kelahiran</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="foto_anak" disabled {{ $anakDidik->foto_anak ? 'checked' : '' }}>
                  <label class="form-check-label" for="foto_anak">Foto Anak</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="pemeriksaan_tes_rambut" disabled {{ $anakDidik->pemeriksaan_tes_rambut ? 'checked' : '' }}>
                  <label class="form-check-label" for="pemeriksaan_tes_rambut">Pemeriksaan Tes Rambut</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="anamnesa" disabled {{ $anakDidik->anamnesa ? 'checked' : '' }}>
                  <label class="form-check-label" for="anamnesa">Anamnesa</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="tes_iq" disabled {{ $anakDidik->tes_iq ? 'checked' : '' }}>
                  <label class="form-check-label" for="tes_iq">Tes IQ</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="pemeriksaan_dokter_lab" disabled {{ $anakDidik->pemeriksaan_dokter_lab ? 'checked' : '' }}>
                  <label class="form-check-label" for="pemeriksaan_dokter_lab">Pemeriksaan Dokter / Lab</label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="surat_pernyataan" disabled {{ $anakDidik->surat_pernyataan ? 'checked' : '' }}>
                  <label class="form-check-label" for="surat_pernyataan">Surat Pernyataan</label>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- End of detail view, no form tag -->
      </div>
    </div>
  </div>
</div>
@endsection