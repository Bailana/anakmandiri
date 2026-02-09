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

<!-- DEBUG INFO -->
<div class="debug-info">
  <strong>DEBUG - Absensi Data:</strong>
  <br>ID: {{ $absensi->id }}
  <br>Kondisi Fisik: {{ $absensi->kondisi_fisik }}
  <br>Jenis Tanda Fisik: {{ $absensi->jenis_tanda_fisik }}
  <br>Lokasi Luka: {{ json_encode($absensi->lokasi_luka) }}
  <br>Foto Bukti Path: {{ $absensi->foto_bukti }}
  <br>Anak Didik Jenis Kelamin: {{ $absensi->anakDidik->jenis_kelamin }}
</div>

<div class="container-fluid">
  <h6 class="mb-3">{{ $absensi->anakDidik->nama }}</h6>

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
    
    <div class="detail-3d-container" style="height: 400px; position: relative;" id="modelContainer3DDiv">
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
  @if($absensi->foto_bukti)
  <div class="mb-3">
    <p><strong>Foto Bukti:</strong></p>
    <img src="{{ asset('storage/' . $absensi->foto_bukti) }}" alt="Foto Bukti" style="max-width: 100%; max-height: 400px; border-radius: 8px;">
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

  <!-- Guru yang mengabsensi -->
  <hr>
  <div class="mt-3">
    <p><strong>Diinput oleh:</strong> {{ $absensi->guru->name ?? '-' }}</p>
    <p><strong>Tanggal Input:</strong> {{ $absensi->created_at->format('d M Y H:i:s') }}</p>
  </div>
</div>

