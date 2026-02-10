@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Absensi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
<style>
  .signature-pad {
    border: 3px solid #007bff;
    border-radius: 8px;
    display: block;
    width: 100%;
    height: 250px;
    cursor: crosshair;
    background-color: #fff;
  }

  .location-badge {
    display: inline-block;
    background-color: #dc3545;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
  }

  .location-badge .badge-remove {
    cursor: pointer;
    margin-left: 0.5rem;
    font-weight: bold;
  }

  /* 3D Model Hotspot Styles */
  .body-hotspot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: radial-gradient(circle, #ff0000 0%, #cc0000 70%, transparent 100%);
    border: 2px solid #ffffff;
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.8), 0 0 20px rgba(255, 0, 0, 0.5);
    animation: hotspotPulse 1.5s ease-in-out infinite;
    cursor: pointer;
  }

  @keyframes hotspotPulse {

    0%,
    100% {
      transform: scale(1);
      opacity: 1;
    }

    50% {
      transform: scale(1.3);
      opacity: 0.7;
    }
  }

  .body-hotspot:hover {
    transform: scale(1.4);
    box-shadow: 0 0 15px rgba(255, 0, 0, 1), 0 0 30px rgba(255, 0, 0, 0.8);
  }
</style>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Absensi Anak Didik</h5>
        <a href="{{ route('absensi.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Gagal:</strong>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($absensi->waktu_jemput)
        <!-- Form Edit Data Penjemputan -->
        <div class="alert alert-info mb-4">
          <i class="ri-information-line me-2"></i>
          Anak didik sudah dijemput pada <strong>{{ $absensi->waktu_jemput->format('d M Y H:i:s') }}</strong>.
          Anda dapat mengedit data penjemputan di bawah ini.
        </div>

        <form action="{{ route('absensi.update', $absensi->id) }}" method="POST" enctype="multipart/form-data" id="editJemputForm">
          @csrf
          @method('PUT')
          <input type="hidden" name="edit_type" value="penjemputan">

          <div class="row g-2">
            <!-- Info Absensi (Read-only) -->
            <div class="col-12">
              <h6 class="mb-2"><i class="ri-information-line me-2"></i>Informasi Absensi</h6>
            </div>

            <div class="col-md-4">
              <label class="form-label">Anak Didik</label>
              <input type="text" class="form-control" value="{{ $absensi->anakDidik->nama }}" readonly>
            </div>

            <div class="col-md-4">
              <label class="form-label">Tanggal</label>
              <input type="text" class="form-control" value="{{ $absensi->tanggal->format('d M Y') }}" readonly>
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <input type="text" class="form-control" value="{{ ucfirst($absensi->status) }}" readonly>
            </div>

            <!-- Divider -->
            <div class="col-12">
              <hr class="my-2">
            </div>

            <!-- Data Penjemputan -->
            <div class="col-12">
              <h6 class="mb-2"><i class="ri-user-follow-line me-2"></i>Data Penjemputan</h6>
            </div>

            <div class="col-12">
              <label for="nama_penjemput" class="form-label">Nama Penjemput <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('nama_penjemput') is-invalid @enderror"
                id="nama_penjemput" name="nama_penjemput"
                value="{{ old('nama_penjemput', $absensi->nama_penjemput) }}" required>
              @error('nama_penjemput')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <!-- Foto Penjemput Existing -->
            @if($absensi->foto_penjemput && count((array)$absensi->foto_penjemput) > 0)
            <div class="col-12">
              <label class="form-label">Foto Penjemput Saat Ini ({{ count((array)$absensi->foto_penjemput) }} foto)</label>
              <div class="row g-2" id="existing Photos">
                @foreach((array)$absensi->foto_penjemput as $index => $foto)
                <div class="col-md-3" id="existing-photo-{{ $index }}">
                  <div class="position-relative">
                    <img src="{{ asset('storage/' . $foto) }}" class="img-thumbnail w-100" style="height:150px; object-fit:cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                      onclick="removeExistingPhoto({{ $index }}, '{{ $foto }}')" style="padding:0.1rem 0.3rem;">
                      <i class="ri-close-line"></i>
                    </button>
                  </div>
                  <input type="hidden" name="existing_foto_penjemput[]" value="{{ $foto }}" id="existing-photo-input-{{ $index }}">
                </div>
                @endforeach
              </div>
            </div>
            @endif

            <!-- Upload Foto Baru -->
            <div class="col-12">
              <label class="form-label">Tambah Foto Penjemput Baru</label>
              <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="editCameraBtn">
                  <i class="ri-camera-line me-1"></i>Ambil Foto
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="editUploadBtn">
                  <i class="ri-upload-line me-1"></i>Upload Foto
                </button>
                <input type="file" id="editFileInput" name="foto_penjemput[]" accept="image/*" multiple style="display:none;">
              </div>
              <div id="editCameraContainer" style="display:none;">
                <video id="editCameraStream" autoplay playsinline style="max-width:100%; border-radius:8px;"></video>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="editCaptureBtn">
                  <i class="ri-camera-line me-1"></i>Ambil Foto
                </button>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="editStopCameraBtn">
                  <i class="ri-stop-line me-1"></i>Tutup Kamera
                </button>
              </div>
              <div id="editPreviewContainer" class="mt-3" style="display:none;">
                <label class="form-label fw-semibold">Preview Foto Baru:</label>
                <div id="editPhotoPreview" class="d-flex flex-wrap gap-2"></div>
              </div>
            </div>

            <!-- Tanda Tangan - SELALU BUAT BARU -->
            <div class="col-12">
              <label class="form-label">Tanda Tangan Penjemput <span class="text-danger">*</span></label>
              <p class="text-muted mb-2"><small><i class="ri-information-line"></i> Harap buat tanda tangan baru untuk perubahan data</small></p>
              <canvas id="editSignaturePad" class="signature-pad"></canvas>
              <div class="d-flex gap-2 align-items-center mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="editClearSignature" title="Hapus Tanda Tangan">
                  <i class="ri-delete-bin-line"></i>
                </button>
                <span class="text-muted"><small id="editSignatureStatus">Belum ada tanda tangan</small></span>
              </div>
              <input type="hidden" id="edit_signature_data" name="signature_penjemput" required>
            </div>

            <!-- Keterangan -->
            <div class="col-12">
              <label for="keterangan_penjemput" class="form-label">Keterangan (Opsional)</label>
              <textarea class="form-control" id="keterangan_penjemput" name="keterangan_penjemput" rows="3"
                placeholder="Catatan tambahan tentang penjemputan...">{{ old('keterangan_penjemput', $absensi->keterangan_penjemput) }}</textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-success" title="Simpan Perubahan Penjemputan">
              <i class="ri-save-line"></i>
            </button>
          </div>
        </form>

        @else
        <!-- Form Edit Data Absensi (Belum Dijemput) - SAMA SEPERTI TAMBAH ABSENSI -->
        <form action="{{ route('absensi.update', $absensi->id) }}" method="POST" enctype="multipart/form-data" id="editAbsensiForm">
          @csrf
          @method('PUT')
          <input type="hidden" name="edit_type" value="absensi">
          <input type="hidden" name="anak_didik_id" value="{{ $absensi->anak_didik_id }}">
          <input type="hidden" name="tanggal" value="{{ $absensi->tanggal->format('Y-m-d') }}">

          <!-- Section 1: Informasi Dasar -->
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label for="anak_didik_display" class="form-label">Anak Didik</label>
              <input type="text" class="form-control" id="anak_didik_display" value="{{ $absensi->anakDidik->nama }}" readonly disabled>
              <small class="text-muted">Anak didik tidak dapat diubah setelah absensi dibuat</small>
            </div>

            <div class="col-md-6">
              <label for="tanggal_display" class="form-label">Tanggal</label>
              <input type="text" class="form-control" id="tanggal_display" value="{{ $absensi->tanggal->format('d M Y') }}" readonly disabled>
              <small class="text-muted">Tanggal tidak dapat diubah</small>
            </div>

            <div class="col-md-6">
              <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
              <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="hadir" @selected(old('status', $absensi->status) == 'hadir')>Hadir</option>
                <option value="izin" @selected(old('status', $absensi->status) == 'izin')>Izin</option>
                <option value="alfa" @selected(old('status', $absensi->status) == 'alfa')>Alfa</option>
              </select>
              @error('status')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-12" id="keteranganSection">
              <label for="keterangan" class="form-label">Keterangan <span class="text-danger" id="keteranganRequired" style="display:none;">*</span></label>
              <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                rows="2" placeholder="Catatan tambahan...">{{ old('keterangan', $absensi->keterangan) }}</textarea>
              @error('keterangan')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <hr class="my-4" id="divider1">

          <!-- Section 2: Kondisi Fisik -->
          <div id="kondisiFisikSection" class="mb-4">
            <h6 class="mb-3"><i class="ri-hospital-line me-2"></i>Kondisi Fisik</h6>
            <div class="form-check mb-2">
              <input class="form-check-input kondisi-fisik-radio" type="radio" name="kondisi_fisik" id="kondisi_baik" value="baik"
                @checked(old('kondisi_fisik', $absensi->kondisi_fisik ?? 'baik') == 'baik')>
              <label class="form-check-label" for="kondisi_baik">
                ✓ Kondisi Fisik Baik (Tidak ada tanda luka/lebam)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input kondisi-fisik-radio" type="radio" name="kondisi_fisik" id="kondisi_ada_tanda" value="ada_tanda"
                @checked(old('kondisi_fisik', $absensi->kondisi_fisik) == 'ada_tanda')>
              <label class="form-check-label" for="kondisi_ada_tanda">
                ⚠ Ada Tanda Fisik (Ada lebam/luka yang harus didokumentasikan)
              </label>
            </div>
          </div>

          <hr class="my-4" id="tandaFisikDivider" @if(old('kondisi_fisik', $absensi->kondisi_fisik) == 'ada_tanda') style="display: block;" @else style="display: none;" @endif>

          <!-- Section 3: Detail Tanda Fisik -->
          <div id="tandaFisikSection" @if(old('kondisi_fisik', $absensi->kondisi_fisik) == 'ada_tanda') style="display: block;" @else style="display: none;" @endif>
            <h6 class="mb-3"><i class="ri-alert-line me-2"></i>Detail Tanda Fisik</h6>

            <div class="row g-3 mb-4">
              <div class="col-12">
                <label class="form-label">Jenis Tanda Fisik</label>
                <div class="row">
                  @foreach($jenisTandaFisik as $key => $label)
                  @if($key !== 'baik')
                  <div class="col-md-6 col-lg-4">
                    <div class="form-check mb-2">
                      @php
                      $existingJenis = is_string($absensi->jenis_tanda_fisik) ? explode(',', $absensi->jenis_tanda_fisik) : [];
                      $existingJenis = array_map('trim', $existingJenis);
                      @endphp
                      <input class="form-check-input" type="checkbox" name="jenis_tanda_fisik[]" id="jenis_tanda_fisik_{{ $key }}" value="{{ $key }}"
                        @checked(is_array(old('jenis_tanda_fisik')) ? in_array($key, old('jenis_tanda_fisik')) : in_array($key, $existingJenis))>
                      <label class="form-check-label" for="jenis_tanda_fisik_{{ $key }}">
                        {{ $label }}
                      </label>
                    </div>
                  </div>
                  @endif
                  @endforeach
                </div>
              </div>

              <div class="col-12">
                <label for="keterangan_tanda_fisik" class="form-label">Keterangan Tanda Fisik</label>
                <textarea class="form-control" id="keterangan_tanda_fisik" name="keterangan_tanda_fisik" rows="3"
                  placeholder="Deskripsikan kondisi/tanda fisik yang ditemukan...">{{ old('keterangan_tanda_fisik', $absensi->keterangan_tanda_fisik) }}</textarea>
              </div>
            </div>

            <!-- 3D Body Map -->
            <div class="mb-4">
              <label class="form-label">Lokasi Tanda Fisik di Tubuh</label>
              <p class="text-muted"><small>Putar model 3D untuk melihat bagian tubuh, lalu pilih lokasi tanda fisik</small></p>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <div style="width: 100%; height: 500px; border: 2px solid #ccc; border-radius: 8px; background: linear-gradient(to bottom, #f0f4ff 0%, #e8eefc 100%); position: relative;">
                    <model-viewer id="bodyModel3D"
                      src=""
                      alt="3D Body Model"
                      camera-controls
                      shadow-intensity="1"
                      exposure="1.0"
                      camera-orbit="0deg 75deg 105%"
                      min-camera-orbit="auto auto auto"
                      max-camera-orbit="auto auto auto"
                      interpolation-decay="200"
                      interaction-prompt="none"
                      data-jenis-kelamin="{{ $absensi->anakDidik->jenis_kelamin ?? 'laki-laki' }}"
                      style="width: 100%; height: 100%;">
                    </model-viewer>
                    <div id="model3DLoader" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); pointer-events: none;">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading 3D model...</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; background-color: #f9f9f9;">
                    <h6 class="mb-3">Pilih Lokasi Tanda Fisik:</h6>

                    <!-- Kepala & Wajah -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-user-smile-line me-1"></i> Kepala & Wajah</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Kepala">Kepala</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Wajah">Wajah</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Telinga Kiri">Telinga Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Telinga Kanan">Telinga Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Leher">Leher</button>
                      </div>
                    </div>

                    <!-- Badan Atas -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-heart-pulse-line me-1"></i> Badan Atas</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Dada">Dada</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Perut">Perut</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Punggung Atas">Punggung Atas</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Punggung Bawah">Punggung Bawah</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Pinggang">Pinggang</button>
                      </div>
                    </div>

                    <!-- Lengan Kiri -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-hand-heart-line me-1"></i> Lengan Kiri</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Bahu Kiri">Bahu Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lengan Atas Kiri">Lengan Atas Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Siku Kiri">Siku Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lengan Bawah Kiri">Lengan Bawah Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Pergelangan Tangan Kiri">Pergelangan Tangan Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Jari Tangan Kiri">Jari Tangan Kiri</button>
                      </div>
                    </div>

                    <!-- Lengan Kanan -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-hand-heart-line me-1"></i> Lengan Kanan</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Bahu Kanan">Bahu Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lengan Atas Kanan">Lengan Atas Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Siku Kanan">Siku Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lengan Bawah Kanan">Lengan Bawah Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Pergelangan Tangan Kanan">Pergelangan Tangan Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Jari Tangan Kanan">Jari Tangan Kanan</button>
                      </div>
                    </div>

                    <!-- Kaki Kiri -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-footprint-line me-1"></i> Kaki Kiri</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Paha Kiri">Paha Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lutut Kiri">Lutut Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Betis Kiri">Betis Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Pergelangan Kaki Kiri">Pergelangan Kaki Kiri</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Jari Kaki Kiri">Jari Kaki Kiri</button>
                      </div>
                    </div>

                    <!-- Kaki Kanan -->
                    <div class="mb-3">
                      <strong class="d-block mb-2 text-primary"><i class="ri-footprint-line me-1"></i> Kaki Kanan</strong>
                      <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Paha Kanan">Paha Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Lutut Kanan">Lutut Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Betis Kanan">Betis Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Pergelangan Kaki Kanan">Pergelangan Kaki Kanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary body-part-btn" data-part="Jari Kaki Kanan">Jari Kaki Kanan</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div id="selectedLocations" class="mt-3"></div>
              <input type="hidden" id="lokasi_luka" name="lokasi_luka" value="{{ old('lokasi_luka', json_encode($absensi->lokasi_luka ?? [])) }}">
            </div>

            <!-- Foto Bukti Existing -->
            @if($absensi->foto_bukti && count((array)$absensi->foto_bukti) > 0)
            <div class="mb-3">
              <label class="form-label">Foto Bukti Saat Ini ({{ count((array)$absensi->foto_bukti) }} foto)</label>
              <div class="row g-2" id="existingFotoBukti">
                @foreach((array)$absensi->foto_bukti as $index => $foto)
                <div class="col-md-3" id="existing-foto-bukti-{{ $index }}">
                  <div class="position-relative">
                    <img src="{{ asset('storage/' . $foto) }}" class="img-thumbnail w-100" style="height:150px; object-fit:cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                      onclick="removeExistingFotoBukti({{ $index }}, '{{ $foto }}')" style="padding:0.1rem 0.3rem;">
                      <i class="ri-close-line"></i>
                    </button>
                  </div>
                  <input type="hidden" name="existing_foto_bukti[]" value="{{ $foto }}" id="existing-foto-bukti-input-{{ $index }}">
                </div>
                @endforeach
              </div>
            </div>
            @endif

            <!-- Upload Foto Bukti Baru -->
            <div class="mb-4">
              <label class="form-label">Tambah Foto Bukti Baru</label>
              <input type="file" class="form-control" name="foto_bukti[]" accept="image/*" multiple>
            </div>
          </div>

          <hr class="my-4" id="divider2">

          <!-- Section 4: Verifikasi Orang Tua / Pengantar - SELALU BUAT BARU -->
          <div id="verifikasiOrangTuaSection">
            <h6 class="mb-3"><i class="ri-pen-nib-line me-2"></i>Verifikasi Orang Tua / Pengantar</h6>
            <p class="text-muted"><small><i class="ri-information-line"></i> Harap buat tanda tangan baru untuk perubahan data absensi</small></p>

            <div class="mb-4">
              <label for="nama_pengantar" class="form-label">Nama Orang Tua / Pengantar <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama_pengantar" name="nama_pengantar"
                value="{{ old('nama_pengantar', $absensi->nama_pengantar) }}" required placeholder="Nama lengkap...">
            </div>

            <div class="mb-4">
              <label for="signature_pengantar" class="form-label">Tanda Tangan Orang Tua / Pengantar <span class="text-danger">*</span></label>
              <canvas id="signaturePad" class="signature-pad"></canvas>
              <input type="hidden" id="signature_pengantar" name="signature_pengantar" required>
            </div>

            <div class="d-flex gap-2 align-items-center">
              <button type="button" id="clearSignatureBtn" class="btn btn-sm btn-outline-danger" title="Hapus Tanda Tangan">
                <i class="ri-delete-bin-line"></i>
              </button>
              <span class="text-muted"><small id="signatureStatus">Belum ada tanda tangan</small></span>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-success" title="Simpan Perubahan">
              <i class="ri-save-line"></i>
            </button>
          </div>
        </form>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
