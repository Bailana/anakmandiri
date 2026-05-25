@extends('layouts.print')

@section('title', 'LAPORAN HASIL BELAJAR-' . ($rapor->anakDidik->nama ?? 'Rapor'))

@push('head')
<style>
  :root {
    --primary: #666cff;
    --success: #71dd5a;
    --info: #03c3ec;
    --warning: #ffb64d;
    --danger: #ff3e1d;
    --gray-100: #f8f9fa;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-600: #6c757d;
    --gray-900: #1a1a1a;
  }

  * {
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    color: #4a4a4a;
    line-height: 1.5;
    background: white;
  }

  .preview-shell {
    padding: 4px 0;
    max-width: 100%;
    position: relative;
    counter-reset: section-counter;
    page-break-after: avoid;
  }

  /* Section Title */
  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
    margin-top: 16px;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    counter-increment: section-counter;
  }

  .section-title::before {
    content: '';
    width: 4px;
    height: 20px;
    background: var(--primary);
    border-radius: 2px;
    margin-right: 10px;
  }

  /* Group Catatan */
  .group-catatan {
    font-size: 12px;
    color: var(--gray-700);
    margin-bottom: 8px;
    padding: 8px 12px;
    background: #f5f5f5;
    border-left: 3px solid #ddd;
    line-height: 1.4;
  }

  /* Subsection Title */
  .subsection-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--gray-900);
    margin-top: 10px;
    margin-bottom: 4px;
    padding: 4px 8px;
    background: var(--gray-100);
    border-left: 3px solid var(--primary);
  }

  /* Subsection Group */
  .subsection-group {
    page-break-inside: avoid;
  }

  .preview-group-block {
    page-break-inside: auto;
    break-inside: auto;
  }

  .preview-group-block:first-of-type {
    page-break-before: auto;
  }

  .preview-group-block+.preview-group-block {
    page-break-before: always;
  }

  /* Meta Information */
  .preview-meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
    page-break-inside: avoid;
  }

  .meta-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 12px;
    background: #f9fafb;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
  }

  .meta-label {
    font-size: 11px;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 3px;
    font-weight: 600;
  }

  .meta-value {
    font-size: 13px;
    font-weight: 700;
    color: var(--gray-900);
  }

  /* Tables */
  .preview-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
    font-size: 12px;
    margin-bottom: 16px;
    page-break-inside: avoid;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-radius: 6px;
    overflow: hidden;
  }

  .preview-table thead tr {
    background: linear-gradient(135deg, var(--primary) 0%, #5a5dff 100%);
    color: white;
  }

  .preview-table th {
    padding: 9px 10px;
    text-align: center;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    border: none;
    vertical-align: middle;
  }

  .preview-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.2s;
  }

  .preview-table tbody tr:hover {
    background: #fafbff;
  }

  .preview-table tbody tr:last-child {
    border-bottom: none;
  }

  .preview-table td {
    padding: 9px 10px;
    vertical-align: top;
    word-break: break-word;
    border: none;
  }

  .preview-table td.text-center {
    text-align: center;
  }

  .preview-table th.text-center {
    text-align: center;
  }

  .preview-col-program {
    width: 50%;
    font-weight: 500;
    color: var(--gray-900);
  }

  .preview-col-nilai {
    width: 12%;
  }

  .preview-col-catatan-kategori {
    width: 38%;
    padding: 12px 10px;
    font-size: 11px;
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  .preview-col-catatan {
    width: 38%;
    font-size: 11px;
    color: var(--gray-600);
    text-align: justify;
    line-height: 1.5;
    word-wrap: break-word;
  }

  .group-catatan-below {
    font-size: 11px;
    color: var(--gray-700);
    margin-top: 8px;
    margin-bottom: 16px;
    padding: 8px 12px;
    background: #f9fafb;
    border-left: 3px solid #ddd;
    line-height: 1.4;
  }

  /* Table Catatan Kategori */
  .preview-table-catatan {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    margin-top: 8px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .preview-table-catatan tbody tr {
    border-bottom: 1px solid #e5e7eb;
  }

  .preview-table-catatan td {
    padding: 10px 12px;
    vertical-align: top;
    font-size: 11px;
    color: var(--gray-700);
  }

  .catatan-kategori-label {
    font-weight: 600;
    width: 20%;
    color: var(--gray-900);
    padding-right: 8px;
  }

  .catatan-kategori-value {
    width: 80%;
    line-height: 1.5;
    word-wrap: break-word;
    text-align: justify;
  }

  .preview-cell-nilai {
    text-align: center;
    vertical-align: middle;
  }

  /* Badge Styling */
  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 28px;
    padding: 0 8px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.01em;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    background: #e9ecef;
    color: #6c757d;
  }

  .badge.a {
    background: #dcfce7;
    color: #166534;
  }

  .badge.b {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .badge.c {
    background: #fef3c7;
    color: #92400e;
  }

  .badge.d {
    background: #fee2e2;
    color: #b91c1c;
  }

  .badge.\- {
    background: #f3f4f6;
    color: #6b7280;
  }

  /* Print Button */
  .print-btn-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
  }

  .btn-print {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(102, 108, 255, 0.25);
    transition: all 0.2s;
  }

  .btn-print:hover {
    background: #5a5dff;
    box-shadow: 0 6px 16px rgba(102, 108, 255, 0.35);
  }

  .btn-print:active {
    transform: scale(0.98);
  }

  /* Suggestions Section */
  .suggestions-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 16px;
    page-break-inside: avoid;
  }

  .suggestion-card {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 12px;
    background: #fafbff;
  }

  .suggestion-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    text-transform: uppercase;
    margin-bottom: 6px;
    letter-spacing: 0.02em;
  }

  .suggestion-content {
    font-size: 12px;
    line-height: 1.6;
    color: var(--gray-900);
    white-space: pre-wrap;
    text-align: justify;
  }

  /* Signature Section */
  .signature-section {
    margin-top: 32px;
    page-break-inside: avoid;
  }

  .signature-location {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-900);
    margin-bottom: 4px;
    text-align: center;
  }

  .signature-approval-text {
    font-size: 13px;
    color: var(--gray-900);
    margin-bottom: 24px;
    text-align: center;
  }

  .signature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px 32px;
    margin-top: 16px;
  }

  .signature-item {
    text-align: center;
    page-break-inside: avoid;
  }

  .signature-line {
    border-top: 1px solid #000;
    margin-top: 12px;
    margin-bottom: 4px;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
  }

  .signature-space {
    min-height: 50px;
  }

  .signature-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 2px;
  }

  .signature-title {
    font-size: 10px;
    color: var(--gray-600);
    line-height: 1.4;
  }

  /* Footer */
  .print-footer {
    display: none;
    margin-top: 8px;
    padding: 4px 0;
    background: transparent;
    border-radius: 0;
    font-size: 8px;
    color: #6b7280;
    line-height: 1.2;
  }

  .print-footer strong {
    color: #4b5563;
    display: inline;
    margin-bottom: 0;
    font-size: 8px;
  }

  .print-footer p {
    margin: 0;
    padding: 0;
    display: inline;
    white-space: nowrap;
    overflow: hidden;
  }

  /* Utilities */
  .d-print-none {
    display: block;
  }

  .page-header-repeat {
    display: none;
  }

  /* Responsive Adjustments */
  @media screen and (max-width: 768px) {
    .preview-meta {
      grid-template-columns: repeat(2, 1fr);
    }

    .suggestions-section {
      grid-template-columns: 1fr;
    }
  }

  /* Print Media Styles */
  @media print {
    body {
      background: white;
      margin: 0;
      padding: 0;
    }

    .preview-shell {
      padding: 0;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .d-print-none {
      display: none !important;
    }

    .page-header-repeat {
      display: block !important;
    }

    .print-btn-wrapper {
      display: none !important;
    }

    .print-footer {
      display: block !important;
    }

    .preview-meta {
      grid-template-columns: repeat(4, 1fr);
      page-break-inside: avoid;
    }

    .preview-table {
      page-break-inside: avoid;
      break-inside: avoid;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .subsection-group {
      page-break-inside: avoid;
      break-inside: avoid;
    }

    .preview-group-block {
      page-break-inside: auto;
      break-inside: auto;
    }

    .preview-table-catatan {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      page-break-inside: avoid;
    }

    .preview-table thead tr {
      background: linear-gradient(135deg, var(--primary) 0%, #5a5dff 100%) !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .preview-table th {
      color: white !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .badge {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .suggestions-section {
      page-break-inside: avoid;
    }

    .signature-section {
      page-break-inside: avoid;
    }

    .signature-grid {
      grid-template-columns: 1fr 1fr;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    /* Optimize for PDF output */
    * {
      box-shadow: none !important;
      transform: none !important;
    }
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 20px;
    color: var(--gray-600);
    font-size: 13px;
  }

  /* Page Header untuk repeat di halaman berikutnya */
  .page-header-repeat {
    page-break-inside: avoid;
    text-align: center;
    margin-bottom: 12px;
    margin-top: 8px;
  }

  .page-header-repeat img {
    max-width: 100%;
    height: auto;
    display: block;
    margin-bottom: 8px;
  }
</style>
@endpush

{{-- Define print-meta section so layout can render it under kop (outside .content) --}}
@section('print-meta')
@endsection

@section('content')

<div class="preview-shell">

  <div class="preview-meta">
    <div class="meta-card">
      <div class="meta-label">Nama Anak Didik</div>
      <div class="meta-value">{{ $rapor->anakDidik->nama ?? '-' }}</div>
    </div>
    <div class="meta-card">
      <div class="meta-label">Kelas</div>
      <div class="meta-value">{{ $rapor->kelas ?? '-' }}</div>
    </div>
    <div class="meta-card">
      <div class="meta-label">Semester</div>
      <div class="meta-value">Semester {{ $rapor->semester }}</div>
    </div>
    <div class="meta-card">
      <div class="meta-label">Tahun Pelajaran</div>
      <div class="meta-value">{{ $rapor->tahun_pelajaran ?? '-' }}</div>
    </div>
  </div>

  <div class="print-btn-wrapper d-print-none">
    <button class="btn-print" onclick="window.print()"><span>📄</span> Cetak / Simpan PDF</button>
  </div>

  @if(isset($previewGroups) && is_array($previewGroups) && count($previewGroups) > 0)
  @php
  // ensure preferred category order for preview groups
  $preferred = ['Basic Learning','Akademik','Bina Diri','Motorik'];
  try {
  usort($previewGroups, function($a, $b) use ($preferred) {
  $map = array_flip(array_map('strtolower', $preferred));
  $la = strtolower($a['label'] ?? ($a['label'] ?? ''));
  $lb = strtolower($b['label'] ?? ($b['label'] ?? ''));
  $ia = $map[$la] ?? 1000;
  $ib = $map[$lb] ?? 1000;
  if ($ia !== $ib) return $ia - $ib;
  return strcmp($la, $lb);
  });
  } catch (Throwable $e) {
  // ignore
  }
  @endphp
  @foreach($previewGroups as $group)
  <div class="preview-group-block">
    <div class="section-title">{{ $group['label'] }}</div>

    @php
    // build subgroups by initial letter (A, B, etc.)
    $subMap = [];
    foreach($group['programs'] as $p) {
    $name = trim($p['nama_program'] ?? '');
    if (preg_match('/^([A-Za-z])/i', $name, $m)) {
    $k = strtoupper($m[1]);
    } else {
    $k = '_';
    }
    $subMap[$k][] = $p;
    }
    ksort($subMap);
    $groupNoteText = !empty($group['group_note']) ? $group['group_note'] : null;
    $subTitles = [
    'A' => 'Sikap Kooperatif dan Penguatan Kemampuan yang Efektif (A1-A19)',
    'B' => 'Kemampuan Visual (B1-B27)',
    'C' => 'Bahasa Reseptif (Reseptive Language) (C1-C57)',
    'D' => 'Menirukan (Imitation) (D1-D27)',
    'E' => 'Menirukan Secara Lisan (E1-E20)',
    'F' => 'Kemampuan Permintaan (F1-F29)',
    'G' => 'Menamakan (Labeling) (G1-G47)',
    'H' => 'Kemampuan Intraverbal (Intraverbal) (H1-H49)',
    'I' => 'Spontan Secara Lisan (I1-I9)',
    'J' => 'Aturan Penyusunan Kata dan Tata Bahasa (Syntax and Grammar) (J1-J20)',
    'K' => 'Kemampuan Bermain (K1-K15)',
    'L' => 'Interaksi Sosial (L1-L34)',
    'M' => 'Belajar Berkelompok (M1-M12)',
    'N' => 'Mengikuti Rutinitas di dalam Kelas (N1-N10)',
    'P' => 'Menggeneralisasikan Respon (Generalized Respon) (P1-P6)',
    'Q' => 'Kemampuan Membaca (Reading Skills) (Q1-Q17)',
    'R' => 'Kemampuan Berhitung (Math Skills) (R1-R29)',
    'S' => 'Kemampuan Menulis (Writing Skills) (S1-S10)',
    'T' => 'Mengeja (Spelling) (T1-T7)',
    'U' => 'Kemampuan Berpakaian (Dressing Skill) (U1-U15)',
    'V' => 'Kemampuan/Tata Cara Makan (Eating Skills) (V1-V10)',
    'W' => 'Kebersihan Diri (Grooming Skills) (W1-W7)',
    'X' => 'Kemampuan Menggunakan Toilet (Toileting Skills) (X1-X10)',
    'Y' => 'Kemampuan Motorik Kasar (Gross Motor Skills) (Y1-Y30)',
    'Z' => 'Kemampuan Motorik Halus (Fine Motor Skills) (Z1-Z28)'
    ];
    @endphp

    @foreach($subMap as $subKey => $programs)
    <div class="subsection-group">
      @if(isset($subTitles[$subKey]))
      <div class="subsection-title">{{ $subTitles[$subKey] }}</div>
      @endif
      <table class="preview-table">
        <thead>
          <tr>
            <th class="preview-col-program">Program</th>
            <th class="preview-col-nilai">Nilai</th>
            <th class="preview-col-catatan">Catatan Program</th>
          </tr>
        </thead>
        <tbody>
          @foreach($programs as $item)
          <tr>
            <td class="preview-col-program">{{ $item['nama_program'] }}</td>
            <td class="preview-cell-nilai"><span class="badge {{ strtolower($item['nilai_huruf'] ?? '-') }}">{{ $item['nilai_huruf'] ?? '-' }}</span></td>
            <td class="preview-col-catatan">{!! nl2br(e($item['catatan'] ?? '-')) !!}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <table class="preview-table-catatan">
        <tbody>
          <tr>
            <td class="catatan-kategori-label">Catatan {{ $group['label'] }}:</td>
            <td class="catatan-kategori-value">{{ !empty($groupNoteText) ? $groupNoteText : '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    @endforeach
  </div>
  @endforeach
  @else
  @php
  $grouped = collect($rapor->items)->groupBy(function($item){
  if ($item->kategori_label) return $item->kategori_label;
  if ($item->kategori === 'perilaku') return 'Basic Learning';
  return $item->kategori && $item->kategori !== '-' ? ucfirst($item->kategori) : 'Lainnya';
  });
  @endphp

  @php
  // reorder grouped categories to preferred sequence first
  $preferred = ['Basic Learning','Akademik','Bina Diri','Motorik'];
  $ordered = [];
  foreach ($preferred as $p) {
  if ($grouped->has($p)) {
  $ordered[$p] = $grouped->get($p);
  $grouped = $grouped->except($p);
  }
  }
  // append remaining categories sorted by key
  $remainingKeys = $grouped->keys()->sort();
  foreach ($remainingKeys as $k) {
  $ordered[$k] = $grouped->get($k);
  }
  $grouped = collect($ordered);
  @endphp

  @foreach($grouped as $kategori => $items)
  <div class="preview-group-block">
    <div class="section-title">{{ $kategori }}</div>

    @php
    $groupNoteText = (isset($rapor->group_notes[$kategori]) && !empty($rapor->group_notes[$kategori])) ? $rapor->group_notes[$kategori] : null;
    // subgroup within this category
    $subMap = [];
    foreach($items as $p) {
    $name = trim($p->nama_program ?? '');
    if (preg_match('/^([A-Za-z])/i', $name, $m)) {
    $k = strtoupper($m[1]);
    } else {
    $k = '_';
    }
    $subMap[$k][] = $p;
    }
    ksort($subMap);
    $subTitles = [
    'A' => 'Sikap Kooperatif dan Penguatan Kemampuan yang Efektif (A1-A19)',
    'B' => 'Kemampuan Visual (B1-B27)',
    'C' => 'Bahasa Reseptif (Reseptive Language) (C1-C57)',
    'D' => 'Menirukan (Imitation) (D1-D27)',
    'E' => 'Menirukan Secara Lisan (E1-E20)',
    'F' => 'Kemampuan Permintaan (F1-F29)',
    'G' => 'Menamakan (Labeling) (G1-G47)',
    'H' => 'Kemampuan Intraverbal (Intraverbal) (H1-H49)',
    'I' => 'Spontan Secara Lisan (I1-I9)',
    'J' => 'Aturan Penyusunan Kata dan Tata Bahasa (Syntax and Grammar) (J1-J20)',
    'K' => 'Kemampuan Bermain (K1-K15)',
    'L' => 'Interaksi Sosial (L1-L34)',
    'M' => 'Belajar Berkelompok (M1-M12)',
    'N' => 'Mengikuti Rutinitas di dalam Kelas (N1-N10)',
    'P' => 'Menggeneralisasikan Respon (Generalized Respon) (P1-P6)',
    'Q' => 'Kemampuan Membaca (Reading Skills) (Q1-Q17)',
    'R' => 'Kemampuan Berhitung (Math Skills) (R1-R29)',
    'S' => 'Kemampuan Menulis (Writing Skills) (S1-S10)',
    'T' => 'Mengeja (Spelling) (T1-T7)',
    'U' => 'Kemampuan Berpakaian (Dressing Skill) (U1-U15)',
    'V' => 'Kemampuan/Tata Cara Makan (Eating Skills) (V1-V10)',
    'W' => 'Kebersihan Diri (Grooming Skills) (W1-W7)',
    'X' => 'Kemampuan Menggunakan Toilet (Toileting Skills) (X1-X10)',
    'Y' => 'Kemampuan Motorik Kasar (Gross Motor Skills) (Y1-Y30)',
    'Z' => 'Kemampuan Motorik Halus (Fine Motor Skills) (Z1-Z28)'
    ];
    @endphp

    @foreach($subMap as $subKey => $programs)
    <div class="subsection-group">
      @if(isset($subTitles[$subKey]))
      <div class="subsection-title">{{ $subTitles[$subKey] }}</div>
      @endif
      <table class="preview-table">
        <thead>
          <tr>
            <th class="preview-col-program">Program</th>
            <th class="preview-col-nilai">Nilai</th>
            <th class="preview-col-catatan">Catatan Program</th>
          </tr>
        </thead>
        <tbody>
          @foreach($programs as $item)
          <tr>
            <td class="preview-col-program">{{ $item->nama_program }}</td>
            <td class="preview-cell-nilai"><span class="badge {{ strtolower($item->nilai_huruf) }}">{{ $item->nilai_huruf }}</span></td>
            <td class="preview-col-catatan">{!! nl2br(e($item->catatan ?: '-')) !!}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <table class="preview-table-catatan">
        <tbody>
          <tr>
            <td class="catatan-kategori-label">Catatan {{ $kategori }}:</td>
            <td class="catatan-kategori-value">{{ !empty($groupNoteText) ? $groupNoteText : '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    @endforeach
  </div>
  @endforeach

  @if($grouped->isEmpty())
  <div class="empty-state">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:inline-block;margin-bottom:8px;opacity:0.5;">
      <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"></path>
    </svg>
    <p>Belum ada program pada rapor ini.</p>
  </div>
  @endif
  @endif

  <!-- Keterangan Penilaian Section -->
  <div class="print-footer" style="margin-top: 20px; page-break-inside: avoid;">
    <strong>Keterangan Penilaian:</strong>
    <p><strong>A</strong> = Tercapai sesuai kriteria | <strong>B</strong> = Tercapai 80% | <strong>C</strong> = Tercapai 50% | <strong>D</strong> = Belum tercapai | <strong>-</strong> = Belum Terlaksana</p>
  </div>

  <!-- Attendance Summary Section -->
  @if(isset($absensiSummary) && !empty($absensiSummary['monthly']))
  <div class="section-title">Rekapitulasi Kehadiran Semester</div>

  <div class="preview-table-wrapper">
    <table class="preview-table">
      <thead>
        <tr>
          <th class="text-center" style="width: 25%;">Bulan</th>
          <th class="text-center" style="width: 25%;">Hadir</th>
          <th class="text-center" style="width: 25%;">Izin</th>
          <th class="text-center" style="width: 25%;">Alpha</th>
        </tr>
      </thead>
      <tbody>
        @foreach($absensiSummary['monthly'] as $month)
        <tr>
          <td class="text-center">{{ $month['bulan'] }} {{ $month['tahun'] }}</td>
          <td class="text-center">{{ $month['hadir'] }}</td>
          <td class="text-center">{{ $month['izin'] }}</td>
          <td class="text-center">{{ $month['alfa'] }}</td>
        </tr>
        @endforeach
        <tr style="font-weight: 700; background-color: #f8f9fa;">
          <td class="text-center">JUMLAH TOTAL</td>
          <td class="text-center">{{ $absensiSummary['total']['hadir'] }}</td>
          <td class="text-center">{{ $absensiSummary['total']['izin'] }}</td>
          <td class="text-center">{{ $absensiSummary['total']['alfa'] }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  @else
  <div class="section-title">Rekapitulasi Kehadiran Semester</div>
  <div class="empty-state">
    <p>Belum ada data kehadiran pada semester ini.</p>
  </div>
  @endif

  <div class="suggestions-section">
    <div class="suggestion-card">
      <div class="suggestion-label">💬 Saran Guru</div>
      <div class="suggestion-content">{{ $rapor->saran_guru ?: '-' }}</div>
    </div>
    <div class="suggestion-card">
      <div class="suggestion-label">👨‍👩‍👧 Saran Orang Tua</div>
      <div class="suggestion-content">{{ $rapor->saran_orang_tua ?: '-' }}</div>
    </div>
  </div>

  <!-- Signature Section -->
  <div class="signature-section">
    <div class="signature-location">Pekanbaru, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
    <div class="signature-approval-text">Mengetahui,</div>

    <div class="signature-grid">
      <!-- Orang Tua/Wali -->
      <div class="signature-item">
        <div class="signature-name">Orang Tua/Wali</div>
        <div class="signature-space"></div>
        <div class="signature-line"></div>
      </div>

      <!-- Guru Kelas/Fokus -->
      <div class="signature-item">
        <div class="signature-name">Guru Kelas/Fokus</div>
        <div class="signature-space"></div>
        <div class="signature-line"></div>
        <div class="signature-name" style="margin-top: 4px; font-weight: 500; font-size: 11px;">{{ $rapor->anakDidik->guruFokus->nama ?? '(Nama Guru)' }}</div>
      </div>

      <!-- Pimpinan Lembaga -->
      <div class="signature-item">
        <div class="signature-name">Pimpinan Lembaga</div>
        <div class="signature-title">Pendidikan Sekolah Luar Biasa Anak Mandiri</div>
        <div class="signature-space"></div>
        <div class="signature-line"></div>
        <div class="signature-name" style="margin-top: 4px; font-weight: 500; font-size: 11px;">Rovanita Rama, S.E., M.H.</div>
      </div>

      <!-- Kepala Sekolah -->
      <div class="signature-item">
        <div class="signature-name">Kepala Sekolah</div>
        <div class="signature-title">Sekolah Luar Biasa Anak Mandiri</div>
        <div class="signature-space"></div>
        <div class="signature-line"></div>
        <div class="signature-name" style="margin-top: 4px; font-weight: 500; font-size: 11px;">Rovaldi Rama, S.E.</div>
      </div>
    </div>
  </div>

  @endsection