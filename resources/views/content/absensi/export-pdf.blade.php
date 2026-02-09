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
  $totalAbsensi = $absensis->count();
  $totalHadir = $absensis->where('status', 'hadir')->count();
  $totalIzin = $absensis->where('status', 'izin')->count();
  $totalAlfa = $absensis->where('status', 'alfa')->count();
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

  @if($absensis->count() > 0)
  <table>
    <thead>
      <tr>
        <th width="4%">No</th>
        <th width="12%">Tanggal</th>
        <th width="20%">Nama Anak Didik</th>
        <th width="10%">Status</th>
        <th width="10%">Keterangan</th>
        <th width="12%">Kondisi Fisik</th>
        <th width="15%">Lokasi Luka</th>
        <th width="17%">Diinput Oleh</th>
      </tr>
    </thead>
    <tbody>
      @foreach($absensis as $index => $absensi)
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
        <td><strong>{{ $absensi->anakDidik->nama ?? '-' }}</strong></td>
        <td>
          @if($absensi->status === 'hadir')
          <span class="badge badge-success">Hadir</span>
          @elseif($absensi->status === 'izin')
          <span class="badge badge-warning">Izin</span>
          @else
          <span class="badge badge-danger">Alfa</span>
          @endif
        </td>
        <td>{{ $absensi->keterangan ?? '-' }}</td>
        <td>
          @if($absensi->kondisi_fisik === 'sehat')
          <span class="badge badge-success">Sehat</span>
          @elseif($absensi->kondisi_fisik === 'sakit')
          <span class="badge badge-danger">Sakit</span>
          @else
          <span class="badge badge-info">Terluka</span>
          @endif
        </td>
        <td>
          @if(is_array($absensi->lokasi_luka) && count($absensi->lokasi_luka) > 0)
          {{ implode(', ', $absensi->lokasi_luka) }}
          @else
          -
          @endif
        </td>
        <td>{{ $absensi->guru->name ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">
    <p>📋 Tidak ada data absensi pada periode yang dipilih.</p>
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