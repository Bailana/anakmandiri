@extends('layouts.print')

@section('title', 'Export PDF - Observasi/Evaluasi Program')

@section('content')
<div class="container py-2">
  <div class="d-print-none" style="font-size:0.95rem;margin-bottom:8px;color:#6c757d">
    <strong>Petunjuk:</strong> Tekan <b>Ctrl+P</b> (atau <b>Cmd+P</b>) lalu pilih <b>Save as PDF</b> untuk menyimpan file ini sebagai PDF.
  </div>

  @php $s = $sumber ?? 'wicara'; @endphp
  <h3 style="margin-bottom:0.5rem">Detail Observasi/Evaluasi</h3>
  <p style="margin-top:0;margin-bottom:12px;font-size:0.95rem">Sumber: {{ strtoupper($s) }}</p>

  <table style="width:100%;border-collapse:collapse;font-size:0.95rem">
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd;width:30%">Anak Didik</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->anakDidik->nama ?? '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Guru Fokus</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Konsultan</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->konsultan->nama ?? ($program->user->name ?? '-') }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Tanggal</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->created_at ? $program->created_at->format('d/m/Y') : '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Diagnosa</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->diagnosa ?? '-' }}</td>
    </tr>
    @if($s === 'psikologi')
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Latar Belakang</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->latar_belakang ?? '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Metode Assessment</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->metode_assessment ?? '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Hasil Assessment</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->hasil_assessment ?? '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Kesimpulan</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->kesimpulan ?? '-' }}</td>
    </tr>
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Diagnosa</th>
      <td style="padding:6px;border:1px solid #ddd">{{ $program->diagnosa_psikologi ?? $program->diagnosa ?? '-' }}</td>
    </tr>
    @else
    <tr>
      <th style="text-align:left;padding:6px;border:1px solid #ddd">Kemampuan</th>
      <td style="padding:6px;border:1px solid #ddd">
        @if(is_array($program->kemampuan) && count($program->kemampuan) > 0)
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="border:1px solid #ddd;padding:6px;text-align:left">Kemampuan</th>
              <th style="border:1px solid #ddd;padding:6px;text-align:center">1</th>
              <th style="border:1px solid #ddd;padding:6px;text-align:center">2</th>
              <th style="border:1px solid #ddd;padding:6px;text-align:center">3</th>
              <th style="border:1px solid #ddd;padding:6px;text-align:center">4</th>
              <th style="border:1px solid #ddd;padding:6px;text-align:center">5</th>
            </tr>

          </thead>
          <tbody>
            @foreach($program->kemampuan as $item)
            <tr>
              <td style="border:1px solid #ddd;padding:6px">{{ $item['judul'] ?? '-' }}</td>
              @for($skala=1; $skala<=5; $skala++)
                <td style="border:1px solid #ddd;padding:6px;text-align:center">@if(isset($item['skala']) && (int)$item['skala'] === $skala)✔️@endif
      </td>
      @endfor
    </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" style="padding:6px;font-size:12px;color:#555;background:#fafafa">
          <strong>Keterangan skala:</strong>
          <span style="margin-left:2px">1: Tidak Mampu</span>
          <span style="margin-left:2px">2: Kurang Mampu</span>
          <span style="margin-left:2px">3: Cukup Mampu</span>
          <span style="margin-left:2px">4: Mampu</span>
          <span style="margin-left:2px">5: Sangat Mampu</span>
        </td>
      </tr>
    </tfoot>
  </table>
  @else
  <em>Tidak ada data kemampuan</em>
  @endif
  </td>
  </tr>
  <tr>
    <th style="text-align:left;padding:6px;border:1px solid #ddd">Wawancara / Keterangan</th>
    <td style="padding:6px;border:1px solid #ddd">{{ $program->wawancara ?? $program->keterangan ?? '-' }}</td>
  </tr>
  <tr>
    <th style="text-align:left;padding:6px;border:1px solid #ddd">Kemampuan Saat Ini</th>
    <td style="padding:6px;border:1px solid #ddd">{{ $program->kemampuan_saat_ini ?? '-' }}</td>
  </tr>
  <tr>
    <th style="text-align:left;padding:6px;border:1px solid #ddd">Saran / Rekomendasi</th>
    <td style="padding:6px;border:1px solid #ddd">{{ $program->saran_rekomendasi ?? '-' }}</td>
  </tr>
  @endif

  </table>
</div>
@endsection