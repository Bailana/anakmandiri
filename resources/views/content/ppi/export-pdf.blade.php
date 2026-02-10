<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan PPI - {{ $anakDidik->nama }}</title>
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

    .badge-primary {
      background-color: #007bff;
      color: white;
    }

    .badge-secondary {
      background-color: #6c757d;
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
  <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

  <div class="header">
    <h1>LAPORAN PROGRAM PEMBELAJARAN INDIVIDUAL (PPI)</h1>
    <p>Periode: {{ $periodeBulan }}</p>
    <p>Dicetak pada: {{ $tanggalCetak }}</p>
  </div>

  <div class="info-box">
    <p><strong>Nama Anak Didik:</strong> {{ $anakDidik->nama }}</p>
    <p><strong>NIS:</strong> {{ $anakDidik->nis ?? '-' }}</p>
    <p><strong>Guru Fokus:</strong> {{ $anakDidik->guruFokus ? $anakDidik->guruFokus->nama : '-' }}</p>
  </div>


  @if(count($programData) > 0)
  <div style="margin-top: 20px; margin-bottom: 10px;">
    <h3 style="font-size: 14pt; color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 8px;">📝 Detail Program</h3>
  </div>

  <table>
    <thead>
      <tr>
        <th width="5%">No</th>
        <th width="20%">Nama Program</th>
        <th width="10%">Kategori</th>
        <th width="12%">Konsultan</th>
        <th width="18%">Deskripsi</th>
        <th width="15%">Tujuan</th>
        <th width="12%">Metode</th>
      </tr>
    </thead>
    <tbody>
      @foreach($programData as $index => $program)
      <tr>
        <td class="text-center">{{ $index + 1 }}</td>
        <td><strong>{{ $program['nama_program'] }}</strong></td>
        <td>
          @php
          $kategori = $program['kategori'];
          $badgeClass = 'badge-secondary';
          $kategoriLabel = ucfirst(str_replace('_', ' ', $kategori));

          switch(strtolower($kategori)) {
          case 'bina_diri':
          $badgeClass = 'badge-success';
          $kategoriLabel = 'Bina Diri';
          break;
          case 'akademik':
          $badgeClass = 'badge-primary';
          $kategoriLabel = 'Akademik';
          break;
          case 'motorik':
          $badgeClass = 'badge-info';
          $kategoriLabel = 'Motorik';
          break;
          case 'perilaku':
          $badgeClass = 'badge-warning';
          $kategoriLabel = 'Basic Learning';
          break;
          case 'vokasi':
          $badgeClass = 'badge-secondary';
          $kategoriLabel = 'Vokasi';
          break;
          }
          @endphp
          <span class="badge {{ $badgeClass }}">{{ $kategoriLabel }}</span>
        </td>
        <td>
          <strong>{{ $program['konsultan_nama'] }}</strong><br>
          <small style="color: #666;">{{ $program['konsultan_spesialisasi'] }}</small>
        </td>
        <td>{{ $program['deskripsi'] }}</td>
        <td>{{ $program['tujuan'] }}</td>
        <td>{{ $program['metode'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">
    <p>📋 Tidak ada program untuk periode ini</p>
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