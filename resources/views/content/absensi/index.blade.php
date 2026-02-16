@extends('layouts.contentNavbarLayout')

@section('title', 'Absensi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
<style>
  .search-wrapper {
    flex: 1 1 100%;
    min-width: 200px;
  }

  .filter-date,
  .filter-status {
    flex: 1 1 calc(50% - 0.5rem);
    min-width: 0;
  }

  @media (min-width: 992px) {
    .search-wrapper {
      flex: 1 1 auto;
    }

    .filter-date,
    .filter-status {
      flex: 0 0 auto;
      width: 200px;
    }
  }

  /* Hide badge text on mobile, show icon only */
  @media (max-width: 575.98px) {
    .badge .badge-text {
      display: none;
    }

    .badge i {
      margin: 0 !important;
    }
  }

  /* Mobile button sizing - keep 50% layout on mobile only */
  @media (max-width: 991.98px) {

    .btn-outline-primary,
    .btn-outline-secondary {
      flex: 1 1 calc(50% - 0.5rem);
    }
  }

  /* Signature pad styles */
  .signature-pad {
    border: 3px solid #007bff;
    border-radius: 8px;
    display: block;
    width: 100%;
    height: 250px;
    cursor: crosshair;
    background-color: #fff;
  }
</style>
@endsection

@section('content')
<!-- Card 1: Header with Title and Button -->
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Absensi Anak Didik</h4>
            <p class="text-body-secondary mb-0">Rekapitulasi kehadiran bulanan anak didik</p>
          </div>
          <div class="d-flex gap-2">
            @if(auth()->check() && auth()->user()->role === 'admin')
            <!-- Export PDF Button (Admin Only) -->
            <button type="button" class="btn btn-danger d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;" title="Export PDF" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
              <i class="ri-file-pdf-line" style="font-size:1.7em;"></i>
            </button>
            <button type="button" class="btn btn-danger d-none d-sm-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
              <i class="ri-file-pdf-line me-2"></i>Export PDF
            </button>
            @endif

            @if(auth()->check() && in_array(auth()->user()->role, ['terapis', 'guru']))
            <!-- Tambah Absensi Button (Terapis & Guru Only) -->
            <a href="{{ route('absensi.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
              <i class="ri-add-line" style="font-size:1.7em;"></i>
            </a>
            <a href="{{ route('absensi.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
              <i class="ri-add-line me-2"></i>Tambah Absensi
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="ri-close-circle-line me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('absensi.index') }}" id="filterForm" class="d-flex gap-2 flex-wrap">
      <div class="search-wrapper">
        <input type="text" name="search" id="searchInput" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ request('search') }}">
      </div>
      <input type="month" name="tanggal" id="tanggalFilter" class="form-control filter-date" value="{{ request('tanggal') ? \Carbon\Carbon::parse(request('tanggal'))->format('Y-m') : now()->format('Y-m') }}" title="Pilih Bulan">
      <select name="status" id="statusFilter" class="form-select filter-status">
        <option value="">Semua Status</option>
        <option value="hadir" @selected(request('status')==='hadir' )>Hadir</option>
        <option value="izin" @selected(request('status')==='izin' )>Izin</option>
        <option value="alfa" @selected(request('status')==='alfa' )>Alfa</option>
      </select>
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<!-- Card 3: Table -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Periode: {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }} | Status: {{ \Carbon\Carbon::parse($todayDate)->format('d M Y') }}</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Status Hari Ini</th>
              <th class="text-center">Penjemputan</th>
              <th class="text-center">Hadir</th>
              <th class="text-center">Izin</th>
              <th class="text-center">Alfa</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anakDidiks as $index => $anakDidik)
            @php
            $summary = $absensiSummary[$anakDidik->id] ?? ['hadir' => 0, 'izin' => 0, 'alfa' => 0];
            $todayAbsensi = $todayAbsensis[$anakDidik->id] ?? null;
            @endphp
            <tr id="row-{{ $anakDidik->id }}">
              <td>{{ ($anakDidiks->currentPage() - 1) * $anakDidiks->perPage() + $index + 1 }}</td>
              <td>
                <p class="text-heading mb-0 fw-medium">{{ $anakDidik->nama ?? '-' }}</p>
              </td>
              <td>
                @if($todayAbsensi)
                @if($todayAbsensi->status === 'hadir')
                <span class="badge bg-success">Hadir</span>
                @elseif($todayAbsensi->status === 'izin')
                <span class="badge bg-warning text-dark">Izin</span>
                @else
                <span class="badge bg-danger">Alfa</span>
                @endif
                @else
                <span class="badge bg-secondary">Belum Absensi</span>
                @endif
              </td>
              <td class="text-center">
                @if($todayAbsensi && $todayAbsensi->waktu_jemput)
                <span class="badge bg-info"><i class="ri-check-line"></i></span>
                @else
                -
                @endif
              </td>
              <td class="text-center">
                <span class="badge bg-success">{{ $summary['hadir'] }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-warning text-dark">{{ $summary['izin'] }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-danger">{{ $summary['alfa'] }}</span>
              </td>
              <td>
                <div class="d-flex gap-1">
                  @if($todayAbsensi && !$todayAbsensi->waktu_jemput && !in_array(($todayAbsensi->status ?? ''), ['alfa', 'izin']))
                  <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                    data-bs-target="#jemputModal" data-absensi-id="{{ $todayAbsensi->id }}"
                    data-anak-nama="{{ $anakDidik->nama }}" onclick="openJemputModal(this)" title="Catat Penjemputan">
                    <i class="ri-user-follow-line"></i>
                  </button>
                  @endif
                  <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                    data-bs-target="#riwayatAbsensiModal" data-anak-didik-id="{{ $anakDidik->id }}"
                    onclick="loadRiwayatAbsensi(this)" title="Riwayat Absensi">
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada anak didik ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center pagination-footer-fix">
        <style>
          .pagination-footer-fix {
            flex-wrap: nowrap !important;
            gap: 0.5rem;
          }

          .pagination-footer-fix>div,
          .pagination-footer-fix>nav {
            min-width: 0;
            max-width: 100%;
          }

          .pagination-footer-fix nav {
            flex-shrink: 1;
            flex-grow: 0;
          }

          @media (max-width: 767.98px) {
            .pagination-footer-fix {
              flex-direction: row !important;
              align-items: center !important;
              flex-wrap: nowrap !important;
            }

            .pagination-footer-fix>div,
            .pagination-footer-fix>nav {
              width: auto !important;
              max-width: 100%;
            }

            .pagination-footer-fix nav ul.pagination {
              flex-wrap: nowrap !important;
            }
          }
        </style>
        <div class="text-body-secondary">
          Menampilkan {{ $anakDidiks->firstItem() ?? 0 }} hingga {{ $anakDidiks->lastItem() ?? 0 }} dari {{ $anakDidiks->total() }} data
        </div>
        <nav>
          {{ $anakDidiks->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal Riwayat Absensi -->
<div class="modal fade" id="riwayatAbsensiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="riwayatAbsensiModalTitle" style="font-size: 0.9rem;">Riwayat Absensi Anak Didik</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatAbsensiList">
          <div class="text-center text-muted">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer d-none d-md-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary flex-shrink-0" data-bs-dismiss="modal" style="width: auto;">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Absensi (dipanggil dari riwayat) -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">
          <span>Detail Absensi</span>
          <span id="detailStudentName" class="ms-2"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer d-none d-md-flex justify-content-end">
        <button type="button" class="btn btn-outline-secondary flex-shrink-0" data-bs-dismiss="modal" style="width: auto;">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Export PDF Filter -->
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportPdfModalLabel">
          <i class="ri-file-pdf-line me-2"></i>Export Data Absensi ke PDF
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('absensi.export-pdf') }}" method="GET" target="_blank">
        <div class="modal-body">
          <p class="text-muted mb-3">Pilih rentang tanggal untuk mengekspor data absensi</p>

          <div class="mb-3">
            <label for="export_start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="export_start_date" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" required>
          </div>

          <div class="mb-3">
            <label for="export_end_date" class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="export_end_date" name="end_date" value="{{ now()->endOfMonth()->toDateString() }}" required>
          </div>

          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="ri-information-line me-2"></i>
            <small>Data akan dibuka di tab baru. Gunakan fitur Print browser (Ctrl+P) untuk menyimpan sebagai PDF.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="ri-file-pdf-line me-2"></i>Generate PDF
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Foto Bukti -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fotoModalLabel">Foto Bukti</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="fotoModalBody">
        <div class="row g-3" id="fotoGallery">
          <!-- Foto akan ditampilkan di sini -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Catat Penjemputan -->
<div class="modal fade" id="jemputModal" tabindex="-1" aria-labelledby="jemputModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="jemputModalLabel">
          <i class="ri-user-follow-line me-2"></i>Catat Penjemputan: <span id="jemputAnakNama"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="jemputForm" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="jemput_absensi_id" name="absensi_id">
        <div class="modal-body">
          <div class="row g-3">
            <!-- Nama Penjemput -->
            <div class="col-12">
              <label for="nama_penjemput" class="form-label">Nama Orang Tua/Penjemput <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama_penjemput" name="nama_penjemput" required placeholder="Masukkan nama penjemput">
            </div>

            <!-- Foto Penjemput -->
            <div class="col-12">
              <label class="form-label">Foto Penjemput</label>
              <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="jemputCameraBtn">
                  <i class="ri-camera-line me-1"></i>Ambil Foto
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="jemputUploadBtn">
                  <i class="ri-upload-line me-1"></i>Upload Foto
                </button>
                <input type="file" id="jemputFileInput" name="foto_penjemput[]" accept="image/*" multiple style="display:none;">
              </div>
              <div id="jemputCameraContainer" style="display:none;">
                <video id="jemputCameraStream" autoplay playsinline style="max-width:100%; border-radius:8px;"></video>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="jemputCaptureBtn">
                  <i class="ri-camera-line me-1"></i>Ambil Foto
                </button>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="jemputStopCameraBtn">
                  <i class="ri-stop-line me-1"></i>Tutup Kamera
                </button>
              </div>
              <div id="jemputPreviewContainer" class="mt-3" style="display:none;">
                <label class="form-label fw-semibold">Preview Foto:</label>
                <div id="jemputPhotoPreview" class="d-flex flex-wrap gap-2"></div>
              </div>
            </div>

            <!-- Tanda Tangan Penjemput -->
            <div class="col-12">
              <label class="form-label">Tanda Tangan Penjemput <span class="text-danger">*</span></label>
              <canvas id="jemputSignaturePad" class="signature-pad"></canvas>
              <div class="d-flex gap-2 align-items-center mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="jemputClearSignature" title="Hapus Tanda Tangan">
                  <i class="ri-delete-bin-line me-0 me-md-1"></i><span class="d-none d-md-inline">Hapus Tanda Tangan</span>
                </button>
                <span class="text-muted"><small id="jemputSignatureStatus">Belum ada tanda tangan</small></span>
              </div>
              <input type="hidden" id="jemput_signature_data" name="signature_penjemput" required>
            </div>

            <!-- Keterangan -->
            <div class="col-12">
              <label for="keterangan_penjemput" class="form-label">Keterangan (Opsional)</label>
              <textarea class="form-control" id="keterangan_penjemput" name="keterangan_penjemput" rows="3" placeholder="Catatan tambahan tentang penjemputan..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-end">
          <button type="submit" class="btn btn-success" title="Simpan Data Penjemputan">
            <i class="ri-save-line me-0 me-md-1"></i><span class="d-none d-md-inline">Simpan Data Penjemputan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script>
  // 3D Model coordinates for detail modal
  const bodyPartCoordinates = {
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

  // Initialize 3D model in detail modal
  window.initializeDetail3DModel = async function() {
    console.log('=== initializeDetail3DModel (INDEX) started ===');

    const bodyModel3D = document.getElementById('detailBodyModel3D');
    if (!bodyModel3D) {
      console.error('✗ detailBodyModel3D element not found');
      return;
    }

    console.log('✓ Found model-viewer element');

    // Ensure model-viewer library is loaded
    if (typeof customElements === 'undefined' || !customElements.get('model-viewer')) {
      console.log('Loading model-viewer library...');
      const script = document.createElement('script');
      script.type = 'module';
      script.src = 'https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js';
      script.onload = () => {
        console.log('✓ model-viewer library loaded');
        setTimeout(() => initializeDetail3DModel(), 100);
      };
      script.onerror = () => {
        console.error('✗ Failed to load model-viewer library');
      };
      document.head.appendChild(script);
      return;
    }

    // Get data from attributes
    const jenis_kelamin = bodyModel3D.getAttribute('data-jenis-kelamin') || 'laki-laki';
    const marked_locations = JSON.parse(bodyModel3D.getAttribute('data-lokasi-luka') || '[]');

    console.log('Jenis Kelamin:', jenis_kelamin);
    console.log('Marked Locations:', marked_locations);

    // Set model path based on jenis_kelamin
    let modelPath = '/assets/Male.glb';
    const jenisKelamin = (jenis_kelamin || '').toLowerCase().trim();

    if (jenisKelamin === 'perempuan' || jenisKelamin === 'p') {
      modelPath = '/assets/Female.glb';
      console.log('→ Using Female model');
    } else if (jenisKelamin === 'laki-laki' || jenisKelamin === 'l') {
      modelPath = '/assets/Male.glb';
      console.log('→ Using Male model');
    } else {
      console.log('→ Using default Male model');
    }

    console.log('Model path:', modelPath);
    bodyModel3D.src = modelPath;

    // Function to add hotspots
    function addHotspots() {
      console.log('→ Adding hotspots...');

      // Remove existing hotspots first
      const existingHotspots = bodyModel3D.querySelectorAll('[slot^="hotspot-"]');
      console.log(`Removing ${existingHotspots.length} existing hotspots`);
      existingHotspots.forEach(hotspot => hotspot.remove());

      // Add hotspots for marked locations
      marked_locations.forEach((location, index) => {
        const coords = bodyPartCoordinates[location];
        if (coords) {
          const hotspot = document.createElement('div');
          hotspot.className = 'body-hotspot';
          hotspot.slot = `hotspot-${index}`;
          hotspot.setAttribute('data-position', coords.position);
          hotspot.setAttribute('data-normal', coords.normal);
          hotspot.title = location;

          bodyModel3D.appendChild(hotspot);
          console.log(`✓ Added hotspot: ${location}`);
        } else {
          console.warn(`✗ No coordinates found for: ${location}`);
        }
      });

      console.log(`✓ Total ${marked_locations.length} hotspots loaded`);
    }

    // Add hotspots when model loads
    bodyModel3D.addEventListener('load', function() {
      console.log('✓ Model loaded event fired');
      addHotspots();
    });

    // Try to add hotspots immediately (for cached models)
    if (bodyModel3D.src && bodyModel3D.src !== '') {
      setTimeout(addHotspots, 50);
    }

    // Handle errors
    bodyModel3D.addEventListener('error', function(event) {
      console.error('✗ Model loading error:', event);
    });

    console.log('=== initializeDetail3DModel (INDEX) completed ===');
  };

  // Load riwayat absensi grouped by month
  // Store current nama_anak for jemput modal
  let currentNamaAnak = '';

  window.loadRiwayatAbsensi = function(btn) {
    const anakDidikId = btn.getAttribute('data-anak-didik-id');
    const listDiv = document.getElementById('riwayatAbsensiList');
    const modalTitle = document.getElementById('riwayatAbsensiModalTitle');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';

    fetch(`/absensi/riwayat/${anakDidikId}`)
      .then(response => response.json())
      .then(res => {
        // Update modal title with nama anak
        if (res.nama_anak) {
          modalTitle.textContent = `Riwayat Absensi - ${res.nama_anak}`;
          currentNamaAnak = res.nama_anak; // Store for jemput modal
        }

        if (!res.success || !res.riwayat || res.riwayat.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat absensi.</div>';
          return;
        }

        // Cache riwayat for client-side filtering
        window._riwayatCache = window._riwayatCache || {};
        window._riwayatCache[anakDidikId] = res.riwayat;

        // Build filter controls: month select + status select (two columns, 50% each)
        let filterHtml = `<div class="row g-2 mb-3">
          <div class="col-12 col-md-6">
            <label class="form-label mb-1" style="font-size:0.8rem">Bulan</label>
            <select id="riwayatMonthSelect" class="form-select form-select-sm w-100"></select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label mb-1" style="font-size:0.8rem">Status</label>
            <select id="riwayatStatusSelect" class="form-select form-select-sm w-100">
              <option value="all">Semua</option>
              <option value="hadir">Hadir</option>
              <option value="izin">Izin</option>
              <option value="alfa">Alfa</option>
            </select>
          </div>
        </div>`;

        // Insert filters + container for list
        listDiv.innerHTML = filterHtml + '<div id="riwayatAbsensiContent"></div>';

        const monthSelect = document.getElementById('riwayatMonthSelect');
        const statusSelect = document.getElementById('riwayatStatusSelect');
        const contentDiv = document.getElementById('riwayatAbsensiContent');

        // Populate month select from available groups
        res.riwayat.forEach(group => {
          const opt = document.createElement('option');
          opt.value = group.month_key;
          opt.text = group.month;
          monthSelect.appendChild(opt);
        });

        // Default to first month (most recent)
        if (res.riwayat.length) monthSelect.value = res.riwayat[0].month_key;

        function renderFiltered() {
          const selMonth = monthSelect.value;
          const selStatus = statusSelect.value;
          const riwayat = window._riwayatCache[anakDidikId] || [];

          // Find month group
          const group = riwayat.find(g => g.month_key === selMonth);
          if (!group || !Array.isArray(group.items) || group.items.length === 0) {
            contentDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat untuk pilihan ini.</div>';
            return;
          }

          let html = '<ul class="list-group">';
          group.items.forEach(item => {
            if (selStatus !== 'all' && item.status !== selStatus) return;

            let statusBadge = '';
            if (item.status === 'hadir') statusBadge = '<span class="badge bg-success">Hadir</span>';
            else if (item.status === 'izin') statusBadge = '<span class="badge bg-warning text-dark">Izin</span>';
            else statusBadge = '<span class="badge bg-danger">Alfa</span>';

            let kondisiBadge = '';
            if (item.status !== 'izin') {
              if (item.kondisi_fisik === 'ada_tanda') kondisiBadge = `<span class="badge bg-danger ms-2"><i class="ri-alert-line"></i><span class="badge-text"> ${item.jenis_tanda_fisik_label || 'Ada Tanda Fisik'}</span></span>`;
              else if (item.kondisi_fisik === 'baik') kondisiBadge = '<span class="badge bg-success ms-2"><i class="ri-check-line"></i><span class="badge-text"> Baik</span></span>';
            }

            let fotoBadge = '';
            if (item.foto_bukti && Array.isArray(item.foto_bukti) && item.foto_bukti.length > 0) {
              fotoBadge = `<span class="badge bg-info ms-2"><i class="ri-image-line"></i><span class="badge-text"> ${item.foto_bukti.length} Foto</span></span>`;
            }

            let pickupWarning = '';
            let pickupButton = '';
            if (item.needs_pickup_data) {
              pickupWarning = `<div class="alert alert-warning mt-2 mb-0 py-2 px-2 d-flex align-items-center" style="font-size: 0.85rem;"><i class="ri-alert-line me-2"></i><small>Catatan penjemputan belum diisi untuk tanggal ini</small></div>`;
              pickupButton = `<button type="button" class="btn btn-sm btn-outline-warning" onclick="openJemputFromRiwayat(${item.id})" title="Catat Penjemputan"><i class="ri-user-follow-line"></i></button>`;
            }

            html += `<li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <div>${item.tanggal_formatted}</div>
                    <div class="mt-1">${statusBadge}${kondisiBadge}${fotoBadge}</div>
                    ${pickupWarning}
                  </div>
                  <div class="flex-shrink-0">
                    <div class="d-none d-sm-inline-flex gap-2">
                      ${pickupButton}
                      <button class="btn btn-sm btn-outline-info" onclick="showAbsensiDetail(${item.id})" title="Lihat Detail"><i class="ri-eye-line"></i></button>
                      <a href="/absensi/${item.id}/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="ri-edit-line"></i></a>
                      <button class="btn btn-sm btn-outline-danger" onclick="deleteAbsensiFromRiwayat(${item.id}, ${anakDidikId})" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                    </div>
                    <div class="d-inline-flex d-sm-none">
                      <div class="dropdown">
                        <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" id="absensiActionsToggle${item.id}" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;"><i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="absensiActionsToggle${item.id}">
                          ${item.needs_pickup_data ? `<li><button class="dropdown-item text-warning" type="button" onclick="openJemputFromRiwayat(${item.id})">Penjemputan</button></li>` : ''}
                          <li><button class="dropdown-item" type="button" onclick="showAbsensiDetail(${item.id})">Lihat Detail</button></li>
                          <li><a class="dropdown-item" href="/absensi/${item.id}/edit">Edit</a></li>
                          <li><button class="dropdown-item text-danger" type="button" onclick="deleteAbsensiFromRiwayat(${item.id}, ${anakDidikId})">Hapus</button></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </li>`;
          });

          html += '</ul>';
          contentDiv.innerHTML = html;
        }

        monthSelect.addEventListener('change', renderFiltered);
        statusSelect.addEventListener('change', renderFiltered);
        // Initial render
        renderFiltered();
      })
      .catch(() => {
        listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
      });
  }

  // Show detail absensi in modal
  window.showAbsensiDetail = function(id) {
    console.log('Opening detail modal for absensi ID:', id);

    // Hide riwayat modal first
    const riwayatModal = bootstrap.Modal.getInstance(document.getElementById('riwayatAbsensiModal'));
    if (riwayatModal) riwayatModal.hide();

    // Show detail modal
    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

    fetch(`/absensi/${id}/detail`)
      .then(res => res.text())
      .then(html => {
        console.log('Detail HTML loaded, inserting into modal...');
        document.getElementById('modalBody').innerHTML = html;

        // Extract and set student name in modal title
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const studentName = tempDiv.querySelector('[data-student-name]')?.getAttribute('data-student-name') || '';
        document.getElementById('detailStudentName').textContent = studentName ? `- ${studentName}` : '';

        detailModal.show();

        // Initialize 3D model after modal is shown
        console.log('Waiting for modal to be visible...');
        setTimeout(() => {
          console.log('Calling initializeDetail3DModel...');
          if (typeof initializeDetail3DModel === 'function') {
            initializeDetail3DModel();
          } else {
            console.warn('initializeDetail3DModel is not a function');
          }
        }, 300);
      })
      .catch(err => {
        console.error('Error loading detail:', err);
        alert('Gagal memuat detail absensi');
      });
  }

  // Delete absensi from riwayat modal
  window.deleteAbsensiFromRiwayat = async function(id, anakDidikId) {
    if (!confirm('Hapus data absensi ini?')) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
      const res = await fetch(`/absensi/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        }
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Gagal menghapus data');

      showToast(data.message || 'Absensi berhasil dihapus', 'success');

      // Reload riwayat
      const dummyBtn = document.createElement('button');
      dummyBtn.setAttribute('data-anak-didik-id', anakDidikId);
      loadRiwayatAbsensi(dummyBtn);
    } catch (err) {
      showToast(err.message || 'Gagal menghapus data', 'danger');
    }
  }

  // Open jemput modal from riwayat modal
  window.openJemputFromRiwayat = function(absensiId) {
    // Close riwayat modal first
    const riwayatModal = bootstrap.Modal.getInstance(document.getElementById('riwayatAbsensiModal'));
    if (riwayatModal) {
      riwayatModal.hide();
    }

    // Wait for modal to close, then open jemput modal
    setTimeout(() => {
      // Create a dummy button with required data attributes
      const dummyBtn = document.createElement('button');
      dummyBtn.setAttribute('data-absensi-id', absensiId);
      dummyBtn.setAttribute('data-anak-nama', currentNamaAnak);

      // Call the existing openJemputModal function
      openJemputModal(dummyBtn);

      // Show jemput modal
      const jemputModalEl = document.getElementById('jemputModal');
      const jemputModal = new bootstrap.Modal(jemputModalEl);
      jemputModal.show();
    }, 300);
  }

  // Show foto bukti modal
  window.showFotoModal = function(fotoPaths) {
    const fotoGallery = document.getElementById('fotoGallery');

    if (!Array.isArray(fotoPaths) || fotoPaths.length === 0) {
      fotoGallery.innerHTML = '<div class="alert alert-warning col-12">Tidak ada foto tersimpan</div>';
      return;
    }

    fotoGallery.innerHTML = '';
    fotoPaths.forEach((fotoPath, index) => {
      const col = document.createElement('div');
      col.className = 'col-md-6';
      col.innerHTML = `
        <a href="{{ asset('storage') }}/${fotoPath}" target="_blank" class="d-block">
          <img src="{{ asset('storage') }}/${fotoPath}" alt="Foto ${index + 1}" class="img-thumbnail w-100" style="height: 300px; object-fit: cover;">
        </a>
        <small class="text-muted d-block mt-2">Foto ${index + 1}</small>
      `;
      fotoGallery.appendChild(col);
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Enhanced filter form interactions
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const tanggalFilter = document.getElementById('tanggalFilter');
    const statusFilter = document.getElementById('statusFilter');

    // Auto-submit on date change
    if (tanggalFilter) {
      tanggalFilter.addEventListener('change', function() {
        filterForm.submit();
      });
    }

    // Auto-submit on status change
    if (statusFilter) {
      statusFilter.addEventListener('change', function() {
        filterForm.submit();
      });
    }

    // Submit on Enter key in search input
    if (searchInput) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          filterForm.submit();
        }
      });
    }

    // Handle delete (kept for backward compatibility if needed in detail modal)
    document.querySelectorAll('.btn-delete-absensi').forEach(function(btn) {
      btn.addEventListener('click', async function() {
        const id = this.getAttribute('data-id');
        const tr = this.closest('tr');
        if (!id || !tr) return;
        if (!confirm('Hapus data absensi ini?')) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
          const res = await fetch(`/absensi/${id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            }
          });
          const data = await res.json();
          if (!res.ok) throw new Error(data.message || 'Gagal menghapus data');
          tr.remove();
          showToast(data.message || 'Absensi berhasil dihapus', 'success');
        } catch (err) {
          showToast(err.message || 'Gagal menghapus data', 'danger');
        }
      });
    });

    // Restore riwayat modal when detail modal is closed
    document.getElementById('detailModal').addEventListener('hidden.bs.modal', function() {
      const riwayatModalEl = document.getElementById('riwayatAbsensiModal');
      if (riwayatModalEl && riwayatModalEl.classList.contains('show') === false) {
        // Check if we came from riwayat modal
        if (window._fromRiwayatModal) {
          const riwayatModal = new bootstrap.Modal(riwayatModalEl);
          riwayatModal.show();
          window._fromRiwayatModal = false;
        }
      }
    });

    // Track when opening detail from riwayat
    document.addEventListener('click', function(e) {
      if (e.target.closest('button[onclick^="showAbsensiDetail"]')) {
        window._fromRiwayatModal = true;
      }
    });
  });

  if (typeof showToast !== 'function') {
    function showToast(message, type = 'success') {
      let toast = document.getElementById('customToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
        toast.style.zIndex = 9999;
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        document.body.appendChild(toast);
      } else {
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
      }
      toast.querySelector('.toast-body').textContent = message;
      var bsToast = bootstrap.Toast.getOrCreateInstance(toast, {
        delay: 2000
      });
      bsToast.show();
    }
  }

  // Load model-viewer library globally
  if (typeof customElements !== 'undefined' && !customElements.get('model-viewer')) {
    const mvScript = document.createElement('script');
    mvScript.type = 'module';
    mvScript.src = 'https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js';
    document.body.appendChild(mvScript);
  }

  // ===== JEMPUT MODAL FUNCTIONALITY =====
  let jemputSignaturePad;
  let jemputCameraStream = null;
  let jemputCapturedPhotos = [];

  // Open jemput modal
  window.openJemputModal = function(button) {
    const absensiId = button.getAttribute('data-absensi-id');
    const anakNama = button.getAttribute('data-anak-nama');

    document.getElementById('jemput_absensi_id').value = absensiId;
    document.getElementById('jemputAnakNama').textContent = anakNama;

    // Reset form
    document.getElementById('jemputForm').reset();
    jemputCapturedPhotos = [];
    document.getElementById('jemputPhotoPreview').innerHTML = '';
    document.getElementById('jemputPreviewContainer').style.display = 'none';
  };

  // Initialize signature pad after modal is shown
  const jemputModal = document.getElementById('jemputModal');
  if (jemputModal) {
    jemputModal.addEventListener('shown.bs.modal', function() {
      if (!jemputSignaturePad) {
        const canvas = document.getElementById('jemputSignaturePad');

        // Resize canvas first
        function resizeJemputCanvas() {
          const ratio = Math.max(window.devicePixelRatio || 1, 1);
          canvas.width = canvas.offsetWidth * ratio;
          canvas.height = canvas.offsetHeight * ratio;
          canvas.getContext('2d').scale(ratio, ratio);
          if (jemputSignaturePad) {
            jemputSignaturePad.clear();
          }
        }

        resizeJemputCanvas();

        // Initialize SignaturePad
        jemputSignaturePad = new SignaturePad(canvas, {
          backgroundColor: 'rgb(255, 255, 255)',
          penColor: 'rgb(0, 0, 0)'
        });

        // Add event listener for stroke end
        jemputSignaturePad.addEventListener('endStroke', function() {
          const statusSpan = document.getElementById('jemputSignatureStatus');
          if (statusSpan) {
            statusSpan.innerHTML = '<i class="ri-check-line me-1"></i>Tanda tangan sudah dibuat';
            statusSpan.className = 'text-success';
          }
        });

        window.addEventListener('resize', resizeJemputCanvas);
      } else {
        jemputSignaturePad.clear();
        const statusSpan = document.getElementById('jemputSignatureStatus');
        if (statusSpan) {
          statusSpan.innerHTML = 'Belum ada tanda tangan';
          statusSpan.className = 'text-muted';
        }
      }
    });
  }

  // Camera functionality for jemput
  document.getElementById('jemputCameraBtn')?.addEventListener('click', async function() {
    const container = document.getElementById('jemputCameraContainer');
    const video = document.getElementById('jemputCameraStream');

    try {
      jemputCameraStream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: 'user'
        }
      });
      video.srcObject = jemputCameraStream;
      container.style.display = 'block';
    } catch (err) {
      alert('Tidak dapat mengakses kamera: ' + err.message);
    }
  });

  document.getElementById('jemputStopCameraBtn')?.addEventListener('click', function() {
    if (jemputCameraStream) {
      jemputCameraStream.getTracks().forEach(track => track.stop());
      document.getElementById('jemputCameraStream').srcObject = null;
      document.getElementById('jemputCameraContainer').style.display = 'none';
    }
  });

  document.getElementById('jemputCaptureBtn')?.addEventListener('click', function() {
    const video = document.getElementById('jemputCameraStream');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(blob => {
      const file = new File([blob], `jemput_${Date.now()}.jpg`, {
        type: 'image/jpeg'
      });
      jemputCapturedPhotos.push(file);
      updateJemputPhotoPreview();

      // Stop camera after capture
      if (jemputCameraStream) {
        jemputCameraStream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        document.getElementById('jemputCameraContainer').style.display = 'none';
      }
    }, 'image/jpeg', 0.9);
  });

  document.getElementById('jemputUploadBtn')?.addEventListener('click', function() {
    document.getElementById('jemputFileInput').click();
  });

  document.getElementById('jemputFileInput')?.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    jemputCapturedPhotos.push(...files);
    updateJemputPhotoPreview();
  });

  function updateJemputPhotoPreview() {
    const preview = document.getElementById('jemputPhotoPreview');
    const container = document.getElementById('jemputPreviewContainer');

    if (jemputCapturedPhotos.length === 0) {
      container.style.display = 'none';
      return;
    }

    container.style.display = 'block';
    preview.innerHTML = '';

    jemputCapturedPhotos.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'position-relative';
        div.innerHTML = `
          <img src="${e.target.result}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
          <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                  onclick="removeJemputPhoto(${index})" style="padding:0.1rem 0.3rem;">
            <i class="ri-close-line"></i>
          </button>
        `;
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  window.removeJemputPhoto = function(index) {
    jemputCapturedPhotos.splice(index, 1);
    updateJemputPhotoPreview();
  };

  // Clear signature
  document.getElementById('jemputClearSignature')?.addEventListener('click', function() {
    if (jemputSignaturePad) {
      jemputSignaturePad.clear();
      const statusSpan = document.getElementById('jemputSignatureStatus');
      if (statusSpan) {
        statusSpan.innerHTML = 'Belum ada tanda tangan';
        statusSpan.className = 'text-muted';
      }
    }
  });

  // Handle form submission
  document.getElementById('jemputForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validate signature
    if (jemputSignaturePad.isEmpty()) {
      alert('Tanda tangan penjemput harus diisi!');
      return;
    }

    // Get signature data
    const signatureData = jemputSignaturePad.toDataURL();
    document.getElementById('jemput_signature_data').value = signatureData;

    // Prepare form data
    const formData = new FormData(this);

    // Remove default file input data and add captured photos
    formData.delete('foto_penjemput[]');
    jemputCapturedPhotos.forEach((file, index) => {
      formData.append('foto_penjemput[]', file, file.name);
    });

    const absensiId = document.getElementById('jemput_absensi_id').value;
    const token = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';

    try {
      const response = await fetch(`/absensi/${absensiId}/jemput`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: formData
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Gagal menyimpan data penjemputan');
      }

      // Close modal
      bootstrap.Modal.getInstance(document.getElementById('jemputModal')).hide();

      // Show success message
      showToast(data.message || 'Data penjemputan berhasil disimpan', 'success');

      // Reload page after short delay
      setTimeout(() => {
        window.location.reload();
      }, 1500);

    } catch (error) {
      alert(error.message);
    }
  });

  // Load SignaturePad library
  if (typeof SignaturePad === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js';
    document.head.appendChild(script);
  }
</script>
@endpush