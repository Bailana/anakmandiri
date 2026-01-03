<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $anakDidik->nama }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Arial', sans-serif;
      color: #333;
      line-height: 1.6;
      padding: 20px;
      background-color: #fff;
    }

    .print-instructions {
      background-color: #fef3c7;
      border: 2px solid #f59e0b;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      text-align: center;
      font-size: 14px;
    }

    .print-instructions strong {
      display: block;
      margin-bottom: 5px;
    }

    /* Full-width kop surat header */
    .kop-full {
      width: 100%;
      margin-bottom: 12px;
    }

    .kop-full img {
      width: 100%;
      height: auto;
      display: block;
    }

    .header {
      margin-top: 10px;
      margin-bottom: 18px;
    }

    .header h1 {
      font-size: 20px;
      margin-bottom: 6px;
      color: #1e3a8a;
    }

    .header p {
      font-size: 12px;
      color: #666;
      margin: 0;
    }

    .section {
      margin-bottom: 25px;
      page-break-inside: avoid;
    }

    .section h2 {
      font-size: 16px;
      color: #1e3a8a;
      background-color: #e0e7ff;
      padding: 10px;
      margin-bottom: 15px;
      border-left: 4px solid #1e3a8a;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      margin-bottom: 15px;
    }

    .info-item {
      padding: 10px;
      background-color: #f5f5f5;
      border-left: 3px solid #3b82f6;
    }

    .info-label {
      font-weight: bold;
      color: #1e3a8a;
      font-size: 12px;
      margin-bottom: 3px;
    }

    .info-value {
      color: #333;
      font-size: 13px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
      font-size: 12px;
    }

    table th {
      background-color: #1e3a8a;
      color: white;
      padding: 10px;
      text-align: left;
      font-weight: bold;
    }

    table td {
      padding: 8px 10px;
      border-bottom: 1px solid #ddd;
    }

    table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: bold;
      color: white;
    }

    .badge-primary {
      background-color: #3b82f6;
    }

    .badge-success {
      background-color: #10b981;
    }

    .badge-warning {
      background-color: #f59e0b;
    }

    .badge-danger {
      background-color: #ef4444;
    }

    .badge-info {
      background-color: #06b6d4;
    }

    .empty-message {
      text-align: center;
      padding: 20px;
      color: #999;
      font-style: italic;
      background-color: #f9f9f9;
      border-radius: 4px;
    }

    .checklist {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .checklist-item {
      display: flex;
      align-items: center;
      padding: 8px;
      font-size: 12px;
    }

    .checklist-check {
      width: 16px;
      height: 16px;
      border: 1px solid #ccc;
      margin-right: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #fff;
    }

    .checklist-check.checked {
      background-color: #10b981;
      color: white;
      border-color: #10b981;
    }

    .therapy-badge {
      display: inline-block;
      padding: 6px 12px;
      margin: 4px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      color: white;
    }

    .therapy-si {
      background-color: #3b82f6;
    }

    .therapy-wicara {
      background-color: #10b981;
    }

    .therapy-perilaku {
      background-color: #f59e0b;
    }

    .footer {
      margin-top: 30px;
      text-align: center;
      font-size: 11px;
      color: #999;
      border-top: 1px solid #ddd;
      padding-top: 15px;
    }

    @media print {
      .print-instructions {
        display: none;
      }

      body {
        margin: 0;
        padding: 0;
        background-color: white;
      }

      .header {
        page-break-after: avoid;
      }

      .section {
        page-break-inside: avoid;
      }

      a {
        color: black;
        text-decoration: none;
      }
    }
  </style>
</head>

<body>
  <!-- Print Instructions -->
  <div class="print-instructions">
    <strong><i class="ri-printer-line"></i> Cara Menggunakan:</strong>
    Tekan <strong>Ctrl+P</strong> (Windows) atau <strong>Cmd+P</strong> (Mac) untuk menyimpan laporan sebagai PDF
  </div>

  <!-- Full-width kop surat header -->
  <div class="kop-full">
    <img src="{{ asset('assets/img/kop_surat.png') }}" alt="Kop Surat">
  </div>

  <div class="header">
    <h1>Data Diri Anak Didik</h1>
    <p>Tanggal Laporan: {{ now()->format('d F Y') }}</p>
  </div>

  <!-- Data Diri Section -->
  <div class="section">
    <h2>Data Diri Anak</h2>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Nama Lengkap</div>
        <div class="info-value">{{ $anakDidik->nama }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Nomor Induk Siswa (NIS)</div>
        <div class="info-value">{{ $anakDidik->nis ?: '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Jenis Kelamin</div>
        <div class="info-value">{{ ucfirst($anakDidik->jenis_kelamin) }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Tanggal Lahir</div>
        <div class="info-value">{{ $anakDidik->tanggal_lahir ? $anakDidik->tanggal_lahir->format('d F Y') : '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Tempat Lahir</div>
        <div class="info-value">{{ $anakDidik->tempat_lahir ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Alamat</div>
        <div class="info-value">{{ $anakDidik->alamat ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Email</div>
        <div class="info-value">{{ $anakDidik->email ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Nomor Telepon</div>
        <div class="info-value">{{ $anakDidik->no_telepon ?? '-' }}</div>
      </div>
    </div>
  </div>

  <!-- Data Keluarga -->
  <div class="section">
    <h2>Data Keluarga</h2>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Nama Orang Tua / Wali</div>
        <div class="info-value">{{ $anakDidik->nama_orang_tua ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">No. Telepon Orang Tua</div>
        <div class="info-value">{{ $anakDidik->no_telepon_orang_tua ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Nomor Kartu Keluarga (KK)</div>
        <div class="info-value">{{ $anakDidik->no_kk ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">NIK</div>
        <div class="info-value">{{ $anakDidik->nik ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Tinggal Bersama</div>
        <div class="info-value">{{ $anakDidik->tinggal_bersama ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Jumlah Saudara Kandung</div>
        <div class="info-value">{{ $anakDidik->jumlah_saudara_kandung ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Anak Ke</div>
        <div class="info-value">{{ $anakDidik->anak_ke ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">No. Akta Kelahiran</div>
        <div class="info-value">{{ $anakDidik->no_akta_kelahiran ?? '-' }}</div>
      </div>
    </div>
  </div>

  <!-- Data Kesehatan -->
  <div class="section">
    <h2>Data Kesehatan</h2>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Tinggi Badan</div>
        <div class="info-value">{{ $anakDidik->tinggi_badan ? $anakDidik->tinggi_badan . ' cm' : '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Berat Badan</div>
        <div class="info-value">{{ $anakDidik->berat_badan ? $anakDidik->berat_badan . ' kg' : '-' }}</div>
      </div>
    </div>
  </div>

  <!-- Data Pendidikan -->
  <div class="section">
    <h2>Data Pendidikan</h2>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Pendidikan Terakhir</div>
        <div class="info-value">{{ $anakDidik->pendidikan_terakhir ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Asal Sekolah</div>
        <div class="info-value">{{ $anakDidik->asal_sekolah ?? '-' }}</div>
      </div>
      <div class="info-item">
        <div class="info-label">Tanggal Pendaftaran</div>
        <div class="info-value">{{ $anakDidik->tanggal_pendaftaran ? $anakDidik->tanggal_pendaftaran->format('d F Y') : '-' }}</div>
      </div>
    </div>
  </div>

  <!-- Kelengkapan Dokumen -->
  <div class="section">
    <h2>Kelengkapan Dokumen Registrasi</h2>
    <div class="checklist">
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->kk ? 'checked' : '' }}">{{ $anakDidik->kk ? '✓' : '' }}</div>
        <span>Kartu Keluarga (KK)</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->ktp_orang_tua ? 'checked' : '' }}">{{ $anakDidik->ktp_orang_tua ? '✓' : '' }}</div>
        <span>KTP Orang Tua</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->akta_kelahiran ? 'checked' : '' }}">{{ $anakDidik->akta_kelahiran ? '✓' : '' }}</div>
        <span>Akta Kelahiran</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->foto_anak ? 'checked' : '' }}">{{ $anakDidik->foto_anak ? '✓' : '' }}</div>
        <span>Foto Anak</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->pemeriksaan_tes_rambut ? 'checked' : '' }}">{{ $anakDidik->pemeriksaan_tes_rambut ? '✓' : '' }}</div>
        <span>Pemeriksaan Tes Rambut</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->anamnesa ? 'checked' : '' }}">{{ $anakDidik->anamnesa ? '✓' : '' }}</div>
        <span>Anamnesa</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->tes_iq ? 'checked' : '' }}">{{ $anakDidik->tes_iq ? '✓' : '' }}</div>
        <span>Tes IQ</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->pemeriksaan_dokter_lab ? 'checked' : '' }}">{{ $anakDidik->pemeriksaan_dokter_lab ? '✓' : '' }}</div>
        <span>Pemeriksaan Dokter / Lab</span>
      </div>
      <div class="checklist-item">
        <div class="checklist-check {{ $anakDidik->surat_pernyataan ? 'checked' : '' }}">{{ $anakDidik->surat_pernyataan ? '✓' : '' }}</div>
        <span>Surat Pernyataan</span>
      </div>
    </div>
  </div>

  <!-- Program Terapi -->
  @if($anakDidik->therapyPrograms && $anakDidik->therapyPrograms->count() > 0)
  <div class="section">
    <h2>Program Terapi yang Diikuti</h2>
    <div style="margin-bottom: 15px;">
      @foreach($anakDidik->therapyPrograms as $therapy)
      @if($therapy->type_therapy === 'si')
      <span class="therapy-badge therapy-si">Sensori Integrasi</span>
      @elseif($therapy->type_therapy === 'wicara')
      <span class="therapy-badge therapy-wicara">Terapi Wicara</span>
      @elseif($therapy->type_therapy === 'perilaku')
      <span class="therapy-badge therapy-perilaku">Terapi Perilaku</span>
      @endif
      @endforeach
    </div>

    <table>
      <thead>
        <tr>
          <th>Jenis Terapi</th>
          <th>Tanggal Mulai</th>
          <th>Tanggal Selesai</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($anakDidik->therapyPrograms as $therapy)
        <tr>
          <td>
            @if($therapy->type_therapy === 'si')
            <span class="badge badge-primary">Sensori Integrasi</span>
            @elseif($therapy->type_therapy === 'wicara')
            <span class="badge badge-success">Terapi Wicara</span>
            @elseif($therapy->type_therapy === 'perilaku')
            <span class="badge badge-warning">Terapi Perilaku</span>
            @endif
          </td>
          <td>{{ $therapy->tanggal_mulai ? $therapy->tanggal_mulai->format('d/m/Y') : '-' }}</td>
          <td>{{ $therapy->tanggal_selesai ? $therapy->tanggal_selesai->format('d/m/Y') : '-' }}</td>
          <td>
            @if($therapy->is_active)
            <span class="badge badge-success">Aktif</span>
            @else
            <span class="badge badge-danger">Tidak Aktif</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <!-- Penilaian -->
  @if($anakDidik->assessments && $anakDidik->assessments->count() > 0)
  <div class="section">
    <h2>Riwayat Penilaian</h2>
    <table>
      <thead>
        <tr>
          <th>Kategori</th>
          <th>Tanggal</th>
          <th>Konsultan</th>
          <th>Hasil</th>
        </tr>
      </thead>
      <tbody>
        @foreach($anakDidik->assessments as $assessment)
        <tr>
          <td>
            @php
            $colors = ['bina_diri' => 'primary', 'akademik' => 'info', 'motorik' => 'success', 'perilaku' => 'warning', 'vokasi' => 'danger'];
            $labels = ['bina_diri' => 'Bina Diri', 'akademik' => 'Akademik', 'motorik' => 'Motorik', 'perilaku' => 'Perilaku', 'vokasi' => 'Vokasi'];
            @endphp
            <span class="badge badge-{{ $colors[$assessment->kategori] ?? 'primary' }}">
              {{ $labels[$assessment->kategori] ?? $assessment->kategori }}
            </span>
          </td>
          <td>{{ $assessment->tanggal_assessment ? $assessment->tanggal_assessment->format('d/m/Y') : '-' }}</td>
          <td>{{ $assessment->konsultan->nama ?? '-' }}</td>
          <td>{{ $assessment->hasil_penilaian ? substr($assessment->hasil_penilaian, 0, 50) . '...' : '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif


  <div class="footer">
    <p>Dokumen ini adalah data resmi anak didik yang dihasilkan otomatis oleh sistem.</p>
    <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
  </div>
</body>

</html>