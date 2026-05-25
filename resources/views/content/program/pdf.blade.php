@extends('layouts.print')

@section('title', 'Export PDF - Observasi/Evaluasi Program')

@section('content')
<style>
  @page {
    margin: 13mm 12mm 14mm;
    size: A4;
  }

  :root {
    --ink: #102033;
    --muted: #667085;
    --line: #d7dde5;
    --soft: #f7f9fc;
    --soft-2: #eef3f9;
    --brand: #2563eb;
    --brand-2: #0ea5e9;
    --success: #16a34a;
  }

  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--ink);
    background: #fff;
    line-height: 1.45;
  }

  .report {
    padding: 0;
  }

  .report-header {
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 14px;
    background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
    box-shadow: 0 8px 24px rgba(16, 32, 51, 0.06);
  }

  .report-header__top {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px 14px;
    border-bottom: 1px solid rgba(16, 32, 51, 0.08);
  }

  .report-header__title {
    max-width: 72%;
  }

  .report-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--brand);
    margin-bottom: 8px;
  }

  .report-kicker::before {
    content: '';
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
  }

  .report-title {
    margin: 0;
    font-size: 21px;
    line-height: 1.15;
    font-weight: 800;
    color: var(--ink);
  }

  .report-subtitle {
    margin: 8px 0 0;
    color: var(--muted);
    font-size: 12px;
  }

  .report-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
    align-content: flex-start;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 6px 11px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.02em;
    border: 1px solid transparent;
    white-space: nowrap;
  }

  .badge-source {
    background: #e8f1ff;
    color: #1d4ed8;
    border-color: #c7ddff;
  }

  .badge-date {
    background: #f4f6f9;
    color: var(--ink);
    border-color: #e1e7ef;
  }

  .badge-owner {
    background: #ecfdf3;
    color: #166534;
    border-color: #c9efd5;
  }

  .report-header__note {
    padding: 10px 20px 14px;
    font-size: 11px;
    color: var(--muted);
    background: rgba(255, 255, 255, 0.65);
  }

  .section {
    margin-bottom: 12px;
    page-break-inside: avoid;
  }

  .section--skills {
    page-break-inside: auto;
    break-inside: auto;
  }

  .section--skills .detail-card {
    padding: 0;
    border: none;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
  }

  .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 800;
    color: var(--ink);
  }

  .section-title::before {
    content: '';
    width: 4px;
    height: 16px;
    border-radius: 999px;
    background: linear-gradient(180deg, var(--brand), var(--brand-2));
    flex: 0 0 auto;
  }

  .summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
  }

  .summary-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #fff;
    padding: 11px 12px;
    min-height: 74px;
    box-shadow: 0 3px 10px rgba(16, 32, 51, 0.04);
  }

  .summary-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    margin-bottom: 6px;
    font-weight: 700;
  }

  .summary-value {
    font-size: 12px;
    font-weight: 800;
    color: var(--ink);
    line-height: 1.35;
    word-break: break-word;
  }

  .detail-card {
    border: 1px solid var(--line);
    border-radius: 16px;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(16, 32, 51, 0.04);
  }

  .detail-table {
    width: 100%;
    border-collapse: collapse;
  }

  .detail-table th,
  .detail-table td {
    border-bottom: 1px solid #edf1f6;
    padding: 11px 12px;
    vertical-align: top;
    font-size: 11px;
  }

  .detail-table tr:last-child th,
  .detail-table tr:last-child td {
    border-bottom: none;
  }

  .detail-table th {
    width: 24%;
    background: var(--soft);
    color: #344054;
    text-align: left;
    font-weight: 700;
  }

  .detail-value {
    color: var(--ink);
    line-height: 1.6;
  }

  .detail-value--justify {
    display: block;
    width: 100%;
    text-align: justify;
    text-align-last: left;
    text-justify: inter-word;
    hyphens: auto;
  }

  .text-center {
    text-align: center;
  }

  .skills-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 6px;
  }

  .skills-table thead th {
    background: linear-gradient(135deg, #1d4ed8, #0f766e);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 10px 8px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .skills-table tbody td {
    border: 1px solid #e7ecf3;
    padding: 9px 8px;
    font-size: 10px;
    vertical-align: middle;
  }

  .skills-table tbody tr:nth-child(even) {
    background: #fbfcfe;
  }

  .skills-table thead {
    display: table-header-group;
  }

  .skills-table tbody tr {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .check {
    color: var(--success);
    font-size: 12px;
    font-weight: 800;
  }

  .empty-state {
    padding: 14px 12px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    color: var(--muted);
    background: #fafcff;
    font-size: 11px;
  }

  .signature-section {
    margin-top: 18px;
    page-break-inside: avoid;
  }

  .signature-wrap {
    display: flex;
    justify-content: flex-end;
  }

  .signature-box {
    width: 280px;
    text-align: center;
    color: var(--ink);
    font-size: 11px;
    line-height: 1.5;
  }

  .signature-label {
    margin-bottom: 42px;
    font-weight: 700;
  }

  .signature-name {
    font-weight: 800;
    text-transform: none;
    text-decoration: underline;
    text-underline-offset: 4px;
    min-height: 18px;
  }

  @media print {

    .section,
    .detail-card,
    .summary-card,
    .report-header {
      box-shadow: none !important;
    }

    .detail-card,
    .summary-card,
    .report-header {
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .section--skills,
    .section--skills .detail-card {
      break-inside: auto;
      page-break-inside: auto;
    }

    .section--skills .detail-card {
      overflow: visible;
      border-radius: 0;
      border: none;
      padding: 0;
    }

    .skills-table {
      border: none;
      border-radius: 0;
      overflow: visible;
      box-shadow: none;
    }

    .report-header__note,
    .d-print-none {
      display: none !important;
    }

    .skills-table thead th {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .skills-table thead {
      display: table-header-group;
    }

    .skills-table tbody tr {
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .signature-section {
      break-inside: avoid;
      page-break-inside: avoid;
    }
  }
</style>

@php
$s = $sumber ?? 'wicara';
$sumberLabel = [
'wicara' => 'Wicara',
'psikologi' => 'Psikologi',
'si' => 'SI',
][$s] ?? strtoupper($s);

$anakDidik = $program->anakDidik ?? null;
$guruFokus = $anakDidik && $anakDidik->guruFokus ? $anakDidik->guruFokus->nama : '-';
$konsultanNama = optional($program->konsultan)->nama ?? optional($program->user)->name ?? '-';
$tanggal = $program->created_at ? $program->created_at->format('d/m/Y') : '-';
$isPsikologi = $s === 'psikologi';
$isSi = $s === 'si';
$kemampuan = is_array($program->kemampuan) ? $program->kemampuan : [];
$siSkalaValues = [5, 4, 3, 2, 1, 0];
$siSkalaLabels = [
5 => 'Baik sekali',
4 => 'Baik',
3 => 'Cukup',
2 => 'Kurang',
1 => 'Kurang sekali',
0 => 'Tidak ada',
];
@endphp

<div class="report">
  <div class="d-print-none" style="font-size:11px;margin-bottom:10px;color:#667085">
    <strong>Petunjuk:</strong> tekan <b>Ctrl+P</b> atau <b>Cmd+P</b>, lalu pilih <b>Save as PDF</b>.
  </div>

  <div class="report-header">
    <div class="report-header__top">
      <div class="report-header__title">
        <h1 class="report-title">Observasi & Evaluasi Terpadu</h1>
      </div>
      <div class="report-badges">
        <span class="badge badge-source">Sumber {{ $sumberLabel }}</span>
        <span class="badge badge-date">Tanggal {{ $tanggal }}</span>
        <span class="badge badge-owner">Konsultan {{ $konsultanNama }}</span>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-label">Anak Didik</div>
        <div class="summary-value">{{ $anakDidik->nama ?? '-' }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Guru Fokus</div>
        <div class="summary-value">{{ $guruFokus }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Konsultan</div>
        <div class="summary-value">{{ $konsultanNama }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Kategori Program</div>
        <div class="summary-value">{{ $sumberLabel }}</div>
      </div>
    </div>
  </div>

  <div class="section section--skills">
    <div class="detail-card" style="padding: 12px;">
      @if(count($kemampuan) > 0)
      @if($isSi)
      <table class="skills-table">
        <thead>
          <tr>
            <th style="text-align:left;width:34%">Kemampuan</th>
            @foreach($siSkalaValues as $sv)
            <th class="text-center">{{ $sv }}<br><span style="font-size:9px;font-weight:600;text-transform:none;letter-spacing:0;color:rgba(255,255,255,.88)">{{ $siSkalaLabels[$sv] }}</span></th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($kemampuan as $item)
          <tr>
            <td style="font-weight:700;color:var(--ink)">{{ $item['judul'] ?? '-' }}</td>
            @foreach($siSkalaValues as $sv)
            <td class="text-center">@if(isset($item['skala']) && (int) $item['skala'] === $sv)<span class="check">✓</span>@endif</td>
            @endforeach
          </tr>
          @endforeach
        </tbody>
      </table>
      @else
      <table class="skills-table">
        <thead>
          <tr>
            <th style="text-align:left;width:40%">Kemampuan</th>
            <th class="text-center">1</th>
            <th class="text-center">2</th>
            <th class="text-center">3</th>
            <th class="text-center">4</th>
            <th class="text-center">5</th>
          </tr>
        </thead>
        <tbody>
          @foreach($kemampuan as $item)
          <tr>
            <td style="font-weight:700;color:var(--ink)">{{ $item['judul'] ?? '-' }}</td>
            @for($skala = 1; $skala <= 5; $skala++)
              <td class="text-center">@if(isset($item['skala']) && (int) $item['skala'] === $skala)<span class="check">✓</span>@endif</td>
              @endfor
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
      @else
      <div class="empty-state">Tidak ada data kemampuan yang tersimpan untuk program ini.</div>
      @endif
    </div>
  </div>

  <div class="section">
    <div class="section-title">Detail Program</div>
    <div class="detail-card">
      <table class="detail-table">
        @if($isSi)
        <tr>
          <th>Keterangan</th>
          <td>
            <div class="detail-value detail-value--justify">{{ $program->keterangan ?? $program->wawancara ?? '-' }}</div>
          </td>
        </tr>
        @else
        <tr>
          <th>Wawancara</th>
          <td>
            <div class="detail-value detail-value--justify">{{ $program->wawancara ?? '-' }}</div>
          </td>
        </tr>
        <tr>
          <th>Kemampuan Saat Ini</th>
          <td>
            <div class="detail-value detail-value--justify">{{ $program->kemampuan_saat_ini ?? '-' }}</div>
          </td>
        </tr>
        <tr>
          <th>Diagnosa</th>
          <td>
            <div class="detail-value detail-value--justify">{{ $program->diagnosa ?? '-' }}</div>
          </td>
        </tr>
        <tr>
          <th>Saran / Rekomendasi</th>
          <td>
            <div class="detail-value detail-value--justify">{{ $program->saran_rekomendasi ?? '-' }}</div>
          </td>
        </tr>
        @endif
      </table>
    </div>
  </div>

  <div class="signature-section">
    <div class="signature-wrap">
      <div class="signature-box">
        <div class="signature-label">Konsultan,</div>
        <div class="signature-name">{{ $konsultanNama }}</div>
      </div>
    </div>
  </div>
</div>
@endsection