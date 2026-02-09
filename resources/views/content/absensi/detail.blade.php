<style>
  .detail-3d-container {
    border: 2px solid #ccc;
    border-radius: 8px;
    background: linear-gradient(to bottom, #f0f4ff 0%, #e8eefc 100%);
    overflow: hidden;
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

  .detail-location-badge {
    display: inline-block;
    background-color: #dc3545;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
  }

  .debug-info {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin: 10px 0;
    font-size: 12px;
    font-family: monospace;
  }
</style>

<div class="container-fluid" data-student-name="{{ $absensi->anakDidik->nama }}">
  <!-- Info Dasar -->
  <div class="row mb-3">
    <div class="col-md-6">
      <p><strong>Tanggal:</strong> {{ $absensi->tanggal->format('d M Y') }}</p>
    </div>
    <div class="col-md-6">
      <p><strong>Status Kehadiran:</strong>
        @if($absensi->status === 'hadir')
        <span class="badge bg-success">Hadir</span>
        @elseif($absensi->status === 'izin')
        <span class="badge bg-warning text-dark">Izin</span>
        @else
        <span class="badge bg-danger">Alfa</span>
        @endif
      </p>
    </div>
  </div>

  @if($absensi->keterangan)
  <div class="mb-3">
    <p><strong>Keterangan:</strong> {{ $absensi->keterangan }}</p>
  </div>
  @endif

  <!-- Kondisi Fisik - Hanya tampil jika bukan izin -->
  @if($absensi->status !== 'izin')
  <!-- Kondisi Fisik -->
  @if($absensi->kondisi_fisik === 'ada_tanda')
  <hr>
  <h6 class="mt-3 mb-3"><i class="ri-alert-line me-2"></i>Detail Tanda Fisik</h6>

  <div class="row mb-3">
    <div class="col-md-6">
      <p><strong>Jenis Tanda Fisik:</strong> <span class="badge bg-danger">{{ $absensi->jenis_tanda_fisik_label }}</span></p>
    </div>
    <div class="col-md-6">
      <p><strong>Waktu Foto Diambil:</strong> {{ $absensi->waktu_foto?->format('d M Y H:i:s') ?? '-' }}</p>
    </div>
  </div>

  <!-- Keterangan Tanda Fisik -->
  @if($absensi->keterangan_tanda_fisik)
  <div class="mb-3">
    <p><strong>Keterangan Tanda Fisik:</strong></p>
    <p class="text-muted">{{ $absensi->keterangan_tanda_fisik }}</p>
  </div>
  @endif

  <!-- 3D Body Model dengan Hotspots -->
  @if($absensi->lokasi_luka && count($absensi->lokasi_luka) > 0)
  <div class="mb-4">
    <h6 class="mb-2"><i class="ri-cube-3d-line me-2"></i>Model 3D Lokasi Tanda Fisik</h6>
    <p class="text-muted"><small>Titik merah menunjukkan lokasi tanda fisik yang ditandai</small></p>

    <div class="detail-3d-container" style="height: 400px; position: relative;">
      <model-viewer
        id="detailBodyModel3D"
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
        data-lokasi-luka="{{ json_encode($absensi->lokasi_luka) }}"
        style="width: 100%; height: 100%; display: block;">
      </model-viewer>
    </div>
    <p class="text-muted text-center mt-2"><small><i class="ri-hand-index-finger me-1"></i>Klik & geser untuk memutar 360° • Scroll untuk zoom</small></p>
  </div>

  <!-- Lokasi Tanda Fisik -->
  <div class="mb-3">
    <p><strong>Lokasi Tanda Fisik yang Ditandai:</strong></p>
    <div>
      @foreach($absensi->lokasi_luka as $lokasi)
      <span class="detail-location-badge">{{ $lokasi }}</span>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Foto Bukti -->
  @if($absensi->foto_bukti && count((array)$absensi->foto_bukti) > 0)
  <div class="mb-3">
    <p><strong>Foto Bukti ({{ count((array)$absensi->foto_bukti) }} foto):</strong></p>
    <div class="row g-2">
      @foreach((array)$absensi->foto_bukti as $foto)
      <div class="col-md-4" data-foto-path="{{ $foto }}">
        <img src="{{ asset('storage/' . $foto) }}" alt="Foto Bukti" class="img-thumbnail w-100" style="max-height: 300px; object-fit: cover; border-radius: 8px;">
      </div>
      @endforeach
    </div>
  </div>
  @else
  <div class="alert alert-warning mb-3">
    <i class="ri-alert-line me-2"></i>Tidak ada foto bukti tersimpan
  </div>
  @endif

  @endif

  <!-- Nama Pengantar -->
  @if($absensi->nama_pengantar)
  <div class="mb-3">
    <p><strong>Nama Orang Tua / Pengantar:</strong> {{ $absensi->nama_pengantar }}</p>
  </div>
  @endif

  <!-- Tanda Tangan -->
  @if($absensi->signature_pengantar)
  <div class="mb-3">
    <p><strong>Tanda Tangan Orang Tua / Pengantar:</strong></p>
    <img src="{{ asset('storage/' . $absensi->signature_pengantar) }}" alt="Tanda Tangan" style="max-width: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
  </div>
  @endif

  @if($absensi->kondisi_fisik === 'baik')
  <div class="alert alert-success mb-3">
    <i class="ri-check-line me-2"></i>Kondisi fisik anak didik baik, tidak ada tanda luka atau lebam.
  </div>
  @endif
  @endif

  <!-- Data Penjemputan -->
  @if($absensi->waktu_jemput)
  <hr>
  <h6 class="mt-3 mb-3"><i class="ri-user-follow-line me-2"></i>Data Penjemputan</h6>

  <div class="row mb-3">
    <div class="col-md-6">
      <p><strong>Waktu Dijemput:</strong> {{ $absensi->waktu_jemput->format('d M Y H:i:s') }}</p>
    </div>
    <div class="col-md-6">
      <p><strong>Nama Penjemput:</strong> {{ $absensi->nama_penjemput }}</p>
    </div>
  </div>

  @if($absensi->keterangan_penjemput)
  <div class="mb-3">
    <p><strong>Keterangan Penjemputan:</strong></p>
    <p class="text-muted">{{ $absensi->keterangan_penjemput }}</p>
  </div>
  @endif

  <!-- Foto Penjemput -->
  @if($absensi->foto_penjemput && count((array)$absensi->foto_penjemput) > 0)
  <div class="mb-3">
    <p><strong>Foto Penjemput ({{ count((array)$absensi->foto_penjemput) }} foto):</strong></p>
    <div class="row g-2">
      @foreach((array)$absensi->foto_penjemput as $foto)
      <div class="col-md-4">
        <img src="{{ asset('storage/' . $foto) }}" alt="Foto Penjemput" class="img-thumbnail w-100" style="max-height: 300px; object-fit: cover; border-radius: 8px;">
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Tanda Tangan Penjemput -->
  @if($absensi->signature_penjemput)
  <div class="mb-3">
    <p><strong>Tanda Tangan Penjemput:</strong></p>
    <img src="{{ asset('storage/' . $absensi->signature_penjemput) }}" alt="Tanda Tangan Penjemput" style="max-width: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
  </div>
  @endif
  @endif

  <!-- Guru yang mengabsensi -->
  <hr>
  <div class="mt-3">
    <p><strong>Diinput oleh:</strong> {{ $absensi->guru->name ?? '-' }}</p>
    <p><strong>Tanggal Input:</strong> {{ $absensi->created_at->format('d M Y H:i:s') }}</p>
  </div>
</div>