<script>
  // Ensure model-viewer is loaded
  function ensureModelViewerLoaded() {
    return new Promise(resolve => {
      if (typeof customElements !== 'undefined' && customElements.get('model-viewer')) {
        console.log('✓ model-viewer already loaded');
        resolve();
      } else {
        console.log('Loading model-viewer library...');
        const script = document.createElement('script');
        script.type = 'module';
        script.src = 'https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js';
        script.onload = () => {
          console.log('✓ model-viewer library loaded');
          // Give it a moment to register
          setTimeout(resolve, 100);
        };
        script.onerror = () => {
          console.error('✗ Failed to load model-viewer library');
          resolve();
        };
        document.head.appendChild(script);
      }
    });
  }

  // 3D coordinates for body parts (matching the create form)
  const bodyPartCoordinates = {
    'Kepala': { position: '0.00 1.80 0.00', normal: '0 1 0' },
    'Wajah': { position: '0.00 1.70 0.10', normal: '0 1 0' },
    'Telinga Kiri': { position: '0.10 1.70 -0.01', normal: '0 1 0' },
    'Telinga Kanan': { position: '-0.10 1.70 -0.01', normal: '0 1 0' },
    'Leher': { position: '0.00 1.56 0.00', normal: '0 1 0' },
    'Dada': { position: '0.00 1.40 0.10', normal: '0 1 0' },
    'Perut': { position: '0.00 1.15 0.12', normal: '0 1 0' },
    'Punggung Atas': { position: '0.00 1.40 -0.16', normal: '0 1 0' },
    'Punggung Bawah': { position: '0.00 1.15 -0.10', normal: '0 1 0' },
    'Pinggang': { position: '0.15 1.05 0.00', normal: '0 1 0' },
    'Bahu Kiri': { position: '0.18 1.48 -0.07', normal: '0 1 0' },
    'Lengan Atas Kiri': { position: '0.34 1.40 -0.07', normal: '0 1 0' },
    'Siku Kiri': { position: '0.45 1.35 -0.10', normal: '0 1 0' },
    'Lengan Bawah Kiri': { position: '0.55 1.30 -0.07', normal: '0 1 0' },
    'Pergelangan Tangan Kiri': { position: '0.68 1.22 -0.07', normal: '0 1 0' },
    'Jari Tangan Kiri': { position: '0.82 1.12 -0.07', normal: '0 1 0' },
    'Bahu Kanan': { position: '-0.18 1.48 -0.07', normal: '0 1 0' },
    'Lengan Atas Kanan': { position: '-0.34 1.40 -0.07', normal: '0 1 0' },
    'Siku Kanan': { position: '-0.45 1.35 -0.10', normal: '0 1 0' },
    'Lengan Bawah Kanan': { position: '-0.55 1.30 -0.07', normal: '0 1 0' },
    'Pergelangan Tangan Kanan': { position: '-0.68 1.22 -0.07', normal: '0 1 0' },
    'Jari Tangan Kanan': { position: '-0.82 1.12 -0.07', normal: '0 1 0' },
    'Paha Kiri': { position: '-0.12 0.7 0.05', normal: '0 0 1' },
    'Lutut Kiri': { position: '-0.12 0.5 0.08', normal: '0 0 1' },
    'Betis Kiri': { position: '-0.12 0.3 0.06', normal: '0 0 1' },
    'Pergelangan Kaki Kiri': { position: '-0.12 0.1 0.05', normal: '0 0 1' },
    'Jari Kaki Kiri': { position: '-0.12 0.02 0.15', normal: '0 0 1' },
    'Paha Kanan': { position: '0.12 0.7 0.05', normal: '0 0 1' },
    'Lutut Kanan': { position: '0.12 0.5 0.08', normal: '0 0 1' },
    'Betis Kanan': { position: '0.12 0.3 0.06', normal: '0 0 1' },
    'Pergelangan Kaki Kanan': { position: '0.12 0.1 0.05', normal: '0 0 1' },
    'Jari Kaki Kanan': { position: '0.12 0.02 0.15', normal: '0 0 1' }
  };

  // Initialize 3D model
  window.initializeDetail3DModel = async function() {
    console.log('=== initializeDetail3DModel started ===');
    
    const bodyModel3D = document.getElementById('detailBodyModel3D');
    if (!bodyModel3D) {
      console.error('✗ detailBodyModel3D element not found');
      return;
    }
    
    console.log('✓ Found model-viewer element');

    // Ensure model-viewer library is loaded
    await ensureModelViewerLoaded();

    // Data dari server (embedded in HTML)
    const anakDidikJenisKelamin = '{{ $absensi->anakDidik->jenis_kelamin ?? '' }}'.trim();
    const markedLocations = @json($absensi->lokasi_luka ?? []);

    console.log('Jenis Kelamin:', anakDidikJenisKelamin);
    console.log('Marked Locations:', markedLocations);

    // Set model path based on jenis_kelamin
    let modelPath = '{{ asset('assets/Male.glb') }}';
    
    const jenisKelamin = (anakDidikJenisKelamin || '').toLowerCase();
    if (jenisKelamin === 'perempuan' || jenisKelamin === 'p') {
      modelPath = '{{ asset('assets/Female.glb') }}';
      console.log('→ Using Female model');
    } else if (jenisKelamin === 'laki-laki' || jenisKelamin === 'l') {
      modelPath = '{{ asset('assets/Male.glb') }}';
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
      markedLocations.forEach((location, index) => {
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

      console.log(`✓ Total ${markedLocations.length} hotspots loaded`);
    }

    // Add hotspots when model loads
    bodyModel3D.addEventListener('load', function() {
      console.log('✓ Model loaded event fired');
      addHotspots();
    });
    
    // Try to add hotspots immediately (for cached models)
    if (bodyModel3D.src && bodyModel3D.src !== '') {
      setTimeout(addHotspots, 100);
    }

    // Handle errors
    bodyModel3D.addEventListener('error', function(event) {
      console.error('✗ Model loading error:', event);
    });

    console.log('=== initializeDetail3DModel completed ===');
  };

  // Try to initialize immediately
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      console.log('DOMContentLoaded fired, initializing...');
      initializeDetail3DModel();
    });
  } else {
    console.log('Document already loaded, initializing immediately...');
    initializeDetail3DModel();
  }
</script>