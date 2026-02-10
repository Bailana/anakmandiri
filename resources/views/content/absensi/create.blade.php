@extends('layouts.contentNavbarLayout')

@section('title', 'Tambah Absensi')

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

  .camera-preview {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    margin: 1rem 0;
  }

  #cameraStream {
    max-width: 100%;
    border-radius: 8px;
  }

  .body-map-container {
    display: flex;
    gap: 2rem;
    margin: 1rem 0;
    flex-wrap: wrap;
  }

  .body-map {
    flex: 1;
    min-width: 200px;
    border: 2px solid #ccc;
    border-radius: 8px;
    padding: 1rem;
    background-color: #f9f9f9;
  }

  .body-map svg {
    width: 100%;
    height: auto;
    max-width: 200px;
  }

  .body-map svg .body-area {
    opacity: 0.3;
    transition: opacity 0.2s;
  }

  .body-map svg .body-area:hover {
    opacity: 0.7;
    fill: #ffc107;
  }

  .body-map svg .body-area.selected {
    opacity: 1;
    fill: #dc3545;
    stroke: #dc3545;
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
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Absensi Anak Didik</h5>
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

        <form action="{{ route('absensi.store') }}" method="POST" enctype="multipart/form-data" id="absensiForm">
          @csrf

          <!-- Section 1: Informasi Dasar -->
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label for="anak_didik_id" class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select class="form-select @error('anak_didik_id') is-invalid @enderror" id="anak_didik_id" name="anak_didik_id" required>
                <option value="">-- Pilih Anak Didik --</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}" @selected(old('anak_didik_id')==$anak->id)>
                  {{ $anak->nama }}
                </option>
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-12">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_izin" name="is_izin" value="1" @checked(old('is_izin'))>
                <label class="form-check-label" for="is_izin">
                  Anak didik izin hari ini
                </label>
              </div>
            </div>

            <div class="col-12" id="keteranganSection" style="display: {{ old('is_izin') ? 'block' : 'none' }};">
              <label for="keterangan" class="form-label">Keterangan <span id="keterangan-required" class="text-danger" style="display: none;">*</span></label>
              <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                rows="2" placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
              @error('keterangan')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <!-- Divider -->
          <hr class="my-4">

          <!-- Section 2: Kondisi Fisik -->
          <div id="kondisiFisikSection" class="mb-4">
            <h6 class="mb-3"><i class="ri-hospital-line me-2"></i>Pemeriksaan Kondisi Fisik</h6>

            <div class="row g-3">
              <div class="col-12">
                <label for="kondisi_fisik" class="form-label">Kondisi Fisik Anak Didik <span class="text-danger">*</span></label>
                <div class="form-check mt-2">
                  <input class="form-check-input kondisi-fisik-radio" type="radio" name="kondisi_fisik" id="kondisi_baik" value="baik"
                    @checked(old('kondisi_fisik', 'baik' )=='baik' )>
                  <label class="form-check-label" for="kondisi_baik">
                    ✓ Kondisi Fisik Baik (Tidak ada tanda luka/lebam)
                  </label>
                </div>
                <div class="form-check mt-2">
                  <input class="form-check-input kondisi-fisik-radio" type="radio" name="kondisi_fisik" id="kondisi_ada_tanda" value="ada_tanda"
                    @checked(old('kondisi_fisik')=='ada_tanda' )>
                  <label class="form-check-label" for="kondisi_ada_tanda">
                    ⚠ Ada Tanda Fisik (Ada lebam/luka yang harus didokumentasikan)
                  </label>
                </div>
                @error('kondisi_fisik')
                <span class="text-danger d-block mt-2">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <!-- Divider -->
          <hr class="my-4" id="tandaFisikDivider" style="display: {{ old('kondisi_fisik') === 'ada_tanda' ? 'block' : 'none' }};">

          <!-- Section 3: Detail Tanda Fisik (Conditional) -->
          <div id="tandaFisikSection" style="display: {{ old('kondisi_fisik') === 'ada_tanda' ? 'block' : 'none' }};">
            <h6 class="mb-3"><i class="ri-alert-line me-2"></i>Detail Tanda Fisik</h6>

            <!-- Jenis Tanda Fisik -->
            <div class="row g-3 mb-4">
              <div class="col-12">
                <label class="form-label">Jenis Tanda Fisik <span class="text-danger">*</span></label>
                <p class="text-muted"><small>Pilih satu atau lebih jenis tanda fisik yang ditemukan</small></p>
                <div class="row @error('jenis_tanda_fisik') is-invalid @enderror">
                  @foreach($jenisTandaFisik as $key => $label)
                  @if($key !== 'baik')
                  <div class="col-md-6 col-lg-4">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="jenis_tanda_fisik[]" id="jenis_tanda_fisik_{{ $key }}" value="{{ $key }}"
                        @checked(is_array(old('jenis_tanda_fisik')) && in_array($key, old('jenis_tanda_fisik')))>
                      <label class="form-check-label" for="jenis_tanda_fisik_{{ $key }}">
                        {{ $label }}
                      </label>
                    </div>
                  </div>
                  @endif
                  @endforeach
                </div>
                @error('jenis_tanda_fisik')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>

              <div class="col-12">
                <label for="keterangan_tanda_fisik" class="form-label">Keterangan <span class="text-danger">*</span></label>
                <textarea class="form-control @error('keterangan_tanda_fisik') is-invalid @enderror" id="keterangan_tanda_fisik" name="keterangan_tanda_fisik"
                  rows="3" placeholder="Deskripsikan kondisi/tanda fisik yang ditemukan...">{{ old('keterangan_tanda_fisik') }}</textarea>
                @error('keterangan_tanda_fisik')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <!-- Body Map 3D -->
            <div class="mb-4">
              <label class="form-label">Lokasi Tanda Fisik di Tubuh <span class="text-danger">*</span></label>
              <p class="text-muted"><small>Putar model 3D untuk melihat bagian tubuh, lalu pilih lokasi tanda fisik dari daftar di bawah</small></p>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <!-- 3D Model Viewer -->
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
                      style="width: 100%; height: 100%;">
                    </model-viewer>
                    <div id="model3DLoader" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); pointer-events: none;">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading 3D model...</span>
                      </div>
                    </div>
                  </div>
                  <div id="model3DError" class="alert alert-warning mt-2" style="display: none;" role="alert">
                    Gagal memuat model 3D. Pastikan URL aset tersedia di server.
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- Body Parts Selection -->
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
              <input type="hidden" id="lokasi_luka" name="lokasi_luka" value="{{ old('lokasi_luka', '[]') }}">
              @error('lokasi_luka')
              <span class="text-danger d-block">{{ $message }}</span>
              @enderror
            </div>

            <!-- Foto Bukti -->
            <div class="mb-4">
              <label for="foto_bukti" class="form-label">Foto Bukti <span class="text-danger">*</span></label>
              <p class="text-muted"><small>Ambil foto bagian yang ada tanda fisiknya. Foto akan disimpan dengan timestamp server sebagai bukti.</small></p>

              <div class="row g-3">
                <div class="col-md-6">
                  <div id="cameraContainer" class="mb-3">
                    <video id="cameraStream" width="100%" height="auto" style="display: none; border: 2px solid #ccc; border-radius: 8px;"></video>
                    <canvas id="captureCanvas" style="display: none;"></canvas>

                    <div id="cameraPlaceholder" class="text-center p-5 border border-dashed rounded">
                      <i class="ri-camera-line" style="font-size: 3rem; color: #ccc;"></i>
                      <p class="text-muted mt-2">Kamera tidak aktif</p>
                    </div>
                  </div>

                  <div class="d-flex gap-2 mb-3">
                    <button type="button" id="startCameraBtn" class="btn btn-primary btn-sm" style="display: none;">
                      <i class="ri-camera-2-line me-2"></i>Buka Kamera
                    </button>
                    <button type="button" id="takeFotoBtn" class="btn btn-success btn-sm" style="display: none;">
                      <i class="ri-camera-line me-2"></i>Ambil Foto
                    </button>
                    <button type="button" id="retakeFotoBtn" class="btn btn-warning btn-sm" style="display: none;">
                      <i class="ri-refresh-line me-2"></i>Ambil Ulang
                    </button>
                    <button type="button" id="addMoreFotoBtn" class="btn btn-info btn-sm" style="display: none;">
                      <i class="ri-add-line me-2"></i>Tambah Foto
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <p class="text-muted"><small>Atau upload file gambar (bisa lebih dari 1):</small></p>
                  <input type="file" class="form-control @error('foto_bukti') is-invalid @enderror" id="foto_bukti" name="foto_bukti[]"
                    accept="image/*" multiple onchange="previewUploadedImage(this)">
                  @error('foto_bukti')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <!-- Preview multiple fotos -->
              <div id="fotoPreviewContainer" class="mt-3"></div>
            </div>

            <!-- Divider -->
            <hr class="my-4" id="verifikasiDivider">
          </div>

          <!-- Section 4: Tanda Tangan Orang Tua / Pengantar (UNTUK SEMUA KONDISI) -->
          <div id="verifikasiOrangTuaSection">
            <h6 class="mb-3"><i class="ri-pen-nib-line me-2"></i>Verifikasi Orang Tua / Pengantar</h6>
            <p class="text-muted"><small>Orang tua/pengantar harus menandatangani bahwa anak sudah diantarkan ke sekolah</small></p>

            <div class="mb-4">
              <label for="nama_pengantar" class="form-label">Nama Orang Tua / Pengantar <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('nama_pengantar') is-invalid @enderror" id="nama_pengantar"
                name="nama_pengantar" value="{{ old('nama_pengantar') }}" placeholder="Nama lengkap...">
              @error('nama_pengantar')
              <span class="invalid-feedback d-block">{{ $message }}</span>
              @enderror
            </div>

            <div class="mb-4">
              <label for="signature_pengantar" class="form-label">Tanda Tangan Orang Tua / Pengantar <span class="text-danger">*</span></label>
              <p class="text-muted"><small>Silakan tanda tangan di kotak berikut dengan jari atau stylus</small></p>
              <div style="border: 3px solid #007bff; border-radius: 8px; overflow: hidden; margin-bottom: 1rem;">
                <canvas id="signaturePad"></canvas>
              </div>
              <input type="hidden" id="signature_pengantar" name="signature_pengantar">
              @error('signature_pengantar')
              <span class="text-danger d-block"><small>{{ $message }}</small></span>
              @enderror
            </div>

            <div class="d-flex gap-2 align-items-center">
              <button type="button" id="clearSignatureBtn" class="btn btn-sm btn-outline-secondary">
                <i class="ri-delete-bin-line me-2"></i>Hapus Tanda Tangan
              </button>
              <span class="text-muted"><small id="signatureStatus">Belum ada tanda tangan</small></span>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Simpan Absensi
            </button>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
              <i class="ri-arrow-left-line me-2"></i>Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.js" defer></script>
