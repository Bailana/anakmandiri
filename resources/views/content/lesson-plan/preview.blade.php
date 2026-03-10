<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lesson Plan - {{ $anakDidik ? $anakDidik->nama : 'Anak Didik' }}</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 11pt;
      color: #000;
      margin: 0;
      padding: 20px;
      line-height: 1.4;
    }

    .btn-print {
      position: fixed;
      top: 16px;
      right: 16px;
      border: none;
      background: #0d6efd;
      color: #fff;
      padding: 10px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 10pt;
      z-index: 1000;
    }

    .header {
      border-bottom: 2px solid #333;
      margin-bottom: 14px;
      padding-bottom: 10px;
      text-align: center;
    }

    .header h1 {
      margin: 0;
      font-size: 18pt;
    }

    .info-box {
      margin: 14px 0;
      border: 1px solid #d9d9d9;
      background: #f8f9fa;
      padding: 12px;
      border-radius: 6px;
    }

    .info-box p {
      margin: 4px 0;
    }

    .section-title {
      display: block;
      width: 100%;
      margin: 20px 0 8px;
      font-size: 11pt;
      font-weight: 700;
      padding: 8px 14px;
      border-radius: 4px;
      color: #fff;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .section-awal {
      background: #f97316;
      color: #fff !important;
    }

    .section-inti {
      background: #7c3aed;
    }

    .section-penutup {
      background: #198754;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      margin-bottom: 14px;
    }

    thead {
      background: #0d6efd;
      color: #fff;
    }

    th,
    td {
      border: 1px solid #dee2e6;
      padding: 8px;
      vertical-align: top;
      font-size: 9.5pt;
    }

    .text-center {
      text-align: center;
    }

    .badge {
      display: inline-block;
      border-radius: 4px;
      padding: 2px 7px;
      font-size: 8pt;
      font-weight: 600;
      color: #fff;
      white-space: nowrap;
    }

    .badge-primary {
      background: #0d6efd;
    }

    .badge-success {
      background: #198754;
    }

    .badge-warning {
      background: #ffc107;
      color: #000;
    }

    .badge-danger {
      background: #dc3545;
    }

    .badge-secondary {
      background: #6c757d;
    }

    .badge-info {
      background: #0dcaf0;
      color: #000;
    }

    .no-data {
      margin: 6px 0 14px;
      border: 1px dashed #bbb;
      padding: 10px;
      text-align: center;
      color: #666;
      border-radius: 4px;
    }

    .footer {
      margin-top: 18px;
      border-top: 1px solid #ccc;
      padding-top: 8px;
      text-align: center;
      color: #666;
      font-size: 9pt;
    }

    @media print {
      .btn-print {
        display: none;
      }

      body {
        padding: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      thead {
        display: table-header-group;
        background: #0d6efd !important;
        color: #fff !important;
      }

      .info-box {
        background: #f8f9fa !important;
      }

      .section-awal {
        background: #f97316 !important;
        color: #fff !important;
      }

      .section-inti {
        background: #7c3aed !important;
        color: #fff !important;
      }

      .section-penutup {
        background: #198754 !important;
        color: #fff !important;
      }

      .badge-primary {
        background: #0d6efd !important;
        color: #fff !important;
      }

      .badge-success {
        background: #198754 !important;
        color: #fff !important;
      }

      .badge-warning {
        background: #ffc107 !important;
        color: #000 !important;
      }

      .badge-danger {
        background: #dc3545 !important;
        color: #fff !important;
      }

      .badge-secondary {
        background: #6c757d !important;
        color: #fff !important;
      }

      .badge-info {
        background: #0dcaf0 !important;
        color: #000 !important;
      }

      @page {
        margin: 1.5cm;
      }

      tr {
        page-break-inside: avoid;
      }
    }
  </style>
</head>

<body>
  <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

  <div class="header">
    <h1>LESSON PLAN</h1>
  </div>

  <div class="info-box">
    <p><strong>Nama Anak Didik:</strong> {{ $anakDidik ? $anakDidik->nama : '-' }}</p>
    <p><strong>Guru Fokus:</strong> {{ ($anakDidik && $anakDidik->guruFokus) ? $anakDidik->guruFokus->nama : '-' }}</p>
    <p><strong>Bulan:</strong> {{ \Carbon\Carbon::parse($lp->tanggal)->locale('id')->translatedFormat('F Y') }}</p>
    @if($ppi)
    <p><strong>Periode PPI:</strong> {{ $periodeMulai }} s/d {{ $periodeSelesai }}</p>
    <p><strong>Keterangan PPI:</strong> {{ $ppi->keterangan ?? '-' }}</p>
    @endif
  </div>

  {{-- Program Aktif --}}
  <div style="margin:28px 0 8px;border-top:2px solid #333;padding-top:14px;">
    <h2 style="font-size:14pt;margin:0 0 10px;">Program Aktif Bulan Ini</h2>
  </div>

  @if(count($programData) > 0)
  <table>
    <thead>
      <tr>
        <th width="4%">No</th>
        <th width="10%">Kode</th>
        <th width="22%">Nama Program</th>
        <th width="12%">Kategori</th>
        <th width="17%">Keterangan</th>
        <th>Tujuan</th>
      </tr>
    </thead>
    <tbody>
      @foreach($programData as $i => $program)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td>{{ $program['kode_program'] ?? '-' }}</td>
        <td>
          <strong>{{ $program['nama_program'] ?? '-' }}</strong>
          @if(!empty($program['notes']))
          <div style="font-size:8.5pt;color:#666;margin-top:4px;">Catatan: {{ $program['notes'] }}</div>
          @endif
        </td>
        <td>
          @php
          $kategori = strtolower((string)($program['kategori'] ?? ''));
          $badgeClass = 'badge-secondary';
          $label = $program['kategori'] ?? '-';
          if ($kategori === 'akademik') { $badgeClass = 'badge-primary'; $label = 'Akademik'; }
          elseif ($kategori === 'bina diri') { $badgeClass = 'badge-success'; $label = 'Bina Diri'; }
          elseif ($kategori === 'motorik') { $badgeClass = 'badge-info'; $label = 'Motorik'; }
          elseif ($kategori === 'perilaku') { $badgeClass = 'badge-warning'; $label = 'Basic Learning'; }
          elseif ($kategori === 'vokasi') { $badgeClass = 'badge-secondary'; $label = 'Vokasi'; }
          @endphp
          <span class="badge {{ $badgeClass }}">{{ $label }}</span>
        </td>
        <td>{{ $program['keterangan'] ?? '-' }}</td>
        <td>{{ $program['tujuan'] ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">Tidak ada program aktif untuk bulan ini.</div>
  @endif

  @foreach(['awal' => 'Awal', 'inti' => 'Inti', 'penutup' => 'Penutup'] as $key => $label)
  <div class="section-title section-{{ $key }}">{{ $label }}</div>
  @if($schedulesBySection[$key]->count())
  <table>
    <thead>
      <tr>
        <th width="5%">No</th>
        <th width="18%">Waktu</th>
        <th width="28%">Program</th>
        <th>Keterangan / Aktivitas</th>
      </tr>
    </thead>
    <tbody>
      @foreach($schedulesBySection[$key] as $i => $row)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td>{{ \Carbon\Carbon::parse($row->jam_mulai)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($row->jam_selesai)->format('H:i') }}</td>
        <td>
          @php
          $programs = array_filter(array_map('trim', explode(',', $row->nama_program ?? '')));
          @endphp
          @forelse($programs as $prog)
          @php
          $cat = strtolower($programCategories[$prog] ?? '');
          $bc = 'badge-secondary';
          if ($cat === 'akademik') $bc = 'badge-primary';
          elseif ($cat === 'bina diri') $bc = 'badge-success';
          elseif ($cat === 'motorik') $bc = 'badge-info';
          elseif ($cat === 'perilaku') $bc = 'badge-warning';
          elseif ($cat === 'vokasi') $bc = 'badge-secondary';
          @endphp
          <span class="badge {{ $bc }}" style="display:block;margin-bottom:3px;white-space:normal;text-align:left;font-weight:normal;">{{ $prog }}</span>
          @empty
          <span class="text-muted">-</span>
          @endforelse
        </td>
        <td>{{ $row->keterangan ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">Tidak ada jadwal pada sesi ini.</div>
  @endif
  @endforeach

  <!-- Refleksi Diri -->
  <div class="section-title" style="margin-top:24px;background:#0f766e;color:#fff;">Refleksi Diri</div>
  <table>
    <tbody>
      <tr style="height:120px;">
        <td style="width:100%;"></td>
      </tr>
    </tbody>
  </table>

  <div class="footer">
    <div>Dokumen ini dibuat pada {{ now()->locale('id')->translatedFormat('d F Y, H:i') }}</div>
    <div style="margin-top:4px;">&copy; {{ now()->year }} R&amp;B Dev. All Rights Reserved.</div>
  </div>
</body>

</html>