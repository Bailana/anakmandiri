@extends('layouts/contentNavbarLayout')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Program Pembelajaran Individual (PPI)</h4>
            <p class="text-body-secondary mb-0">Tampilkan daftar anak didik. Klik untuk melihat program jika Anda memiliki akses.</p>
          </div>
          <div>
            <a href="{{ route('ppi.create') }}" class="btn btn-primary">
              <i class="ri-add-line me-2"></i>Tambah PPI
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Messages -->
<div id="ppi-alert-wrapper">@if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif</div>

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width:200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ $search ?? '' }}">
      </div>
      <div>
        <button type="submit" class="btn btn-outline-primary" title="Cari">
          <i class="ri-search-line"></i>
        </button>
      </div>
      <div>
        <a href="{{ route('ppi.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="ri-refresh-line"></i></a>
      </div>
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
              <th>Anak Didik</th>
              <th>Guru Fokus</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anakList as $index => $anak)
            <tr>
              <td>{{ ($anakList->currentPage() - 1) * $anakList->perPage() + $index + 1 }}</td>
              <td>
                <strong>{{ $anak->nama }}</strong><br>
                <small class="text-body-secondary">{{ $anak->nis ?? '-' }}</small>
              </td>
              <td>{{ $anak->guruFokus ? $anak->guruFokus->nama : '-' }}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  @if(isset($accessMap[$anak->id]) && $accessMap[$anak->id])
                  <a href="{{ route('ppi.show', $anak->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Lihat PPI" aria-label="Lihat PPI" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-eye-line"></i></a>
                  @else
                  <button class="btn btn-sm btn-icon btn-outline-danger btn-request-access" data-id="{{ $anak->id }}" title="Minta Akses" aria-label="Minta Akses" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-lock-line"></i></button>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-5">
                <div class="mb-3"><i class="ri-search-line" style="font-size:3rem;color:#ccc"></i></div>
                <p class="text-body-secondary mb-0">Tidak ada data anak ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $anakList->firstItem() ?? 0 }} hingga {{ $anakList->lastItem() ?? 0 }} dari {{ $anakList->total() }} data
        </div>
        <nav>
          {{ $anakList->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // init bootstrap tooltips for action buttons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    function showAlert(message, type = 'success') {
      const el = document.getElementById('ppi-alert');
      el.innerHTML = `<div class="alert alert-${type} alert-dismissible">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
      el.style.display = 'block';
    }

    document.querySelectorAll('.btn-request-access').forEach(btn => {
      btn.addEventListener('click', function() {
        const anakId = this.dataset.id;
        if (!confirm('Kirim permintaan akses ke guru fokus?')) return;
        const formData = new FormData();
        formData.append('anak_didik_id', anakId);
        // CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('ppi.request-access') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          },
          body: formData
        }).then(r => r.json()).then(j => {
          if (j.success) {
            showAlert(j.message || 'Permintaan berhasil dikirim');
          } else {
            showAlert(j.message || 'Terjadi kesalahan', 'danger');
          }
        }).catch(err => {
          showAlert('Terjadi kesalahan jaringan', 'danger');
        });
      });
    });
  });
</script>
@endpush