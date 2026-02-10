<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Absensi Anak Didik - {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 11pt;
      line-height: 1.4;
      color: #000;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #333;
      padding-bottom: 15px;
    }

    .header h1 {
      font-size: 20pt;
      font-weight: bold;
      margin-bottom: 5px;
      color: #000;
    }

    .header p {
      font-size: 11pt;
      color: #555;
      margin: 5px 0;
    }

    .info-box {
      background: #f5f5f5;
      padding: 12px 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border-left: 4px solid #007bff;
    }

    .info-box p {
      margin: 3px 0;
      font-size: 10pt;
    }

    .info-box strong {
      color: #000;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background: white;
    }

    thead {
      background: #007bff;
      color: white;
    }

    th {
      padding: 10px 8px;
      text-align: left;
      font-weight: 600;
      border: 1px solid #dee2e6;
      font-size: 10pt;
    }

    td {
      padding: 8px;
      border: 1px solid #dee2e6;
      font-size: 9pt;
      vertical-align: top;
      color: #000;
    }

    tbody tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    tbody tr:hover {
      background-color: #e9ecef;
    }

    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 8pt;
      font-weight: 600;
      text-align: center;
      white-space: nowrap;
    }

    .badge-success {
      background-color: #28a745;
      color: white;
    }

    .badge-warning {
      background-color: #ffc107;
      color: #000;
    }

    .badge-danger {
      background-color: #dc3545;
      color: white;
    }

    .badge-info {
      background-color: #17a2b8;
      color: white;
    }

    .text-center {
      text-align: center;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #333;
      font-size: 9pt;
      color: #666;
      text-align: center;
    }

    .summary-box {
      background: #e7f3ff;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #007bff;
    }

    .summary-box h3 {
      font-size: 12pt;
      margin-bottom: 10px;
      color: #007bff;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }

    .summary-item {
      text-align: center;
      padding: 8px;
      background: white;
      border-radius: 4px;
      border: 1px solid #dee2e6;
    }

    .summary-item .value {
      font-size: 18pt;
      font-weight: bold;
      color: #007bff;
    }

    .summary-item .label {
      font-size: 9pt;
      color: #666;
      margin-top: 3px;
    }

    .btn-print {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #007bff;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 11pt;
      font-weight: 600;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .btn-print:hover {
      background: #0056b3;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #999;
      font-style: italic;
    }

    @media print {
      body {
        padding: 0;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .btn-print {
        display: none;
      }

      table {
        page-break-inside: auto;
      }

      td {
        color: #000 !important;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      thead {
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
      }

      .summary-box {
        page-break-after: avoid;
      }

      @page {
        margin: 1.5cm;
        size: auto;
      }
    }

    /* Hide browser default header/footer (URL, date, page numbers) */
    @page {
      margin: 1.5cm;
      size: auto;
    }
  </style>
</head>

<body>
  <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

  <div class="header">
    <h1>LAPORAN ABSENSI ANAK DIDIK</h1>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    <p>Dicetak pada: {{ now()->format('d M Y H:i') }} WIB</p>
  </div>

  @php
  $totalAbsensi = collect($completeData)->filter(function($item) {
  return $item['absensi'] !== null;
  })->count();
  $totalHadir = collect($completeData)->filter(function($item) {
  return $item['absensi'] && $item['absensi']->status === 'hadir';
  })->count();
  $totalIzin = collect($completeData)->filter(function($item) {
  return $item['absensi'] && $item['absensi']->status === 'izin';
  })->count();
  $totalAlfa = collect($completeData)->filter(function($item) {
  return $item['absensi'] && $item['absensi']->status === 'alfa';
  })->count();
  @endphp

  <div class="summary-box">
    <h3>📊 Ringkasan Data</h3>
    <div class="summary-grid">
      <div class="summary-item">
        <div class="value">{{ $totalAbsensi }}</div>
        <div class="label">Total Absensi</div>
      </div>
      <div class="summary-item">
        <div class="value" style="color: #28a745;">{{ $totalHadir }}</div>
        <div class="label">Hadir</div>
      </div>
      <div class="summary-item">
        <div class="value" style="color: #ffc107;">{{ $totalIzin }}</div>
        <div class="label">Izin</div>
      </div>
      <div class="summary-item">
        <div class="value" style="color: #dc3545;">{{ $totalAlfa }}</div>
        <div class="label">Alfa</div>
      </div>
    </div>
  </div>

  <div class="summary-box" style="background: #fff3cd; border-color: #ffc107;">
    <h3 style="color: #856404;">📋 Ringkasan Data Per Anak Didik</h3>
    <table style="margin-bottom: 0;">
      <thead style="background: #ffc107; color: #000;">
        <tr>
          <th width="5%">No</th>
          <th width="45%">Nama Anak Didik</th>
          <th width="12%" class="text-center">Hadir</th>
          <th width="12%" class="text-center">Izin</th>
          <th width="12%" class="text-center">Alfa</th>
          <th width="14%" class="text-center">Total Absensi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($summaryPerAnak as $index => $summary)
        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          <td><strong>{{ $summary['anak_didik']->nama }}</strong></td>
          <td class="text-center">
            <span class="badge badge-success">{{ $summary['hadir'] }}</span>
          </td>
          <td class="text-center">
            <span class="badge badge-warning">{{ $summary['izin'] }}</span>
          </td>
          <td class="text-center">
            <span class="badge badge-danger">{{ $summary['alfa'] }}</span>
          </td>
          <td class="text-center"><strong>{{ $summary['total'] }}</strong></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if(count($completeData) > 0)
  <div style="margin-top: 20px; margin-bottom: 10px;">
    <h3 style="font-size: 14pt; color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 8px;">📝 Detail Absensi</h3>
  </div>

  @php
  // Group data by tanggal
  $groupedByDate = collect($completeData)->groupBy(function($item) {
  return $item['tanggal']->format('Y-m-d');
  });
  @endphp

  @foreach($groupedByDate as $dateKey => $dataForDate)
  @php
  $tanggalDisplay = \Carbon\Carbon::parse($dateKey);
  @endphp

  <div style="margin-top: 15px; margin-bottom: 8px;">
    <h4 style="font-size: 11pt; color: #495057; background-color: #e9ecef; padding: 6px 10px; border-left: 4px solid #007bff;">
      📅 {{ $tanggalDisplay->locale('id')->isoFormat('dddd, D MMMM Y') }}
    </h4>
  </div>

  <table>
    <thead>
      <tr>
        <th width="6%">No</th>
        <th width="22%">Nama Anak Didik</th>
        <th width="7%" class="text-center">Hadir</th>
        <th width="7%" class="text-center">Izin</th>
        <th width="7%" class="text-center">Alfa</th>
        <th width="13%">Keterangan</th>
        <th width="12%">Kondisi Fisik</th>
        <th width="13%">Lokasi Luka</th>
        <th width="13%">Diinput Oleh</th>
      </tr>
    </thead>
    <tbody>
      @foreach($dataForDate as $index => $data)
      @php
      $absensi = $data['absensi'];
      $anakDidik = $data['anak_didik'];
      @endphp
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td><strong>{{ $anakDidik->nama ?? '-' }}</strong></td>
        <td class="text-center">
          @if($absensi && $absensi->status === 'hadir')
          ✓
          @else
          -
          @endif
        </td>
        <td class="text-center">
          @if($absensi && $absensi->status === 'izin')
          ✓
          @else
          -
          @endif
        </td>
        <td class="text-center">
          @if($absensi && $absensi->status === 'alfa')
          ✓
          @else
          -
          @endif
        </td>
        <td>
          @if($absensi && $absensi->status === 'izin' && $absensi->keterangan)
          {{ $absensi->keterangan }}
          @else
          -
          @endif
        </td>
        <td>
          @if($absensi && $absensi->status !== 'izin')
          @if($absensi->kondisi_fisik === 'baik')
          <span class="badge badge-success">Baik</span>
          @elseif($absensi->kondisi_fisik === 'ada_tanda')
          <span class="badge badge-danger">{{ $absensi->jenis_tanda_fisik_label }}</span>
          @else
          -
          @endif
          @else
          -
          @endif
        </td>
        <td>
          @if($absensi && is_array($absensi->lokasi_luka) && count($absensi->lokasi_luka) > 0)
          {{ implode(', ', $absensi->lokasi_luka) }}
          @else
          -
          @endif
        </td>
        <td>{{ $absensi && $absensi->guru ? $absensi->guru->name : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @endforeach
  @else
  <div class="no-data">
    <p>📋 Tidak ada data anak didik dengan guru fokus yang ditugaskan.</p>
  </div>
  @endif

  <div class="footer">
    <p>Dokumen ini dicetak secara otomatis oleh sistem R&B Dev.</p>
    <p>© {{ now()->year }} R&B Dev. All Rights Reserved.</p>
  </div>

  <script>
    // Auto-print dialog can be triggered here if needed
    // window.onload = function() { window.print(); };
  </script>
</body>

</html>