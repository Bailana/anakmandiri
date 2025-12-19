@extends('layouts/contentNavbarLayout')

@section('title', 'Program Anak')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Program Anak</h4>
          <p class="text-body-secondary mb-0">Kelola program anak didik</p>
        </div>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
        <a href="{{ route('program-anak.create') }}" class="btn btn-primary">
          <i class="ri-add-line me-2"></i>Tambah Program Anak
        </a>
        @endif
      </div>
    </div>
  </div>
</div>
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('program-anak.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width:200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau program..." value="{{ request('search') }}">
      </div>
      <div>
        <button type="submit" class="btn btn-outline-primary" title="Cari">
          <i class="ri-search-line"></i>
        </button>
      </div>
      <div>
        <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="ri-refresh-line"></i></a>
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="programAnakTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama Anak</th>
              <th>Program</th>
              <th>Saran Terapi</th>
              <!-- <th>Status</th> -->
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programAnak as $index => $program)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $program->anakDidik->nama ?? '-' }}</td>
              <td>{{ $program->nama_program }}</td>
              <td>
                @php
                $pk = $program->programKonsultan ?? null;
                $konsultanSpesRaw = optional($pk)->konsultan->spesialisasi ?? null;
                if (!$konsultanSpesRaw && isset($currentKonsultanSpesRaw)) {
                $konsultanSpesRaw = $currentKonsultanSpesRaw;
                }
                $konsultanSpes = strtolower($konsultanSpesRaw ?? '');
                $badge = null;
                if ($program->is_suggested && $konsultanSpesRaw) {
                if (str_contains($konsultanSpes, 'wicara')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_contains($konsultanSpes, 'sensori') || str_contains($konsultanSpes, 'integrasi')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_contains($konsultanSpes, 'psikologi')) {
                $badge = ['label' => 'PS', 'class' => 'bg-warning text-dark'];
                } else {
                // try to derive from kode_program if available (e.g. SI-001, WIC-001, PS-001)
                $kode = strtoupper($program->kode_program ?? '');
                if (str_starts_with($kode, 'SI')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_starts_with($kode, 'WIC') || str_starts_with($kode, 'WICARA')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_starts_with($kode, 'PS')) {
                $badge = ['label' => 'PS', 'class' => 'bg-warning text-dark'];
                } else {
                $parts = preg_split('/\s+/', trim($konsultanSpesRaw));
                $initials = '';
                foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= strtoupper(mb_substr($p, 0, 1));
                }
                $label = $initials ?: strtoupper(substr($konsultanSpesRaw, 0, 2));
                $badge = ['label' => $label, 'class' => 'bg-info'];
                }
                }
                }
                @endphp
                @if($badge)
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                @else
                -
                @endif
              </td>
              <!-- <td><span class="badge bg-label-success">{{ ucfirst($program->status) }}</span></td> -->
              <td>
                <a href="{{ route('program-anak.show', $program->id) }}" class="btn btn-sm btn-outline-info" title="Lihat"><i class="ri-eye-line"></i></a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('program-anak.edit', $program->id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="ri-edit-line"></i></a>
                <form action="{{ route('program-anak.destroy', $program->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                </form>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection