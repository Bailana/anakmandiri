@extends('layouts/contentNavbarLayout')
@section('title', 'Log Aktivitas')

@section('content')
<!-- Header -->
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Log Aktivitas</h4>
          <p class="text-body-secondary mb-0">Daftar aktivitas pengguna</p>
        </div>
        <div>
          <!-- Tombol export CSV responsif -->
          <a href="{{ route('activity.logs.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-danger d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;" title="Export CSV">
            <i class="ri-file-download-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('activity.logs.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-danger d-none d-sm-inline-flex align-items-center">
            <i class="ri-file-download-line me-2"></i>Export CSV
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- <style>
  @media (max-width: 767.98px) {
    .pagination {
      flex-wrap: nowrap !important;
      font-size: 0.95em;
      /* overflow-x dan white-space dihilangkan agar tidak scroll */
    }

    .pagination .page-link {
      padding: 0.25rem 0.5rem;
      min-width: 32px;
    }

    .pagination .page-item {
      min-width: 32px;
    }
  }
</style> -->

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
    <form method="GET" class="d-flex gap-2 align-items-end" action="{{ route('activity.logs') }}">
      <div style="min-width:250px;" class="flex-grow-1">
        <label class="form-label visually-hidden">Search</label>
        <input type="text" name="search" class="form-control" placeholder="Cari pengguna, aksi, model, deskripsi..." value="{{ request('search') }}">
      </div>

      <div style="max-width: 160px;">
        <label class="form-label visually-hidden">Range</label>
        <select name="range" id="rangeSelect" class="form-select">
          <option value="all" @if(request('range','all')=='all' ) selected @endif>All</option>
          <option value="today" @if(request('range')=='today' ) selected @endif>Today</option>
          <option value="7" @if(request('range')=='7' ) selected @endif>Last 7 days</option>
          <option value="30" @if(request('range')=='30' ) selected @endif>Last 30 days</option>
          <option value="custom" @if(request('range')=='custom' ) selected @endif>Custom Range</option>
        </select>
      </div>

      <div style="max-width: 160px;">
        <label class="form-label visually-hidden">From</label>
        <input type="date" name="from" id="fromInput" value="{{ request('from') }}" class="form-control" @if(request('range')!='custom' ) disabled @endif>
      </div>

      <div style="max-width: 160px;">
        <label class="form-label visually-hidden">To</label>
        <input type="date" name="to" id="toInput" value="{{ request('to') }}" class="form-control" @if(request('range')!='custom' ) disabled @endif>
      </div>

      <div style="max-width: 160px;">
        <label class="form-label visually-hidden">Role</label>
        <select name="role" class="form-select">
          <option value="all">All</option>
          @foreach($roles as $r)
          <option value="{{ $r }}" @if(request('role')==$r) selected @endif>{{ ucfirst($r) }}</option>
          @endforeach
        </select>
      </div>

      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('activity.logs') }}" class="btn btn-outline-secondary" title="Reset">
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
          <thead class="table-light">
            <tr>
              <th>Waktu</th>
              <th>Role</th>
              <th>Pengguna</th>
              <th>Deskripsi</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activities as $a)
            <tr>
              <td><small class="text-muted">{{ $a->created_at->diffForHumans() }}</small></td>
              @php
              $role = $a->user ? ucfirst($a->user->role) : '-';
              $roleColor = $a->user ? ($a->user->role === 'konsultan' ? 'warning' : ($a->user->role === 'terapis' ? 'info' : ($a->user->role === 'karyawan' ? 'secondary' : 'primary'))) : 'secondary';
              @endphp
              <td><span class="badge bg-{{ $roleColor }}">{{ $role }}</span></td>
              <td>{{ $a->user ? $a->user->name : '-' }}</td>
              <td>{{ $a->description }}</td>
              <td>{{ $a->ip_address }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted">Belum ada log</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary mb-2 mb-md-0">
          Menampilkan {{ $activities->firstItem() ?? 0 }} hingga {{ $activities->lastItem() ?? 0 }} dari {{ $activities->total() }} data
        </div>
        <div class="pagination-responsive flex-grow-1" style="min-width:0;">
          <nav>
            {!! $activities->links('pagination::bootstrap-4') !!}
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- <style>
  @media (max-width: 767.98px) {
    .pagination-responsive nav .pagination {
      flex-wrap: nowrap !important;
      overflow-x: auto;
      white-space: nowrap;
    }

    .pagination-responsive nav {
      width: 100%;
    }

    .pagination-responsive .overflow-auto {
      width: 100%;
      padding-bottom: 2px;
    }
  }
</style> -->

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const range = document.getElementById('rangeSelect');
    const from = document.getElementById('fromInput');
    const to = document.getElementById('toInput');

    function toggleCustom() {
      if (range.value === 'custom') {
        from.disabled = false;
        to.disabled = false;
      } else {
        from.disabled = true;
        to.disabled = true;
      }
    }

    range.addEventListener('change', toggleCustom);
    toggleCustom();
  });
</script>
@endpush
@endsection