@extends('layouts/contentNavbarLayout')

@section('title', 'Permintaan Akses PPI Anak Didik')

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
            <h4 class="mb-0">Permintaan Akses PPI Anak Didik</h4>
            <p class="text-body-secondary mb-0">Kelola permintaan akses PPI anak didik</p>
          </div>
          <a href="{{ url('/dashboard') }}" class="btn btn-secondary btn-sm"><i class="ri-arrow-left-line me-1"></i> Kembali</a>
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

<div class="row">
  <div class="col-12">
    @if($requests && $requests->count())
    <!-- Search & Filter -->
    <div class="row mb-4">
      <div class="col-12">
        <form method="GET" action="{{ url()->current() }}" class="d-flex gap-2 align-items-end">
          <div class="flex-grow-1">
            <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau pengaju..." value="{{ request('search') }}">
          </div>

          <!-- Filter Pengaju -->
          <select name="pengaju" class="form-select" style="max-width: 220px;">
            <option value="">Pengaju</option>
            @php
            if (isset($pengajuOptions) && is_array($pengajuOptions)) {
            $opts = $pengajuOptions;
            } else {
            $opts = [];
            foreach($requests->getCollection() as $r) {
            if (isset($r->requesterUser->id)) $opts[$r->requesterUser->id] = $r->requesterUser->name;
            }
            }
            @endphp
            @foreach($opts as $id => $name)
            <option value="{{ $id }}" {{ request('pengaju') == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
          </select>

          <!-- Filter Guru Fokus -->
          <select name="guru_fokus" class="form-select" style="max-width: 200px;">
            <option value="">Guru Fokus</option>
            @php
            if (isset($guruOptions) && is_array($guruOptions)) {
            $gopts = $guruOptions;
            } else {
            $gopts = [];
            foreach($requests->getCollection() as $r) {
            if (isset($r->anakDidik) && isset($r->anakDidik->guruFokus) && isset($r->anakDidik->guruFokus->id)) $gopts[$r->anakDidik->guruFokus->id] = $r->anakDidik->guruFokus->nama;
            }
            }
            @endphp
            @foreach($gopts as $id => $name)
            <option value="{{ $id }}" {{ request('guru_fokus') == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
          </select>

          <button type="submit" class="btn btn-outline-primary" title="Cari">
            <i class="ri-search-line"></i>
          </button>
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Reset">
            <i class="ri-refresh-line"></i>
          </a>
        </form>
      </div>
    </div>
    @php
    $items = $requests;
    if (isset($requests) && method_exists($requests, 'getCollection')) {
    $items = $requests->getCollection()->sortByDesc('created_at')->values();
    } else {
    $items = collect($requests)->sortByDesc('created_at')->values();
    }
    @endphp
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="approvalRequestsTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak</th>
              <th>Pengaju</th>
              <th>Alasan</th>
              <th>Diajukan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $index => $req)
            <tr data-req-id="{{ $req->id }}">
              <td>{{ ($requests->currentPage() - 1) * $requests->perPage() + $index + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ asset('assets/img/avatars/' . (($req->anakDidik->id ?? 0) % 4 + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div>
                    <p class="text-heading mb-0 fw-medium">{{ $req->anakDidik->nama ?? '-' }}</p>
                  </div>
                </div>
              </td>
              <td>
                <div>
                  <p class="mb-0">{{ $req->requesterUser->name ?? '-' }}</p>
                </div>
              </td>
              <td>{{ $req->reason ?? '-' }}</td>
              <td>{{ $req->created_at->diffForHumans() }}</td>
              <td class="text-capitalize">{{ $req->status }}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button class="btn btn-sm btn-icon btn-outline-secondary btn-edit-request" data-id="{{ $req->id }}" data-reason="{{ e($req->reason) }}" title="Edit"><i class="ri-edit-2-line"></i></button>
                  <button class="btn btn-sm btn-icon btn-outline-danger btn-delete-request" data-id="{{ $req->id }}" data-reason="{{ e($req->reason) }}" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada permintaan akses.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $requests->firstItem() ?? 0 }} hingga {{ $requests->lastItem() ?? 0 }} dari {{ $requests->total() }} data
        </div>
        <nav>
          {{ $requests->withQueryString()->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
    @else
    <div class="text-center text-muted p-4">Tidak ada permintaan akses.</div>
    @endif
  </div>
</div>

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    function csrfHeader() {
      return {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      };
    }

    // Open modal for edit/delete to capture reason
    const reasonModalHtml = `
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reasonModalLabel">Alasan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="modal-req-id" />
            <input type="hidden" id="modal-action" />
            <div class="mb-3">
              <label for="modal-reason" class="form-label">Alasan</label>
              <textarea id="modal-reason" class="form-control" rows="4" placeholder="Masukkan alasan..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="modal-submit">Kirim</button>
          </div>
        </div>
      </div>
    </div>`;

    // inject modal if not present
    if (!document.getElementById('reasonModal')) {
      const div = document.createElement('div');
      div.innerHTML = reasonModalHtml;
      document.body.appendChild(div.firstElementChild);
    }

    const bsModal = new bootstrap.Modal(document.getElementById('reasonModal'));

    function openReasonModal(id, action, currentReason) {
      document.getElementById('modal-req-id').value = id;
      document.getElementById('modal-action').value = action;
      document.getElementById('modal-reason').value = currentReason || '';
      document.getElementById('reasonModalLabel').textContent = action === 'delete' ? 'Alasan Penghapusan' : 'Ubah Alasan';
      bsModal.show();
    }

    // edit -> open modal
    document.querySelectorAll('.btn-edit-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const reason = this.getAttribute('data-reason') || '';
        if (!id) return;
        openReasonModal(id, 'edit', reason);
      });
    });

    // delete -> open modal to capture reason
    document.querySelectorAll('.btn-delete-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const reason = this.getAttribute('data-reason') || '';
        if (!id) return;
        openReasonModal(id, 'delete', reason);
      });
    });

    // modal submit handler
    document.getElementById('modal-submit').addEventListener('click', function() {
      const id = document.getElementById('modal-req-id').value;
      const action = document.getElementById('modal-action').value;
      const reason = document.getElementById('modal-reason').value;
      if (!id || !action) return;
      this.disabled = true;
      const url = `/guru-anak/approvals/${id}`;
      const opts = {
        method: action === 'delete' ? 'DELETE' : 'PUT',
        headers: Object.assign(csrfHeader(), {
          'Content-Type': 'application/json'
        }),
        body: JSON.stringify({
          reason: reason
        })
      };
      fetch(url, opts).then(r => r.json()).then(j => {
        if (j.success) {
          if (action === 'delete') {
            const tr = document.querySelector(`tr[data-req-id="${id}"]`);
            if (tr) tr.remove();
          } else {
            const tr = document.querySelector(`tr[data-req-id="${id}"]`);
            if (tr) {
              const td = tr.querySelector('td:nth-child(4)');
              if (td) td.textContent = reason || '-';
            }
          }
          bsModal.hide();
        } else {
          alert(j.message || 'Gagal memproses permintaan');
        }
        document.getElementById('modal-submit').disabled = false;
      }).catch(() => {
        alert('Terjadi kesalahan jaringan');
        document.getElementById('modal-submit').disabled = false;
      });
    });
  });
</script>
@endpush

@endsection