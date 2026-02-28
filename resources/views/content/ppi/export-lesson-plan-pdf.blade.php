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

    .meta {
      margin-top: 6px;
      color: #444;
      font-size: 10pt;
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
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
      margin-top: 14px;
      border: 1px dashed #bbb;
      padding: 16px;
      text-align: center;
      color: #666;
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
    <p><strong>NIS:</strong> {{ $anakDidik && $anakDidik->nis ? $anakDidik->nis : '-' }}</p>
    <p><strong>Guru Fokus:</strong> {{ ($anakDidik && $anakDidik->guruFokus) ? $anakDidik->guruFokus->nama : '-' }}</p>
    <p><strong>Periode PPI:</strong> {{ $periodeMulai }} s/d {{ $periodeSelesai }}</p>
    <p><strong>Keterangan PPI:</strong> {{ $ppi->keterangan ?? '-' }}</p>
  </div>

  @if(count($programData) > 0)
  <table>
    <thead>
      <tr>
        <th width="4%">No</th>
        <th width="12%">Kode</th>
        <th width="18%">Nama Program</th>
        <th width="10%">Kategori</th>
        <th width="14%">Konsultan</th>
        <th width="14%">Keterangan Program</th>
        <th width="14%">Tujuan</th>
        <th width="14%">Aktivitas</th>
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
        <td>
          <strong>{{ $program['konsultan_nama'] ?? '-' }}</strong><br>
          <small>{{ $program['konsultan_spesialisasi'] ?? '-' }}</small>
        </td>
        <td>{{ $program['keterangan'] ?? '-' }}</td>
        <td>{{ $program['tujuan'] ?? '-' }}</td>
        <td>{{ $program['aktivitas'] ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">Tidak ada program aktif untuk entri PPI ini.</div>
  @endif

  <div class="footer">
    <div>Dokumen Lesson Plan ini dihasilkan dari program aktif pada tanggal pembuatan PPI.</div>
    <div style="margin-top:4px;">&copy; {{ now()->year }} R&amp;B Dev. All Rights Reserved.</div>
  </div>

</body>

</html>