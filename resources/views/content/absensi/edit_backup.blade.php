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
    0%, 100% {
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

          <div class="row g-3">
            <!-- Info Absensi (Read-only) -->
            <div class="col-12">
              <h6 class="mb-3"><i class="ri-information-line me-2"></i>Informasi Absensi</h6>
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
            <div class="col-12"><hr></div>

            <!-- Data Penjemputan -->
            <div class="col-12">
              <h6 class="mb-3"><i class="ri-user-follow-line me-2"></i>Data Penjemputan</h6>
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
              <div class="row g-2" id="existingPhotos">
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

            <!-- Tanda Tangan -->
            <div class="col-12">
              <label class="form-label">Tanda Tangan Penjemput <span class="text-danger">*</span></label>
              @if($absensi->signature_penjemput)
              <div class="mb-2">
                <p class="text-muted mb-2">Tanda tangan saat ini:</p>
                <img src="{{ asset('storage/' . $absensi->signature_penjemput) }}" 
                     alt="Tanda Tangan Lama" 
                     style="max-width: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="keep_signature" name="keep_signature" value="1" checked>
                  <label class="form-check-label" for="keep_signature">
                    Gunakan tanda tangan yang ada (hapus centang untuk membuat baru)
                  </label>
                </div>
              </div>
              @endif
              <div id="signatureSection" style="display: {{ $absensi->signature_penjemput ? 'none' : 'block' }};">
                <canvas id="editSignaturePad" class="signature-pad"></canvas>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="editClearSignature">
                  <i class="ri-delete-bin-line me-1"></i>Hapus Tanda Tangan
                </button>
              </div>
              <input type="hidden" id="edit_signature_data" name="signature_penjemput">
            </div>

            <!-- Keterangan -->
            <div class="col-12">
              <label for="keterangan_penjemput" class="form-label">Keterangan</label>
              <textarea class="form-control @error('keterangan_penjemput') is-invalid @enderror" 
                        id="keterangan_penjemput" name="keterangan_penjemput" rows="3" 
                        placeholder="Catatan tambahan tentang penjemputan...">{{ old('keterangan_penjemput', $absensi->keterangan_penjemput) }}</textarea>
              @error('keterangan_penjemput')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Simpan Perubahan
            </button>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
              <i class="ri-arrow-left-line me-2"></i>Batal
            </a>
          </div>
        </form>

        @else
        <!-- Form Edit Data Absensi Biasa -->
        <form action="{{ route('absensi.update', $absensi->id) }}" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="edit_type" value="absensi">

          <div class="row g-3">
            <div class="col-md-6">
              <label for="anak_didik_id" class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select class="form-select @error('anak_didik_id') is-invalid @enderror" id="anak_didik_id" name="anak_didik_id" required>
                <option value="">-- Pilih Anak Didik --</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}" @selected(old('anak_didik_id', $absensi->anak_didik_id) == $anak->id)>
                  {{ $anak->nama }}
                </option>
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
              <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal"
                value="{{ old('tanggal', $absensi->tanggal->format('Y-m-d')) }}" required>
              @error('tanggal')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-12">
              <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
              <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="hadir" @selected(old('status', $absensi->status) == 'hadir')>Hadir</option>
                <option value="izin" @selected(old('status', $absensi->status) == 'izin')>Izin</option>
                <option value="alfa" @selected(old('status', $absensi->status) == 'alfa')>Alfa</option>
              </select>
              @error('status')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-12">
              <label for="keterangan" class="form-label">Keterangan</label>
              <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                rows="3" placeholder="Catatan atau alasan absensi...">{{ old('keterangan', $absensi->keterangan) }}</textarea>
              @error('keterangan')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>

            @if($absensi->status !== 'izin')
            <!-- Divider -->
            <div class="col-12"><hr class="my-4"></div>

            <!-- Kondisi Fisik -->
            <div class="col-12">
              <h6 class="mb-3"><i class="ri-hospital-line me-2"></i>Kondisi Fisik</h6>
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="kondisi_fisik" id="edit_kondisi_baik" value="baik"
                  @checked(old('kondisi_fisik', $absensi->kondisi_fisik) == 'baik')>
                <label class="form-check-label" for="edit_kondisi_baik">
                  ✓ Kondisi Fisik Baik
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="kondisi_fisik" id="edit_kondisi_ada_tanda" value="ada_tanda"
                  @checked(old('kondisi_fisik', $absensi->kondisi_fisik) == 'ada_tanda')>
                <label class="form-check-label" for="edit_kondisi_ada_tanda">
                  ⚠ Ada Tanda Fisik
                </label>
              </div>
            </div>

            <!-- Detail Tanda Fisik (Conditional) -->
            <div id="editTandaFisikSection" class="col-12" style="display: {{ old('kondisi_fisik', $absensi->kondisi_fisik) == 'ada_tanda' ? 'block' : 'none' }};">
              <div class="col-12 mt-3">
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
                      <input class="form-check-input" type="checkbox" name="jenis_tanda_fisik[]" id="edit_jenis_{{ $key }}" value="{{ $key }}"
                        @checked(is_array(old('jenis_tanda_fisik')) ? in_array($key, old('jenis_tanda_fisik')) : in_array($key, $existingJenis))>
                      <label class="form-check-label" for="edit_jenis_{{ $key }}">
                        {{ $label }}
                      </label>
                    </div>
                  </div>
                  @endif
                  @endforeach
                </div>
              </div>

              <div class="col-12 mt-3">
                <label for="keterangan_tanda_fisik" class="form-label">Keterangan Tanda Fisik</label>
                <textarea class="form-control" id="keterangan_tanda_fisik" name="keterangan_tanda_fisik" rows="3">{{ old('keterangan_tanda_fisik', $absensi->keterangan_tanda_fisik) }}</textarea>
              </div>

              <!-- Lokasi Luka 3D Model -->
              <div class="col-12 mt-3">
                <label class="form-label">Lokasi Tanda Fisik di Tubuh</label>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <div style="width: 100%; height: 400px; border: 2px solid #ccc; border-radius: 8px; background: linear-gradient(to bottom, #f0f4ff 0%, #e8eefc 100%); position: relative;">
                      <model-viewer id="editBodyModel3D"
                        src=""
                        alt="3D Body Model"
                        camera-controls
                        shadow-intensity="1"
                        exposure="1.0"
                        camera-orbit="0deg 75deg 105%"
                        data-jenis-kelamin="{{ $absensi->anakDidik->jenis_kelamin ?? 'laki-laki' }}"
                        style="width: 100%; height: 100%;">
                      </model-viewer>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; background-color: #f9f9f9;">
                      <strong class="d-block mb-2">Pilih Lokasi:</strong>
                      <div class="mb-2">
                        <strong class="text-primary">Kepala & Wajah</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Kepala">Kepala</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Wajah">Wajah</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Leher">Leher</button>
                        </div>
                      </div>
                      <div class="mb-2">
                        <strong class="text-primary">Badan</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Dada">Dada</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Perut">Perut</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Punggung Atas">Punggung Atas</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Punggung Bawah">Punggung Bawah</button>
                        </div>
                      </div>
                      <div class="mb-2">
                        <strong class="text-primary">Lengan</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Lengan Kiri">Lengan Kiri</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Lengan Kanan">Lengan Kanan</button>
                        </div>
                      </div>
                      <div class="mb-2">
                        <strong class="text-primary">Kaki</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Paha Kiri">Paha Kiri</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Paha Kanan">Paha Kanan</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Betis Kiri">Betis Kiri</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary edit-body-part-btn" data-part="Betis Kanan">Betis Kanan</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="editSelectedLocations" class="mt-2"></div>
                <input type="hidden" id="edit_lokasi_luka" name="lokasi_luka" value="{{ old('lokasi_luka', json_encode($absensi->lokasi_luka ?? [])) }}">
              </div>

              <!-- Foto Bukti Existing -->
              @if($absensi->foto_bukti && count((array)$absensi->foto_bukti) > 0)
              <div class="col-12 mt-3">
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
              <div class="col-12 mt-3">
                <label class="form-label">Tambah Foto Bukti Baru</label>
                <input type="file" class="form-control" name="foto_bukti[]" accept="image/*" multiple>
              </div>
            </div>

            <!-- Divider -->
            <div class="col-12"><hr class="my-4"></div>

            <!-- Data Pengantar -->
            <div class="col-12">
              <h6 class="mb-3"><i class="ri-pen-nib-line me-2"></i>Data Pengantar</h6>
            </div>

            <div class="col-12">
              <label for="nama_pengantar" class="form-label">Nama Orang Tua / Pengantar</label>
              <input type="text" class="form-control" id="nama_pengantar" name="nama_pengantar" 
                     value="{{ old('nama_pengantar', $absensi->nama_pengantar) }}">
            </div>

            <!-- Tanda Tangan Pengantar -->
            <div class="col-12 mt-3">
              <label class="form-label">Tanda Tangan Pengantar</label>
              @if($absensi->signature_pengantar)
              <div class="mb-2">
                <p class="text-muted mb-2">Tanda tangan saat ini:</p>
                <img src="{{ asset('storage/' . $absensi->signature_pengantar) }}" 
                     alt="Tanda Tangan Pengantar" 
                     style="max-width: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="keep_signature_pengantar" name="keep_signature_pengantar" value="1" checked>
                  <label class="form-check-label" for="keep_signature_pengantar">
                    Gunakan tanda tangan yang ada (hapus centang untuk membuat baru)
                  </label>
                </div>
              </div>
              @endif
              <div id="signaturePengantarSection" style="display: {{ $absensi->signature_pengantar ? 'none' : 'block' }};">
                <canvas id="editSignaturePengantar" class="signature-pad"></canvas>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="editClearSignaturePengantar">
                  <i class="ri-delete-bin-line me-1"></i>Hapus Tanda Tangan
                </button>
              </div>
              <input type="hidden" id="edit_signature_pengantar_data" name="signature_pengantar">
            </div>
            @endif
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line me-2"></i>Simpan Perubahan
            </button>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
              <i class="ri-arrow-left-line me-2"></i>Batal
            </a>
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
    }

    // Toggle signature section based on checkbox
    const keepSignatureCheckbox = document.getElementById('keep_signature');
    const signatureSection = document.getElementById('signatureSection');
    
    if (keepSignatureCheckbox) {
      keepSignatureCheckbox.addEventListener('change', function() {
        if (this.checked) {
          signatureSection.style.display = 'none';
          if (editSignaturePad) editSignaturePad.clear();
        } else {
          signatureSection.style.display = 'block';
        }
      });
    }

    // Camera functionality
    document.getElementById('editCameraBtn')?.addEventListener('click', async function() {
      const container = document.getElementById('editCameraContainer');
      const video = document.getElementById('editCameraStream');
      
      try {
        editCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
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
        const file = new File([blob], `edit_jemput_${Date.now()}.jpg`, { type: 'image/jpeg' });
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
      
      // Add to captured photos array for unified handling
      files.forEach(file => {
        editCapturedPhotos.push(file);
      });
      
      updateEditPhotoPreview();
      
      // Clear the file input so it doesn't interfere
      this.value = '';
    });

    document.getElementById('editClearSignature')?.addEventListener('click', function() {
      if (editSignaturePad) {
        editSignaturePad.clear();
      }
    });

    // Form submission
    document.getElementById('editJemputForm')?.addEventListener('submit', function(e) {
      const keepSignature = document.getElementById('keep_signature')?.checked;
      
      if (!keepSignature && editSignaturePad && editSignaturePad.isEmpty()) {
        e.preventDefault();
        alert('Tanda tangan penjemput harus diisi!');
        return false;
      }
      
      if (!keepSignature && editSignaturePad) {
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
  let editSignaturePengantar;
  let editSelectedBodyParts = @json($absensi->lokasi_luka ?? []);

  // 3D Model coordinates
  const bodyPartCoordinates = {
    'Kepala': { position: '0.00 1.80 0.00', normal: '0 1 0' },
    'Wajah': { position: '0.00 1.70 0.10', normal: '0 1 0' },
    'Leher': { position: '0.00 1.56 0.00', normal: '0 1 0' },
    'Dada': { position: '0.00 1.40 0.10', normal: '0 1 0' },
    'Perut': { position: '0.00 1.15 0.12', normal: '0 1 0' },
    'Punggung Atas': { position: '0.00 1.40 -0.16', normal: '0 1 0' },
    'Punggung Bawah': { position: '0.00 1.15 -0.10', normal: '0 1 0' },
    'Lengan Kiri': { position: '0.30 1.30 0.00', normal: '0 1 0' },
    'Lengan Kanan': { position: '-0.30 1.30 0.00', normal: '0 1 0' },
    'Paha Kiri': { position: '0.12 0.70 0.00', normal: '0 1 0' },
    'Paha Kanan': { position: '-0.12 0.70 0.00', normal: '0 1 0' },
    'Betis Kiri': { position: '0.12 0.30 0.00', normal: '0 1 0' },
    'Betis Kanan': { position: '-0.12 0.30 0.00', normal: '0 1 0' },
  };

  document.addEventListener('DOMContentLoaded', function() {
    // Toggle kondisi fisik section
    document.querySelectorAll('input[name=\"kondisi_fisik\"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const section = document.getElementById('editTandaFisikSection');
        if (this.value === 'ada_tanda') {
          section.style.display = 'block';
        } else {
          section.style.display = 'none';
        }
      });
    });

    // Initialize 3D Model
    const modelViewer = document.getElementById('editBodyModel3D');
    if (modelViewer) {
      const jenisKelamin = modelViewer.getAttribute('data-jenis-kelamin') || 'laki-laki';
      const modelUrl = jenisKelamin === 'perempuan' 
        ? @json(asset('assets/3d-models/female.glb'))
        : @json(asset('assets/3d-models/male.glb'));
      
      modelViewer.src = modelUrl;

      modelViewer.addEventListener('load', () => {
        updateEditBodyHotspots();
      });
    }

    // Body part selection
    document.querySelectorAll('.edit-body-part-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const part = this.getAttribute('data-part');
        const index = editSelectedBodyParts.indexOf(part);
        
        if (index > -1) {
          editSelectedBodyParts.splice(index, 1);
          this.classList.remove('btn-danger');
          this.classList.add('btn-outline-secondary');
        } else {
          editSelectedBodyParts.push(part);
          this.classList.remove('btn-outline-secondary');
          this.classList.add('btn-danger');
        }
        
        updateEditSelectedLocations();
        updateEditBodyHotspots();
      });

      // Set initial state
      if (editSelectedBodyParts.includes(btn.getAttribute('data-part'))) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-danger');
      }
    });

    // Initialize signature pad pengantar
    const canvasPengantar = document.getElementById('editSignaturePengantar');
    if (canvasPengantar) {
      function resizeCanvasPengantar() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvasPengantar.width = canvasPengantar.offsetWidth * ratio;
        canvasPengantar.height = canvasPengantar.offsetHeight * ratio;
        canvasPengantar.getContext('2d').scale(ratio, ratio);
        if (editSignaturePengantar) {
          editSignaturePengantar.clear();
        }
      }

      resizeCanvasPengantar();
      editSignaturePengantar = new SignaturePad(canvasPengantar, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
      });
      window.addEventListener('resize', resizeCanvasPengantar);
    }

    // Toggle signature pengantar section
    const keepSignaturePengantar = document.getElementById('keep_signature_pengantar');
    const signaturePengantarSection = document.getElementById('signaturePengantarSection');
    
    if (keepSignaturePengantar) {
      keepSignaturePengantar.addEventListener('change', function() {
        if (this.checked) {
          signaturePengantarSection.style.display = 'none';
          if (editSignaturePengantar) editSignaturePengantar.clear();
        } else {
          signaturePengantarSection.style.display = 'block';
        }
      });
    }

    document.getElementById('editClearSignaturePengantar')?.addEventListener('click', function() {
      if (editSignaturePengantar) {
        editSignaturePengantar.clear();
      }
    });

    // Form submission
    const form = document.querySelector('form[action*=\"absensi\"]');
    if (form && !form.id) {
      form.addEventListener('submit', function(e) {
        // Save signature pengantar if needed
        const keepSigPengantar = document.getElementById('keep_signature_pengantar');
        if (!keepSigPengantar || !keepSigPengantar.checked) {
          if (editSignaturePengantar && !editSignaturePengantar.isEmpty()) {
            const signatureData = editSignaturePengantar.toDataURL();
            document.getElementById('edit_signature_pengantar_data').value = signatureData;
          }
        }
      });
    }

    // Initialize selected locations display
    updateEditSelectedLocations();
  });

  function updateEditSelectedLocations() {
    const container = document.getElementById('editSelectedLocations');
    const hiddenInput = document.getElementById('edit_lokasi_luka');
    
    if (!container) return;
    
    container.innerHTML = '';
    
    if (editSelectedBodyParts.length > 0) {
      const label = document.createElement('p');
      label.className = 'mb-2 fw-semibold';
      label.textContent = 'Lokasi yang dipilih:';
      container.appendChild(label);
      
      editSelectedBodyParts.forEach(part => {
        const badge = document.createElement('span');
        badge.className = 'location-badge';
        badge.innerHTML = `${part} <span class="badge-remove" onclick="removeEditBodyPart('${part}')">×</span>`;
        container.appendChild(badge);
      });
    }
    
    hiddenInput.value = JSON.stringify(editSelectedBodyParts);
  }

  function updateEditBodyHotspots() {
    const modelViewer = document.getElementById('editBodyModel3D');
    if (!modelViewer) return;

    // Remove existing hotspots
    const existingHotspots = modelViewer.querySelectorAll('.body-hotspot');
    existingHotspots.forEach(h => h.remove());

    // Add hotspots for selected parts
    editSelectedBodyParts.forEach(part => {
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

  window.removeEditBodyPart = function(part) {
    const index = editSelectedBodyParts.indexOf(part);
    if (index > -1) {
      editSelectedBodyParts.splice(index, 1);
      
      // Update button state
      const btn = document.querySelector(`.edit-body-part-btn[data-part=\"${part}\"]`);
      if (btn) {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-secondary');
      }
      
      updateEditSelectedLocations();
      updateEditBodyHotspots();
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