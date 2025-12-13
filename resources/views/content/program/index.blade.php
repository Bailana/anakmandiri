@extends('layouts/contentNavbarLayout')

@section('title', 'Program Pembelajaran')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Program Pembelajaran</h4>
            <p class="text-body-secondary mb-0">Kelola program pembelajaran anak didik</p>
          </div>
          <a href="{{ route('program.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Program
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Messages -->
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ri-checkbox-circle-line me-2"></i>{{ $message }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('program.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari program atau nama anak..." value="{{ request('search') }}">
      </div>

      <!-- Filter Kategori -->
      <select name="kategori" class="form-select" style="max-width: 150px;">
        <option value="">Semua Kategori</option>
        <option value="bina_diri" {{ request('kategori') === 'bina_diri' ? 'selected' : '' }}>Bina Diri</option>
        <option value="akademik" {{ request('kategori') === 'akademik' ? 'selected' : '' }}>Akademik</option>
        <option value="motorik" {{ request('kategori') === 'motorik' ? 'selected' : '' }}>Motorik</option>
        <option value="perilaku" {{ request('kategori') === 'perilaku' ? 'selected' : '' }}>Perilaku</option>
        <option value="vokasi" {{ request('kategori') === 'vokasi' ? 'selected' : '' }}>Vokasi</option>
      </select>

      <!-- Filter Status -->
      <select name="is_approved" class="form-select" style="max-width: 150px;">
        <option value="">Semua Status</option>
        <option value="true" {{ request('is_approved') === 'true' ? 'selected' : '' }}>Disetujui</option>
        <option value="false" {{ request('is_approved') === 'false' ? 'selected' : '' }}>Menunggu Approval</option>
      </select>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('program.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<!-- Table -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama Program</th>
              <th>Anak Didik</th>
              <th>Konsultan</th>
              <th>Kategori</th>
              <th>Tanggal Mulai</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programs as $index => $program)
            <tr id="row-{{ $program->id }}">
              <td>{{ ($programs->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>
                <strong>{{ $program->nama_program }}</strong><br>
                <small class="text-body-secondary">{{ $program->deskripsi ? Str::limit($program->deskripsi, 50) : '-' }}</small>
              </td>
              <td>{{ $program->anakDidik->nama ?? '-' }}</td>
              <td>{{ $program->konsultan->nama ?? '-' }}</td>
              <td>
                @php
                $kategoriColors = [
                'bina_diri' => 'primary',
                'akademik' => 'info',
                'motorik' => 'success',
                'perilaku' => 'warning',
                'vokasi' => 'danger',
                ];
                $kategoriLabels = [
                'bina_diri' => 'Bina Diri',
                'akademik' => 'Akademik',
                'motorik' => 'Motorik',
                'perilaku' => 'Perilaku',
                'vokasi' => 'Vokasi',
                ];
                @endphp
                <span class="badge bg-label-{{ $kategoriColors[$program->kategori] ?? 'secondary' }}">
                  {{ $kategoriLabels[$program->kategori] ?? $program->kategori }}
                </span>
              </td>
              <td>{{ $program->tanggal_mulai ? $program->tanggal_mulai->format('d M Y') : '-' }}</td>
              <td>
                @if($program->is_approved)
                <span class="badge bg-success">
                  <i class="ri-check-line me-1"></i>Disetujui
                </span>
                @else
                <span class="badge bg-warning">
                  <i class="ri-time-line me-1"></i>Menunggu
                </span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#detailModal"
                    data-program-id="{{ $program->id }}"
                    onclick="showDetail(this)"
                    title="Lihat Detail">
                    <i class="ri-eye-line"></i>
                  </button>
                  <a
                    href="{{ route('program.edit', $program->id) }}"
                    class="btn btn-sm btn-icon btn-outline-warning"
                    title="Edit">
                    <i class="ri-edit-line"></i>
                  </a>
                  @if(!$program->is_approved)
                  <form action="{{ route('program.approve', $program->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Setujui">
                      <i class="ri-check-double-line"></i>
                    </button>
                  </form>
                  @endif
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-danger"
                    data-program-id="{{ $program->id }}"
                    onclick="deleteData(this)"
                    title="Hapus">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada program ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $programs->firstItem() ?? 0 }} hingga {{ $programs->lastItem() ?? 0 }} dari {{ $programs->total() }} data
        </div>
        <nav>
          {{ $programs->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Nama Program</p>
            <p class="fw-medium" id="detailNamaProgram"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Kategori</p>
            <p class="fw-medium" id="detailKategori"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Anak Didik</p>
            <p class="fw-medium" id="detailAnakDidik"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Konsultan</p>
            <p class="fw-medium" id="detailKonsultan"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Mulai</p>
            <p class="fw-medium" id="detailTanggalMulai"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Selesai</p>
            <p class="fw-medium" id="detailTanggalSelesai"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Deskripsi</p>
            <p class="fw-medium" id="detailDeskripsi"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Target Pembelajaran</p>
            <p class="fw-medium" id="detailTargetPembelajaran"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Catatan Konsultan</p>
            <p class="fw-medium" id="detailCatatanKonsultan"></p>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Status Persetujuan</p>
            <p class="fw-medium" id="detailStatus"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function showDetail(btn) {
    const programId = btn.getAttribute('data-program-id');
    fetch(`/program/${programId}`)
      .then(response => response.json())
      .then(data => {
        const program = data.data;
        document.getElementById('detailNamaProgram').textContent = program.nama_program;
        document.getElementById('detailKategori').textContent = formatKategori(program.kategori);
        document.getElementById('detailAnakDidik').textContent = program.anak_didik?.nama || '-';
        document.getElementById('detailKonsultan').textContent = program.konsultan?.nama || '-';
        document.getElementById('detailTanggalMulai').textContent = program.tanggal_mulai ? formatDate(program.tanggal_mulai) : '-';
        document.getElementById('detailTanggalSelesai').textContent = program.tanggal_selesai ? formatDate(program.tanggal_selesai) : '-';
        document.getElementById('detailDeskripsi').textContent = program.deskripsi || '-';
        document.getElementById('detailTargetPembelajaran').textContent = program.target_pembelajaran || '-';
        document.getElementById('detailCatatanKonsultan').textContent = program.catatan_konsultan || '-';
        document.getElementById('detailStatus').innerHTML = program.is_approved ?
          '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Disetujui</span>' :
          '<span class="badge bg-warning"><i class="ri-time-line me-1"></i>Menunggu Approval</span>';
      });
  }

  function deleteData(btn) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
      const programId = btn.getAttribute('data-program-id');
      const row = document.getElementById(`row-${programId}`);

      fetch(`/program/${programId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            row.remove();
            alert(data.message);
          }
        });
    }
  }

  function formatDate(dateStr) {
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };
    return new Date(dateStr).toLocaleDateString('id-ID', options);
  }

  function formatKategori(kategori) {
    const labels = {
      'bina_diri': 'Bina Diri',
      'akademik': 'Akademik',
      'motorik': 'Motorik',
      'perilaku': 'Perilaku',
      'vokasi': 'Vokasi'
    };
    return labels[kategori] || kategori;
  }
</script>
@endsection