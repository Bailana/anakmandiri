@extends('layouts.contentNavbarLayout')

@section('title', 'Pasien Terapis')

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
            <h4 class="mb-0">Pasien Terapis</h4>
            <p class="text-body-secondary mb-0">Daftar pasien yang mengikuti terapis</p>
          </div>
          <div class="d-flex gap-2">
            @if(isset($user) && in_array($user->role, ['admin','terapis']))
            <a href="{{ route('terapis.pasien.create') }}" class="btn btn-primary">
              <i class="ri-add-line me-2"></i>Tambah Pasien Terapis
            </a>
            @endif
          </div>
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
    <!-- Search & Filter (like /program) -->
    <div class="row mb-4">
      <div class="col-12">
        <form method="GET" action="{{ route('terapis.pasien.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
          <div class="flex-grow-1" style="min-width:200px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ request('search') }}">
          </div>
          <select name="status" class="form-select" style="max-width:150px;">
            <option value="">Semua Status</option>
            <option value="aktif" {{ (isset($selectedStatus) && $selectedStatus === 'aktif') ? 'selected' : '' }}>Aktif</option>
            <option value="non-aktif" {{ (isset($selectedStatus) && $selectedStatus === 'non-aktif') ? 'selected' : '' }}>Non Aktif</option>
          </select>
          @if(isset($user) && $user->role === 'admin')
          <select name="user_id" class="form-select" style="max-width:200px;">
            <option value="">-- Semua Terapis --</option>
            @foreach($therapists as $t)
            <option value="{{ $t->id }}" {{ request('user_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
          </select>
          @endif
          <button type="submit" class="btn btn-outline-primary" title="Filter">
            <i class="ri-search-line"></i>
          </button>
          <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary" title="Reset">
            <i class="ri-refresh-line"></i>
          </a>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="patientsTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Jenis Terapi</th>
              <th>Terapis</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assignments as $index => $assign)
            <tr>
              <td>{{ (method_exists($assignments, 'currentPage') ? ($assignments->currentPage() - 1) * $assignments->perPage() : 0) + $index + 1 }}</td>
              <td>
                @if($assign->anakDidik)
                <p class="text-heading mb-0 fw-medium"><a href="{{ route('anak-didik.show', $assign->anakDidik->id) }}" class="text-decoration-none text-reset">{{ $assign->anakDidik->nama }}</a></p>
                @else
                -
                @endif
              </td>
              <td>{{ $assign->jenis_terapi ?? '-' }}</td>
              <td>{{ $assign->user->name ?? '-' }}</td>
              <td>
                @if(isset($assign->status) && $assign->status === 'aktif')
                <span class="badge bg-success">Aktif</span>
                @elseif(isset($assign->status) && $assign->status === 'non-aktif')
                <span class="badge bg-danger">Non Aktif</span>
                @else
                <span class="badge bg-secondary">{{ $assign->status ?? '-' }}</span>
                @endif
              </td>
              <td>
                @if(isset($user) && in_array($user->role, ['admin','terapis']))
                <button type="button" class="btn btn-icon btn-sm btn-outline-info me-1" title="Lihat Jadwal" onclick="showTherapySchedules(this)" data-anak-id="{{ $assign->anak_didik_id }}">
                  <i class="ri-eye-line"></i>
                </button>
                <a class="btn btn-icon btn-sm btn-outline-warning" href="{{ route('terapis.pasien.edit', $assign->id) }}" title="Edit">
                  <i class="ri-edit-line"></i>
                </a>
                <form action="{{ route('terapis.pasien.destroy', $assign->id) }}" method="POST" style="display:inline-block; margin-left:6px;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus penugasan ini?')">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </form>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6">
                <div class="alert alert-warning mb-0" role="alert">
                  <i class="ri-alert-line me-2"></i>Tidak ada pasien.
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- Modal: Jadwal Terapi -->
      <div class="modal fade" id="therapyScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Jadwal Terapi</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="therapyScheduleBody">
                <div class="text-center text-muted">Memuat jadwal...</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ method_exists($assignments, 'firstItem') ? ($assignments->firstItem() ?? 0) : $assignments->count() }} hingga {{ method_exists($assignments, 'lastItem') ? ($assignments->lastItem() ?? 0) : $assignments->count() }} dari {{ method_exists($assignments, 'total') ? $assignments->total() : $assignments->count() }} data
        </div>
        <nav>
          @if(method_exists($assignments, 'links'))
          {{ $assignments->links('pagination::bootstrap-4') }}
          @endif
        </nav>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
  async function showTherapySchedules(btn) {
    const anakId = btn.getAttribute('data-anak-id');
    if (!anakId) return;
    const modalEl = document.getElementById('therapyScheduleModal');
    const body = document.getElementById('therapyScheduleBody');
    body.innerHTML = '<div class="text-center text-muted">Memuat jadwal...</div>';
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    try {
      const res = await fetch(`/terapis/pasien/${anakId}/jadwal`);
      const json = await res.json();
      if (!json.success) {
        body.innerHTML = '<div class="text-danger">Gagal memuat jadwal.</div>';
        return;
      }
      const data = json.data;
      if (!data || data.length === 0) {
        body.innerHTML = '<div class="text-muted">Belum ada jadwal untuk anak ini.</div>';
        return;
      }
      let html = '';
      data.forEach(a => {
        html += `<div class="mb-3"><h6 class="mb-1">${a.jenis_terapi || '-'} <small class="text-muted">(${a.terapis_nama || '-'})</small></h6>`;
        if (!a.schedules || a.schedules.length === 0) {
          html += '<div class="text-muted">Tidak ada jadwal.</div>';
        } else {
          html += '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Tanggal</th><th>Jam</th></tr></thead><tbody>';
          a.schedules.forEach(s => {
            html += `<tr><td>${s.tanggal_mulai || '-'}</td><td>${s.jam_mulai || '-'}</td></tr>`;
          });
          html += '</tbody></table></div>';
        }
        html += '</div>';
      });
      body.innerHTML = html;
    } catch (e) {
      body.innerHTML = '<div class="text-danger">Terjadi kesalahan saat memuat jadwal.</div>';
    }
  }
</script>
@endsection