<!-- Model Viewer for 3D Body -->
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>

<script>
  // Resolve model asset URLs via Laravel to handle subdirectory deployments
  const MODEL_FEMALE_URL = "{{ asset('assets/Female.glb') }}";
  const MODEL_MALE_URL = "{{ asset('assets/Male.glb') }}";

  // Hybrid drawing system - uses Signature Pad if available, falls back to native canvas
  function initializeSignatureCanvas() {
    const canvas = document.getElementById('signaturePad');
    if (!canvas) {
      console.error('❌ Canvas not found');
      return;
    }

    const container = canvas.parentElement;
    const width = container.clientWidth || 400;
    const height = 250;

    // CRITICAL: Set canvas size
    canvas.width = width;
    canvas.height = height;

    // Set styles for touch support
    canvas.style.touchAction = 'none';
    canvas.style.cursor = 'crosshair';
    canvas.style.display = 'block';

    console.log('Canvas size:', width, 'x', height);

    // Try to use Signature Pad if available
    if (typeof SignaturePad !== 'undefined') {
      console.log('✓ SignaturePad library detected, using library mode');
      try {
        window.signaturePad = new SignaturePad(canvas, {
          penColor: '#000000',
          backgroundColor: '#FFFFFF'
        });
        console.log('✓ SignaturePad initialized');
        setupSignaturePadHandlers();
        return;
      } catch (err) {
        console.warn('⚠ SignaturePad initialization failed:', err);
      }
    }

    // Fallback: Native canvas drawing
    console.log('📌 Using native canvas drawing mode');

    const ctx = canvas.getContext('2d');
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = 2;
    ctx.strokeStyle = '#000000';
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, width, height);

    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    function getCoords(e) {
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;

      let x, y;
      if (e.touches) {
        x = (e.touches[0].clientX - rect.left) * scaleX;
        y = (e.touches[0].clientY - rect.top) * scaleY;
      } else {
        x = (e.clientX - rect.left) * scaleX;
        y = (e.clientY - rect.top) * scaleY;
      }
      return {
        x,
        y
      };
    }

    function drawLine(fromX, fromY, toX, toY) {
      ctx.beginPath();
      ctx.moveTo(fromX, fromY);
      ctx.lineTo(toX, toY);
      ctx.stroke();
    }

    function updateStatus() {
      const statusEl = document.getElementById('signatureStatus');
      if (statusEl && !isCanvasEmpty()) {
        statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
        statusEl.style.color = 'green';
      }
    }

    function isCanvasEmpty() {
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const data = imageData.data;
      for (let i = 3; i < data.length; i += 4) {
        if (data[i] !== 0) return false;
      }
      return true;
    }

    // Mouse events
    canvas.addEventListener('mousedown', (e) => {
      isDrawing = true;
      const coords = getCoords(e);
      lastX = coords.x;
      lastY = coords.y;

      ctx.beginPath();
      ctx.arc(lastX, lastY, 1.5, 0, Math.PI * 2);
      ctx.fill();
    });

    canvas.addEventListener('mousemove', (e) => {
      if (!isDrawing) return;
      const coords = getCoords(e);
      drawLine(lastX, lastY, coords.x, coords.y);
      lastX = coords.x;
      lastY = coords.y;
      updateStatus();
    });

    canvas.addEventListener('mouseup', () => {
      isDrawing = false;
      updateStatus();
    });

    canvas.addEventListener('mouseleave', () => {
      isDrawing = false;
    });

    // Touch events
    canvas.addEventListener('touchstart', (e) => {
      e.preventDefault();
      isDrawing = true;
      const coords = getCoords(e);
      lastX = coords.x;
      lastY = coords.y;

      ctx.beginPath();
      ctx.arc(lastX, lastY, 1.5, 0, Math.PI * 2);
      ctx.fill();
    }, {
      passive: false
    });

    canvas.addEventListener('touchmove', (e) => {
      e.preventDefault();
      if (!isDrawing) return;
      const coords = getCoords(e);
      drawLine(lastX, lastY, coords.x, coords.y);
      lastX = coords.x;
      lastY = coords.y;
      updateStatus();
    }, {
      passive: false
    });

    canvas.addEventListener('touchend', (e) => {
      e.preventDefault();
      isDrawing = false;
      updateStatus();
    }, {
      passive: false
    });

    window.isCanvasEmpty = isCanvasEmpty;
    window.getCanvasSignature = () => canvas.toDataURL('image/png');

    // Clear button
    const clearBtn = document.getElementById('clearSignatureBtn');
    if (clearBtn) {
      clearBtn.addEventListener('click', (e) => {
        e.preventDefault();
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, width, height);
        const statusEl = document.getElementById('signatureStatus');
        if (statusEl) {
          statusEl.innerHTML = 'Belum ada tanda tangan';
          statusEl.style.color = '#6c757d';
        }
      });
    }

    console.log('✓ Canvas ready for drawing (native mode)');
  }

  function setupSignaturePadHandlers() {
    const statusEl = document.getElementById('signatureStatus');

    window.isCanvasEmpty = () => window.signaturePad.isEmpty();
    window.getCanvasSignature = () => window.signaturePad.toDataURL('image/png');

    const clearBtn = document.getElementById('clearSignatureBtn');
    if (clearBtn) {
      clearBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.signaturePad.clear();
        if (statusEl) {
          statusEl.innerHTML = 'Belum ada tanda tangan';
          statusEl.style.color = '#6c757d';
        }
      });
    }

    // Function to update status
    function updateSignatureStatus() {
      if (statusEl && !window.signaturePad.isEmpty()) {
        statusEl.innerHTML = '✓ Tanda tangan sudah dibuat';
        statusEl.style.color = 'green';
      }
    }

    // Monitor for signatures using SignaturePad events
    window.signaturePad.addEventListener('endStroke', updateSignatureStatus);

    // Fallback to canvas events for compatibility
    const canvas = document.getElementById('signaturePad');
    canvas.addEventListener('mouseup', () => {
      setTimeout(updateSignatureStatus, 10);
    });

    canvas.addEventListener('touchend', () => {
      setTimeout(updateSignatureStatus, 10);
    });

    console.log('✓ SignaturePad handlers setup complete');
  }

  // Initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      setTimeout(initializeSignatureCanvas, 100);
    });
  } else {
    setTimeout(initializeSignatureCanvas, 100);
  }

  // All other form functionality
  document.addEventListener('DOMContentLoaded', function() {
    // Handle izin checkbox - toggle keterangan required
    const izinCheckbox = document.getElementById('is_izin');
    const keteranganRequiredSpan = document.getElementById('keterangan-required');
    const keteranganField = document.getElementById('keterangan');
    const keteranganSection = document.getElementById('keteranganSection');
    const kondisiFisikSection = document.getElementById('kondisiFisikSection');
    const verifikasiOrangTuaSection = document.getElementById('verifikasiOrangTuaSection');
    const tandaFisikSection = document.getElementById('tandaFisikSection');
    const tandaFisikDivider = document.getElementById('tandaFisikDivider');
    const verifikasiDivider = document.getElementById('verifikasiDivider');

    function updateIzinVisibility() {
      if (izinCheckbox.checked) {
        // Jika izin dicentang, tampilkan keterangan field dan buat wajib
        keteranganSection.style.display = 'block';
        keteranganRequiredSpan.style.display = 'inline';
        keteranganField.required = true;

        // Sembunyikan kondisi fisik, detail tanda fisik, dan verifikasi orang tua sections
        kondisiFisikSection.style.display = 'none';
        tandaFisikSection.style.display = 'none';
        verifikasiOrangTuaSection.style.display = 'none';
        tandaFisikDivider.style.display = 'none';
        if (verifikasiDivider) verifikasiDivider.style.display = 'none';

        // Hapus required attribute dari field yang disembunyikan
        document.getElementById('kondisi_baik').required = false;
        document.getElementById('kondisi_ada_tanda').required = false;
        document.getElementById('keterangan_tanda_fisik').required = false;
      } else {
        // Jika tidak izin, sembunyikan keterangan field
        keteranganSection.style.display = 'none';
        keteranganRequiredSpan.style.display = 'none';
        keteranganField.required = false;

        // Tampilkan kondisi fisik dan verifikasi orang tua sections
        kondisiFisikSection.style.display = 'block';
        verifikasiOrangTuaSection.style.display = 'block';
        tandaFisikDivider.style.display = 'block';
        if (verifikasiDivider) verifikasiDivider.style.display = 'block';

        // Set kondisi_fisik ke wajib
        document.getElementById('kondisi_baik').required = true;
        document.getElementById('kondisi_ada_tanda').required = true;

        // Tampilkan detail tanda fisik dan set required hanya jika ada_tanda dipilih
        const adaTandaRadio = document.getElementById('kondisi_ada_tanda');
        if (adaTandaRadio && adaTandaRadio.checked) {
          tandaFisikSection.style.display = 'block';
          document.getElementById('keterangan_tanda_fisik').required = true;
        } else {
          document.getElementById('keterangan_tanda_fisik').required = false;
        }
      }
    }

    izinCheckbox.addEventListener('change', updateIzinVisibility);

    // Initial check
    updateIzinVisibility();

    // Conditional show/hide tanda fisik section
    const kondisiFisikRadios = document.querySelectorAll('input[name="kondisi_fisik"]');

    // Function to toggle tanda fisik section
    function updateTandaFisikVisibility() {
      const adaTandaRadio = document.getElementById('kondisi_ada_tanda');
      if (adaTandaRadio && adaTandaRadio.checked && !izinCheckbox.checked) {
        tandaFisikSection.style.display = 'block';
        tandaFisikDivider.style.display = 'block';
        document.getElementById('keterangan_tanda_fisik').required = true;
      } else {
        tandaFisikSection.style.display = 'none';
        tandaFisikDivider.style.display = 'none';
        document.getElementById('keterangan_tanda_fisik').required = false;
      }
    }

    // Add change event listeners
    kondisiFisikRadios.forEach(radio => {
      radio.addEventListener('change', updateTandaFisikVisibility);
    });

    // 3D Body Model & Body Part Selection
    const anakDidikSelect = document.getElementById('anak_didik_id');
    const bodyModel3D = document.getElementById('bodyModel3D');
    let selectedLocations = JSON.parse(document.getElementById('lokasi_luka').value || '[]');
    let modelLoaded = false;
    let modelReady = false;

    // Anak didik data from backend
    const anakDidikData = @json($anakDidiks);

    // 3D coordinates for body parts (x, y, z in meters)
    // Koordinat disesuaikan dengan model manusia standar tinggi ~1.7m
    // Model dalam posisi natural pose (tangan menggantung ke bawah)
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
      }
    };

    // Function to update hotspots on 3D model
    function updateModelHotspots() {
      // Remove all existing hotspots
      const existingHotspots = bodyModel3D.querySelectorAll('[slot^="hotspot-"]');
      existingHotspots.forEach(hotspot => hotspot.remove());

      // Add hotspots for selected locations
      selectedLocations.forEach((location, index) => {
        const coords = bodyPartCoordinates[location];
        if (coords) {
          const hotspot = document.createElement('div');
          hotspot.className = 'body-hotspot';
          hotspot.slot = `hotspot-${index}`;
          hotspot.setAttribute('data-position', coords.position);
          hotspot.setAttribute('data-normal', coords.normal);
          hotspot.title = `${location} (Klik untuk hapus)`;

          // Make hotspot clickable to remove
          hotspot.addEventListener('click', function(e) {
            e.stopPropagation();
            window.removeLocation(location);
          });

          bodyModel3D.appendChild(hotspot);
        }
      });
    }

    // Function to update 3D model based on selected anak didik
    function update3DModel() {
      const selectedAnakId = anakDidikSelect.value;
      const loader = document.getElementById('model3DLoader');

      // Reset model state
      modelLoaded = false;
      modelReady = false;

      if (!selectedAnakId) {
        bodyModel3D.src = '';
        loader.style.display = 'none';
        return;
      }

      const selectedAnak = anakDidikData.find(anak => anak.id == selectedAnakId);
      if (!selectedAnak) {
        bodyModel3D.src = '';
        loader.style.display = 'none';
        return;
      }

      // Show loader before loading new model
      loader.style.display = 'flex';

      // Set model based on jenis_kelamin
      const jenisKelamin = (selectedAnak.jenis_kelamin || '').toLowerCase();
      if (jenisKelamin === 'perempuan' || jenisKelamin === 'p') {
        bodyModel3D.src = MODEL_FEMALE_URL;
        console.log('Loading female 3D model...');
      } else if (jenisKelamin === 'laki-laki' || jenisKelamin === 'l') {
        bodyModel3D.src = MODEL_MALE_URL;
        console.log('Loading male 3D model...');
      } else {
        // Default to male if gender not specified
        bodyModel3D.src = MODEL_MALE_URL;
        console.log('Loading default (male) 3D model...');
      }
    }

    // Hide loader when model is fully loaded
    bodyModel3D.addEventListener('load', function() {
      const loader = document.getElementById('model3DLoader');
      loader.style.display = 'none';
      modelLoaded = true;

      console.log('3D model loaded successfully');

      // Update hotspots after model is loaded
      updateModelHotspots();

      // Update status if edit mode is active
      const statusElement = document.getElementById('editModeStatus');
      if (statusElement) {
        statusElement.textContent = 'Menunggu API siap...';
      }

      // Wait for model to be fully interactive (API ready)
      // Some browsers need a delay for the API to become available
      setTimeout(() => {
        modelReady = true;
        console.log('✓ Model ready for interaction (Edit Mode can now be enabled)');

        // Check if API is actually available
        if (typeof bodyModel3D.positionAndNormalFromPoint === 'function') {
          console.log('✓ positionAndNormalFromPoint API is available');

          // Update status
          if (statusElement) {
            statusElement.innerHTML = '<span class="text-success">✓ Siap! Klik pada model untuk menangkap koordinat</span>';
          }
        } else {
          console.warn('⚠ positionAndNormalFromPoint API not available - Edit Mode may not work');

          // Update status with warning
          if (statusElement) {
            statusElement.innerHTML = '<span class="text-warning">⚠ API tidak tersedia</span>';
          }
        }
      }, 1500); // 1.5 second delay to ensure full initialization
    });

    // Handle loading errors
    bodyModel3D.addEventListener('error', function(event) {
      const loader = document.getElementById('model3DLoader');
      loader.innerHTML = '<div class="text-danger"><i class="ri-error-warning-line"></i><br><small>Gagal memuat model 3D</small></div>';
      console.error('Failed to load 3D model:', event);
    });

    // Update model when anak didik selection changes
    anakDidikSelect.addEventListener('change', update3DModel);

    // Initialize model on page load if anak didik already selected
    if (anakDidikSelect.value) {
      update3DModel();
    }

    // Handle body part button clicks
    const bodyPartButtons = document.querySelectorAll('.body-part-btn');

    function updateLocationDisplay() {
      const container = document.getElementById('selectedLocations');
      container.innerHTML = '';

      if (selectedLocations.length === 0) {
        container.innerHTML = '<p class="text-muted"><small>Belum ada lokasi yang dipilih</small></p>';
      } else {
        selectedLocations.forEach(loc => {
          const badge = document.createElement('span');
          badge.className = 'location-badge';
          badge.innerHTML = `${loc} <span class="badge-remove" onclick="removeLocation('${loc}')">×</span>`;
          container.appendChild(badge);
        });
      }

      document.getElementById('lokasi_luka').value = JSON.stringify(selectedLocations);

      // Update 3D model hotspots
      updateModelHotspots();
    }

    bodyPartButtons.forEach(btn => {
      // Restore selected state on load
      const partName = btn.getAttribute('data-part');
      if (selectedLocations.includes(partName)) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-danger');
      }

      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const partName = this.getAttribute('data-part');

        if (selectedLocations.includes(partName)) {
          // Remove from selection
          selectedLocations = selectedLocations.filter(loc => loc !== partName);
          this.classList.remove('btn-danger');
          this.classList.add('btn-outline-secondary');
        } else {
          // Add to selection
          selectedLocations.push(partName);
          this.classList.remove('btn-outline-secondary');
          this.classList.add('btn-danger');
        }

        updateLocationDisplay();
      });
    });

    window.removeLocation = function(location) {
      selectedLocations = selectedLocations.filter(loc => loc !== location);

      // Remove visual indicator from button
      bodyPartButtons.forEach(btn => {
        const partName = btn.getAttribute('data-part');
        if (partName === location) {
          btn.classList.remove('btn-danger');
          btn.classList.add('btn-outline-secondary');
        }
      });

      updateLocationDisplay();
    };

    // Initialize display
    updateLocationDisplay();

    // Camera functionality
    const startCameraBtn = document.getElementById('startCameraBtn');
    const takeFotoBtn = document.getElementById('takeFotoBtn');
    const retakeFotoBtn = document.getElementById('retakeFotoBtn');
    const addMoreFotoBtn = document.getElementById('addMoreFotoBtn');
    const cameraStream = document.getElementById('cameraStream');
    const captureCanvas = document.getElementById('captureCanvas');
    const fotoInput = document.getElementById('foto_bukti');
    let stream = null;
    let capturedPhotos = []; // Array untuk menyimpan foto yang ditangkap

    // Show camera button when ada_tanda is selected
    document.getElementById('kondisi_ada_tanda').addEventListener('change', function() {
      if (this.checked) {
        startCameraBtn.style.display = 'inline-block';
      }
    });

    // Show camera button on load if ada_tanda is already checked
    if (document.getElementById('kondisi_ada_tanda').checked) {
      startCameraBtn.style.display = 'inline-block';
    }

    startCameraBtn.addEventListener('click', async function() {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'environment'
          },
          audio: false
        });

        cameraStream.srcObject = stream;

        // Wait for video metadata to load, then play
        cameraStream.onloadedmetadata = function() {
          cameraStream.play();
          document.getElementById('cameraPlaceholder').style.display = 'none';
          cameraStream.style.display = 'block';
          startCameraBtn.style.display = 'none';
          takeFotoBtn.style.display = 'inline-block';
        };
      } catch (err) {
        alert('Tidak bisa mengakses kamera: ' + err.message);
      }
    });

    takeFotoBtn.addEventListener('click', function() {
      // Ensure video is ready
      if (cameraStream.videoWidth === 0 || cameraStream.videoHeight === 0) {
        alert('Kamera belum siap. Silakan tunggu sebentar dan coba lagi.');
        return;
      }

      captureCanvas.width = cameraStream.videoWidth;
      captureCanvas.height = cameraStream.videoHeight;

      const ctx = captureCanvas.getContext('2d');
      ctx.drawImage(cameraStream, 0, 0);

      // Convert canvas to blob
      captureCanvas.toBlob(function(blob) {
        const file = new File([blob], 'foto_' + Date.now() + '.png', {
          type: 'image/png'
        });

        // Tambah file ke array capturedPhotos
        capturedPhotos.push(file);

        // Update file input dengan semua foto
        const dataTransfer = new DataTransfer();
        capturedPhotos.forEach(f => dataTransfer.items.add(f));
        fotoInput.files = dataTransfer.files;

        // Update preview
        updateFotoPreview();

        // Show buttons
        cameraStream.style.display = 'none';
        takeFotoBtn.style.display = 'none';
        addMoreFotoBtn.style.display = 'inline-block';
        retakeFotoBtn.style.display = 'inline-block';

        // Stop camera
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      });
    });

    function updateFotoPreview() {
      const container = document.getElementById('fotoPreviewContainer');
      container.innerHTML = '';

      if (capturedPhotos.length === 0) {
        container.innerHTML = '';
        return;
      }

      const row = document.createElement('div');
      row.className = 'row g-2 mt-2';

      capturedPhotos.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const col = document.createElement('div');
          col.className = 'col-md-3';
          col.innerHTML = `
            <div class="position-relative">
              <img src="${e.target.result}" class="img-thumbnail w-100" style="height: 200px; object-fit: cover;">
              <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeCapturedPhoto(${index})">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          `;
          row.appendChild(col);
        };
        reader.readAsDataURL(file);
      });

      container.appendChild(row);
    }

    window.removeCapturedPhoto = function(index) {
      capturedPhotos.splice(index, 1);
      const dataTransfer = new DataTransfer();
      capturedPhotos.forEach(f => dataTransfer.items.add(f));
      fotoInput.files = dataTransfer.files;
      updateFotoPreview();

      if (capturedPhotos.length === 0) {
        addMoreFotoBtn.style.display = 'none';
        retakeFotoBtn.style.display = 'none';
        document.getElementById('cameraPlaceholder').style.display = 'block';
        startCameraBtn.style.display = 'inline-block';
      }
    };

    addMoreFotoBtn.addEventListener('click', async function() {
      // Reset camera and start again
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'environment'
          },
          audio: false
        });

        cameraStream.srcObject = stream;

        cameraStream.onloadedmetadata = function() {
          cameraStream.play();
          cameraStream.style.display = 'block';
          takeFotoBtn.style.display = 'inline-block';
          addMoreFotoBtn.style.display = 'none';
          retakeFotoBtn.style.display = 'none';
        };
      } catch (err) {
        alert('Tidak bisa mengakses kamera: ' + err.message);
      }
    });

    retakeFotoBtn.addEventListener('click', async function() {
      // Clear the captured photos
      capturedPhotos = [];
      fotoInput.value = '';
      updateFotoPreview();

      // Reset camera
      document.getElementById('cameraPlaceholder').style.display = 'block';
      retakeFotoBtn.style.display = 'none';
      addMoreFotoBtn.style.display = 'none';
      startCameraBtn.style.display = 'inline-block';
    });

    // Handle form submit
    document.getElementById('absensiForm').addEventListener('submit', function(e) {
      // Only validate signature dan nama_pengantar jika TIDAK izin
      if (!izinCheckbox.checked) {
        // Validasi nama_pengantar
        if (!document.getElementById('nama_pengantar').value) {
          e.preventDefault();
          alert('Silakan masukkan nama orang tua/pengantar');
          return;
        }

        // Check if signature pad is empty
        if (window.isCanvasEmpty()) {
          e.preventDefault();
          alert('Silakan tanda tangan di kotak yang disediakan untuk konfirmasi');
          return;
        }

        // Save signature to hidden field
        document.getElementById('signature_pengantar').value = window.getCanvasSignature();

        // Conditional validation for ada_tanda
        if (document.getElementById('kondisi_ada_tanda').checked) {
          // Validate required fields for ada_tanda only
          const jenisTandaFisikChecked = document.querySelectorAll('input[name="jenis_tanda_fisik[]"]:checked');
          if (jenisTandaFisikChecked.length === 0) {
            e.preventDefault();
            alert('Silakan pilih minimal satu jenis tanda fisik');
            return;
          }

          if (selectedLocations.length === 0) {
            e.preventDefault();
            alert('Silakan pilih lokasi tanda fisik di tubuh');
            return;
          }

          if (!fotoInput.files.length) {
            e.preventDefault();
            alert('Silakan ambil foto bukti');
            return;
          }
        }
      }
    });

    // Preview uploaded image (handle multiple)
    window.previewUploadedImage = function(input) {
      if (input.files && input.files.length > 0) {
        const container = document.getElementById('fotoPreviewContainer');
        container.innerHTML = '';

        // Reset capturedPhotos and add uploaded files
        capturedPhotos = [];
        for (let i = 0; i < input.files.length; i++) {
          capturedPhotos.push(input.files[i]);
        }

        // Display all previews
        const row = document.createElement('div');
        row.className = 'row g-2 mt-2';

        for (let i = 0; i < input.files.length; i++) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-3';
            col.innerHTML = `
              <div class="position-relative">
                <img src="${e.target.result}" class="img-thumbnail w-100" style="height: 200px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeUploadedPhoto(${i})">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            `;
            row.appendChild(col);
          };
          reader.readAsDataURL(input.files[i]);
        }

        container.appendChild(row);

        // Hide camera controls
        document.getElementById('cameraPlaceholder').style.display = 'none';
        startCameraBtn.style.display = 'none';
        takeFotoBtn.style.display = 'none';
        addMoreFotoBtn.style.display = 'inline-block';
        retakeFotoBtn.style.display = 'inline-block';

        // Stop camera if running
        if (stream) {
          stream.getTracks().forEach(track => track.stop());
          stream = null;
        }

        // Hide video stream if visible
        cameraStream.style.display = 'none';
      }
    };

    window.removeUploadedPhoto = function(index) {
      capturedPhotos.splice(index, 1);
      const dataTransfer = new DataTransfer();
      capturedPhotos.forEach(f => dataTransfer.items.add(f));
      fotoInput.files = dataTransfer.files;
      updateFotoPreview();

      if (capturedPhotos.length === 0) {
        addMoreFotoBtn.style.display = 'none';
        retakeFotoBtn.style.display = 'none';
        document.getElementById('cameraPlaceholder').style.display = 'block';
        startCameraBtn.style.display = 'inline-block';
      }
    };

    // Initialize display
    updateLocationDisplay();
  }); // Close DOMContentLoaded
</script>
@endpush