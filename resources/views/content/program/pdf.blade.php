@extends('layouts.print')

@section('title', 'Export PDF - Observasi/Evaluasi Program')

@section('content')
<div class="container py-4">
  <div class="alert alert-warning d-print-none" style="font-size:1rem">
    <strong>Petunjuk:</strong> Tekan <b>Ctrl+P</b> (atau <b>Cmd+P</b> di Mac) lalu pilih <b>Save as PDF</b> untuk menyimpan file ini sebagai PDF.
  </div>
  <h2 class="mb-3">Detail Observasi/Evaluasi Program</h2>
  <table class="table table-bordered">
    <tr>
      <th style="width:30%">Anak Didik</th>
      <td>{{ $program->anakDidik->nama ?? '-' }}</td>
    </tr>
    <tr>
      <th>Guru Fokus</th>
      <td>{{ $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-' }}</td>
    </tr>
    <tr>
      <th>Konsultan</th>
      <td>{{ $program->konsultan->nama ?? '-' }}</td>
    </tr>
    <tr>
      <th>Tanggal</th>
      <td>{{ $program->created_at ? $program->created_at->format('d/m/Y') : '-' }}</td>
    </tr>
    <tr>
      <th>Kemampuan</th>
      <td>
        @if(is_array($program->kemampuan) && count($program->kemampuan) > 0)
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Kemampuan</th>
              <th class="text-center">1</th>
              <th class="text-center">2</th>
              <th class="text-center">3</th>
              <th class="text-center">4</th>
              <th class="text-center">5</th>
            </tr>
          </thead>
          <tbody>
            @foreach($program->kemampuan as $item)
            <tr>
              <td>{{ $item['judul'] ?? '-' }}</td>
              @for($skala=1; $skala<=5; $skala++)
                <td class="text-center">@if(isset($item['skala']) && (int)$item['skala'] === $skala)✔️@endif
      </td>
      @endfor
    </tr>
    @endforeach
    </tbody>
  </table>
  @else
  <em>Tidak ada data kemampuan</em>
  @endif
  </td>
  </tr>
  <tr>
    <th>Wawancara</th>
    <td>{{ $program->wawancara ?? '-' }}</td>
  </tr>
  <tr>
    <th>Kemampuan Saat Ini</th>
    <td>{{ $program->kemampuan_saat_ini ?? '-' }}</td>
  </tr>
  <tr>
    <th>Saran / Rekomendasi</th>
    <td>{{ $program->saran_rekomendasi ?? '-' }}</td>
  </tr>
  </table>
</div>
@endsection