<script>
  @if($absensi->waktu_jemput)
  // Edit Penjemputan Scripts
  let editSignaturePad;
  let editCameraStream = null;
  let editCapturedPhotos = [];

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad
    const canvas = document.getElementById('editSignaturePad');
    if (canvas) {
      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (editSignaturePad) {
          editSignaturePad.clear();
        }
      }

      resizeCanvas();
      editSignaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
      });
      window.addEventListener('resize', resizeCanvas);

      // Update signature status with visual feedback
      editSignaturePad.addEventListener('endStroke', () => {
        const statusEl = document.getElementById('editSignatureStatus');
        if (statusEl && !editSignaturePad.isEmpty()) {
          statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
          statusEl.style.color = 'green';
        }
      });

      // Fallback to canvas events for compatibility
      canvas.addEventListener('mouseup', () => {
        setTimeout(() => {
          const statusEl = document.getElementById('editSignatureStatus');
          if (statusEl && !editSignaturePad.isEmpty()) {
            statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
            statusEl.style.color = 'green';
          }
        }, 10);
      });

      canvas.addEventListener('touchend', () => {
        setTimeout(() => {
          const statusEl = document.getElementById('editSignatureStatus');
          if (statusEl && !editSignaturePad.isEmpty()) {
            statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
            statusEl.style.color = 'green';
          }
        }, 10);
      });
    }

    // Camera functionality
    document.getElementById('editCameraBtn')?.addEventListener('click', async function() {
      const container = document.getElementById('editCameraContainer');
      const video = document.getElementById('editCameraStream');

      try {
        editCameraStream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'user'
          }
        });
        video.srcObject = editCameraStream;
        container.style.display = 'block';
      } catch (err) {
        alert('Tidak dapat mengakses kamera: ' + err.message);
      }
    });

    document.getElementById('editStopCameraBtn')?.addEventListener('click', function() {
      if (editCameraStream) {
        editCameraStream.getTracks().forEach(track => track.stop());
        document.getElementById('editCameraStream').srcObject = null;
        document.getElementById('editCameraContainer').style.display = 'none';
      }
    });

    document.getElementById('editCaptureBtn')?.addEventListener('click', function() {
      const video = document.getElementById('editCameraStream');
      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext('2d').drawImage(video, 0, 0);

      canvas.toBlob(blob => {
        const file = new File([blob], `edit_jemput_${Date.now()}.jpg`, {
          type: 'image/jpeg'
        });
        editCapturedPhotos.push(file);
        updateEditPhotoPreview();

        if (editCameraStream) {
          editCameraStream.getTracks().forEach(track => track.stop());
          video.srcObject = null;
          document.getElementById('editCameraContainer').style.display = 'none';
        }
      }, 'image/jpeg', 0.9);
    });

    document.getElementById('editUploadBtn')?.addEventListener('click', function() {
      document.getElementById('editFileInput').click();
    });

    document.getElementById('editFileInput')?.addEventListener('change', function(e) {
      const files = Array.from(e.target.files);
      files.forEach(file => {
        editCapturedPhotos.push(file);
      });
      updateEditPhotoPreview();
      this.value = '';
    });

    document.getElementById('editClearSignature')?.addEventListener('click', function() {
      if (editSignaturePad) {
        editSignaturePad.clear();
        const statusEl = document.getElementById('editSignatureStatus');
        if (statusEl) {
          statusEl.innerHTML = 'Belum ada tanda tangan';
          statusEl.style.color = '#6c757d';
        }
      }
    });

    // Form submission
    document.getElementById('editJemputForm')?.addEventListener('submit', function(e) {
      if (editSignaturePad && editSignaturePad.isEmpty()) {
        e.preventDefault();
        alert('Tanda tangan penjemput harus diisi!');
        return false;
      }

      if (editSignaturePad) {
        const signatureData = editSignaturePad.toDataURL();
        document.getElementById('edit_signature_data').value = signatureData;
      }

      // Add captured photos to file input using DataTransfer
      if (editCapturedPhotos.length > 0) {
        const fileInput = document.getElementById('editFileInput');
        const dataTransfer = new DataTransfer();

        editCapturedPhotos.forEach(file => {
          dataTransfer.items.add(file);
        });

        fileInput.files = dataTransfer.files;
      }
    });
  });

  function updateEditPhotoPreview() {
    const preview = document.getElementById('editPhotoPreview');
    const container = document.getElementById('editPreviewContainer');

    if (editCapturedPhotos.length === 0) {
      container.style.display = 'none';
      return;
    }

    container.style.display = 'block';
    preview.innerHTML = '';

    editCapturedPhotos.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'position-relative';
        div.innerHTML = `
          <img src="${e.target.result}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
          <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                  onclick="removeEditPhoto(${index})" style="padding:0.1rem 0.3rem;">
            <i class="ri-close-line"></i>
          </button>
        `;
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  window.removeEditPhoto = function(index) {
    editCapturedPhotos.splice(index, 1);
    updateEditPhotoPreview();
  };

  window.removeExistingPhoto = function(index, path) {
    if (confirm('Hapus foto ini?')) {
      document.getElementById('existing-photo-' + index).style.display = 'none';
      document.getElementById('existing-photo-input-' + index).remove();
    }
  };
  @else
  // Edit Absensi Biasa Scripts
  let signaturePad;
  let selectedBodyParts = @json($absensi->lokasi_luka ?? []);

  // 3D Model coordinates
  const bodyPartCoordinates = {
    // Kepala & Wajah (y: 1.5-1.7m)
    'Kepala': {
      position: '0.00 1.80 0.00',
      normal: '0 1 0'
    },
    'Wajah': {
      position: '0.00 1.70 0.10',
      normal: '0 1 0'
    },
    'Telinga Kiri': {
      position: '0.10 1.70 -0.01',
      normal: '0 1 0'
    },
    'Telinga Kanan': {
      position: '-0.10 1.70 -0.01',
      normal: '0 1 0'
    },
    'Leher': {
      position: '0.00 1.56 0.00',
      normal: '0 1 0'
    },

    // Badan Atas (y: 1.0-1.4m)
    'Dada': {
      position: '0.00 1.40 0.10',
      normal: '0 1 0'
    },
    'Perut': {
      position: '0.00 1.15 0.12',
      normal: '0 1 0'
    },
    'Punggung Atas': {
      position: '0.00 1.40 -0.16',
      normal: '0 1 0'
    },
    'Punggung Bawah': {
      position: '0.00 1.15 -0.10',
      normal: '0 1 0'
    },
    'Pinggang': {
      position: '0.15 1.05 0.00',
      normal: '0 1 0'
    },

    // Lengan Kiri - Natural Pose (tangan menggantung ke bawah)
    'Bahu Kiri': {
      position: '0.18 1.48 -0.07',
      normal: '0 1 0'
    },
    'Lengan Atas Kiri': {
      position: '0.34 1.40 -0.07',
      normal: '0 1 0'
    },
    'Siku Kiri': {
      position: '0.45 1.35 -0.10',
      normal: '0 1 0'
    },
    'Lengan Bawah Kiri': {
      position: '0.55 1.30 -0.07',
      normal: '0 1 0'
    },
    'Pergelangan Tangan Kiri': {
      position: '0.68 1.22 -0.07',
      normal: '0 1 0'
    },
    'Jari Tangan Kiri': {
      position: '0.82 1.12 -0.07',
      normal: '0 1 0'
    },

    // Lengan Kanan - Natural Pose (tangan menggantung ke bawah)
    'Bahu Kanan': {
      position: '-0.18 1.48 -0.07',
      normal: '0 1 0'
    },
    'Lengan Atas Kanan': {
      position: '-0.34 1.40 -0.07',
      normal: '0 1 0'
    },
    'Siku Kanan': {
      position: '-0.45 1.35 -0.10',
      normal: '0 1 0'
    },
    'Lengan Bawah Kanan': {
      position: '-0.55 1.30 -0.07',
      normal: '0 1 0'
    },
    'Pergelangan Tangan Kanan': {
      position: '-0.68 1.22 -0.07',
      normal: '0 1 0'
    },
    'Jari Tangan Kanan': {
      position: '-0.82 1.12 -0.07',
      normal: '0 1 0'
    },

    // Kaki Kiri (x: -0.1, y: 0-0.9m)
    'Paha Kiri': {
      position: '-0.12 0.7 0.05',
      normal: '0 0 1'
    },
    'Lutut Kiri': {
      position: '-0.12 0.5 0.08',
      normal: '0 0 1'
    },
    'Betis Kiri': {
      position: '-0.12 0.3 0.06',
      normal: '0 0 1'
    },
    'Pergelangan Kaki Kiri': {
      position: '-0.12 0.1 0.05',
      normal: '0 0 1'
    },
    'Jari Kaki Kiri': {
      position: '-0.12 0.02 0.15',
      normal: '0 0 1'
    },

    // Kaki Kanan (x: 0.1, y: 0-0.9m)
    'Paha Kanan': {
      position: '0.12 0.7 0.05',
      normal: '0 0 1'
    },
    'Lutut Kanan': {
      position: '0.12 0.5 0.08',
      normal: '0 0 1'
    },
    'Betis Kanan': {
      position: '0.12 0.3 0.06',
      normal: '0 0 1'
    },
    'Pergelangan Kaki Kanan': {
      position: '0.12 0.1 0.05',
      normal: '0 0 1'
    },
    'Jari Kaki Kanan': {
      position: '0.12 0.02 0.15',
      normal: '0 0 1'
    },
  };

  document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle sections based on status
    function toggleStatusSections() {
      const status = document.getElementById('status').value;
      const kondisiFisikSection = document.getElementById('kondisiFisikSection');
      const tandaFisikSection = document.getElementById('tandaFisikSection');
      const verifikasiSection = document.getElementById('verifikasiOrangTuaSection');
      const divider1 = document.getElementById('divider1');
      const divider2 = document.getElementById('divider2');
      const tandaFisikDivider = document.getElementById('tandaFisikDivider');
      const keteranganField = document.getElementById('keterangan');
      const keteranganRequired = document.getElementById('keteranganRequired');
      const namaPengantar = document.getElementById('nama_pengantar');
      const signaturePengantar = document.getElementById('signature_pengantar');

      if (status === 'izin' || status === 'alfa') {
        // Hide all sections except keterangan
        kondisiFisikSection.style.display = 'none';
        tandaFisikSection.style.display = 'none';
        verifikasiSection.style.display = 'none';
        divider1.style.display = 'none';
        divider2.style.display = 'none';
        tandaFisikDivider.style.display = 'none';

        // Make keterangan required
        keteranganField.required = true;
        keteranganRequired.style.display = 'inline';

        // Remove required from hidden sections
        if (namaPengantar) namaPengantar.required = false;
        if (signaturePengantar) signaturePengantar.required = false;
      } else {
        // Show all sections for 'hadir'
        kondisiFisikSection.style.display = 'block';
        verifikasiSection.style.display = 'block';
        divider1.style.display = 'block';
        divider2.style.display = 'block';

        // Keterangan optional for hadir
        keteranganField.required = false;
        keteranganRequired.style.display = 'none';

        // Make signature required for hadir
        if (namaPengantar) namaPengantar.required = true;
        if (signaturePengantar) signaturePengantar.required = true;

        // Resize signature canvas after making section visible
        setTimeout(() => {
          if (typeof window.resizeSignatureCanvas === 'function') {
            window.resizeSignatureCanvas();
          }
        }, 100);

        // Check kondisi fisik for tanda fisik section
        const kondisiFisik = document.querySelector('input[name="kondisi_fisik"]:checked');
        if (kondisiFisik && kondisiFisik.value === 'ada_tanda') {
          tandaFisikSection.style.display = 'block';
          tandaFisikDivider.style.display = 'block';
        } else {
          tandaFisikSection.style.display = 'none';
          tandaFisikDivider.style.display = 'none';
        }
      }
    }

    // Event listener for status change
    document.getElementById('status')?.addEventListener('change', toggleStatusSections);

    // Initial state on page load
    toggleStatusSections();

    // Toggle kondisi fisik section
    document.querySelectorAll('.kondisi-fisik-radio').forEach(radio => {
      radio.addEventListener('change', function() {
        const section = document.getElementById('tandaFisikSection');
        const divider = document.getElementById('tandaFisikDivider');
        const status = document.getElementById('status').value;

        // Only show if status is hadir
        if (status === 'hadir') {
          if (this.value === 'ada_tanda') {
            section.style.display = 'block';
            divider.style.display = 'block';
          } else {
            section.style.display = 'none';
            divider.style.display = 'none';
          }
        }
      });
    });

    // Initialize 3D Model
    const modelViewer = document.getElementById('bodyModel3D');
    if (modelViewer) {
      const jenisKelamin = modelViewer.getAttribute('data-jenis-kelamin') || 'laki-laki';
      const modelUrl = jenisKelamin === 'perempuan' ? '/assets/Female.glb' : '/assets/Male.glb';

      modelViewer.src = modelUrl;
      console.log('Loading 3D model:', modelUrl, 'for', jenisKelamin);

      modelViewer.addEventListener('load', () => {
        // Hide loader when model is loaded
        const loader = document.getElementById('model3DLoader');
        if (loader) {
          loader.style.display = 'none';
        }
        updateBodyHotspots();
      });
    }

    // Body part selection
    document.querySelectorAll('.body-part-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const part = this.getAttribute('data-part');
        const index = selectedBodyParts.indexOf(part);

        if (index > -1) {
          selectedBodyParts.splice(index, 1);
          this.classList.remove('btn-danger');
          this.classList.add('btn-outline-secondary');
        } else {
          selectedBodyParts.push(part);
          this.classList.remove('btn-outline-secondary');
          this.classList.add('btn-danger');
        }

        updateSelectedLocations();
        updateBodyHotspots();
      });

      // Set initial state for existing data
      if (selectedBodyParts.includes(btn.getAttribute('data-part'))) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-danger');
      }
    });

    // Initialize signature pad
    const canvas = document.getElementById('signaturePad');
    let resizeCanvasFunc;

    if (canvas) {
      resizeCanvasFunc = function() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (signaturePad) {
          signaturePad.clear();
        }
      };

      resizeCanvasFunc();
      signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
      });
      window.addEventListener('resize', resizeCanvasFunc);

      // Make resize function available globally
      window.resizeSignatureCanvas = resizeCanvasFunc;

      // Update signature status with visual feedback
      signaturePad.addEventListener('endStroke', () => {
        const statusEl = document.getElementById('signatureStatus');
        if (statusEl && !signaturePad.isEmpty()) {
          statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
          statusEl.style.color = 'green';
        }
      });

      // Fallback to canvas events for compatibility
      canvas.addEventListener('mouseup', () => {
        setTimeout(() => {
          const statusEl = document.getElementById('signatureStatus');
          if (statusEl && !signaturePad.isEmpty()) {
            statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
            statusEl.style.color = 'green';
          }
        }, 10);
      });

      canvas.addEventListener('touchend', () => {
        setTimeout(() => {
          const statusEl = document.getElementById('signatureStatus');
          if (statusEl && !signaturePad.isEmpty()) {
            statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
            statusEl.style.color = 'green';
          }
        }, 10);
      });
    }

    document.getElementById('clearSignatureBtn')?.addEventListener('click', function() {
      if (signaturePad) {
        signaturePad.clear();
        const statusEl = document.getElementById('signatureStatus');
        if (statusEl) {
          statusEl.innerHTML = 'Belum ada tanda tangan';
          statusEl.style.color = '#6c757d';
        }
      }
    });

    // Form submission
    document.getElementById('editAbsensiForm')?.addEventListener('submit', function(e) {
      const status = document.getElementById('status').value;

      // Only validate signature for 'hadir' status
      if (status === 'hadir') {
        if (signaturePad && signaturePad.isEmpty()) {
          e.preventDefault();
          alert('Tanda tangan pengantar harus diisi!');
          return false;
        }

        if (signaturePad) {
          const signatureData = signaturePad.toDataURL();
          document.getElementById('signature_pengantar').value = signatureData;
        }
      } else {
        // For izin/alfa, check keterangan is filled
        const keterangan = document.getElementById('keterangan').value.trim();
        if (!keterangan) {
          e.preventDefault();
          alert('Keterangan harus diisi untuk status Izin/Alfa!');
          return false;
        }
      }
    });

    // Initialize selected locations display
    updateSelectedLocations();
  });

  function updateSelectedLocations() {
    const container = document.getElementById('selectedLocations');
    const hiddenInput = document.getElementById('lokasi_luka');

    if (!container) return;

    container.innerHTML = '';

    if (selectedBodyParts.length > 0) {
      const label = document.createElement('p');
      label.className = 'mb-2 fw-semibold';
      label.textContent = 'Lokasi yang dipilih:';
      container.appendChild(label);

      selectedBodyParts.forEach(part => {
        const badge = document.createElement('span');
        badge.className = 'location-badge';
        badge.innerHTML = `${part} <span class="badge-remove" onclick="removeBodyPart('${part}')">×</span>`;
        container.appendChild(badge);
      });
    }

    hiddenInput.value = JSON.stringify(selectedBodyParts);
  }

  function updateBodyHotspots() {
    const modelViewer = document.getElementById('bodyModel3D');
    if (!modelViewer) return;

    // Remove existing hotspots
    const existingHotspots = modelViewer.querySelectorAll('.body-hotspot');
    existingHotspots.forEach(h => h.remove());

    // Add hotspots for selected parts
    selectedBodyParts.forEach(part => {
      const coords = bodyPartCoordinates[part];
      if (coords) {
        const hotspot = document.createElement('button');
        hotspot.slot = `hotspot-${part.replace(/\s+/g, '-')}`;
        hotspot.className = 'body-hotspot';
        hotspot.setAttribute('data-position', coords.position);
        hotspot.setAttribute('data-normal', coords.normal);
        hotspot.title = part;
        modelViewer.appendChild(hotspot);
      }
    });
  }

  window.removeBodyPart = function(part) {
    const index = selectedBodyParts.indexOf(part);
    if (index > -1) {
      selectedBodyParts.splice(index, 1);

      // Update button state
      const btn = document.querySelector(`.body-part-btn[data-part="${part}"]`);
      if (btn) {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-secondary');
      }

      updateSelectedLocations();
      updateBodyHotspots();
    }
  };

  window.removeExistingFotoBukti = function(index, path) {
    if (confirm('Hapus foto bukti ini?')) {
      document.getElementById('existing-foto-bukti-' + index).style.display = 'none';
      document.getElementById('existing-foto-bukti-input-' + index).remove();
    }
  };
  @endif
</script>
@endpush