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
      <div style="min-width:200px;">
        <select name="guru_fokus" class="form-select">
          <option value="">Guru Fokus</option>
          @if(!empty($guruOptions))
          @foreach($guruOptions as $g)
          <option value="{{ $g->id }}" {{ (isset($guru_fokus) && $guru_fokus == $g->id) ? 'selected' : '' }}>{{ $g->nama }}</option>
          @endforeach
          @endif
        </select>
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
                  <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#riwayatPpiModal" data-anak-didik-id="{{ $anak->id }}" onclick="loadRiwayatPpi(this)" title="Riwayat PPI">
                    <i class="ri-history-line"></i>
                  </button>
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

<!-- Modal Riwayat PPI -->
<div class="modal fade" id="riwayatPpiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat PPI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatPpiList">
          <div class="text-center text-muted">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
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

    // Load riwayat PPI for an anak didik and render list
    window.loadRiwayatPpi = function(btn) {
      var listDiv = document.getElementById('riwayatPpiList');
      listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
      var anakId = btn.getAttribute('data-anak-didik-id');
      var currentUserId = @json(Auth::id());
      var canApprove = @json($canApprovePPI ?? false);
      fetch('/ppi/riwayat/' + anakId)
        .then(r => r.json())
        .then(res => {
          if (!res.success || !res.riwayat || res.riwayat.length === 0) {
            listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat PPI.</div>';
            return;
          }
          let html = '';
          res.riwayat.forEach(item => {
            html += `<div class="mb-2 p-2 border rounded d-flex justify-content-between align-items-center"><div><strong>${item.nama_program}</strong><div class="text-muted small">${item.created_at}${item.periode_mulai ? ' — ' + item.periode_mulai + (item.periode_selesai ? ' s/d ' + item.periode_selesai : '') : ''}</div><div class="text-muted small">${item.keterangan || ''}</div></div><div class="text-end">`;
            html += `<button class="btn btn-sm btn-outline-info me-1" onclick="viewPpiDetail(${item.id})" title="Lihat"><i class='ri-eye-line'></i></button>`;
            if (canApprove && item.status !== 'disetujui') {
              html += `<button class="btn btn-sm btn-success" onclick="approvePpi(${item.id})" title="Setujui"><i class='ri-check-line'></i></button>`;
            } else {
              html += `<span class="badge bg-secondary">${item.status || 'aktif'}</span>`;
            }
            html += `</div></div>`;
          });
          listDiv.innerHTML = html;
        }).catch(() => {
          listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        });
    }

    window.approvePpi = function(id) {
      if (!confirm('Setujui PPI ini?')) return;
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      fetch('/ppi/' + id + '/approve', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      }).then(r => r.json()).then(res => {
        if (res.success) {
          // refresh list
          var lastBtn = document.querySelector('button[data-bs-target="#riwayatPpiModal"]:focus') || document.querySelector('button[data-bs-target="#riwayatPpiModal"]');
          if (lastBtn) loadRiwayatPpi(lastBtn);
          alert(res.message || 'Berhasil disetujui');
        } else {
          alert(res.message || 'Gagal menyetujui');
        }
      }).catch(() => alert('Terjadi kesalahan jaringan'));
    }

    function viewPpiDetail(id) {
      // fallback: redirect to show page
      window.location.href = '/ppi/' + id;
    }
  });
</script>
@endpush