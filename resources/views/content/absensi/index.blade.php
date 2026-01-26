@extends('layouts.contentNavbarLayout')

@section('title', 'Absensi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Absensi Anak</h5>
    <div>
      <a href="{{ route('absensi.create') }}" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Absensi</a>
    </div>
  </div>
  <div class="card-body">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
      <table class="table table-sm" id="absensiTable">
        <thead>
          <tr>
            <th style="width:48px">No</th>
            <th>Nama Anak</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th style="width:140px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php $items = $absensis ?? $absensi ?? $records ?? []; @endphp
          @foreach($items as $i => $a)
          <tr data-id="{{ $a->id ?? '' }}">
            <td>{{ $i + 1 }}</td>
            <td>{{ $a->anak->nama ?? $a->nama ?? '-' }}</td>
            <td>{{ isset($a->tanggal) ? \Carbon\Carbon::parse($a->tanggal)->format('d M Y') : '-' }}</td>
            <td>
              @if(isset($a->status))
              @if($a->status === 'hadir')
              <span class="badge bg-success">Hadir</span>
              @elseif($a->status === 'izin')
              <span class="badge bg-warning">Izin</span>
              @else
              <span class="badge bg-secondary">{{ ucfirst($a->status) }}</span>
              @endif
              @else
              -
              @endif
            </td>
            <td>
              <a href="{{ isset($a->id) ? route('absensi.edit', $a->id) : '#' }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete-absensi" data-id="{{ $a->id ?? '' }}">Hapus</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-absensi').forEach(function(btn) {
      btn.addEventListener('click', async function() {
        const id = this.getAttribute('data-id');
        const tr = this.closest('tr');
        if (!id || !tr) return;
        if (!confirm('Hapus data absensi ini?')) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
          const res = await fetch(`/absensi/${id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            }
          });
          if (!res.ok) throw new Error('Gagal menghapus data');
          tr.remove();
          showToast('Absensi berhasil dihapus', 'success');
        } catch (err) {
          showToast('Gagal menghapus data', 'danger');
        }
      });
    });
  });

  if (typeof showToast !== 'function') {
    function showToast(message, type = 'success') {
      let toast = document.getElementById('customToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
        toast.style.zIndex = 9999;
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        document.body.appendChild(toast);
      } else {
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
      }
      toast.querySelector('.toast-body').textContent = message;
      var bsToast = bootstrap.Toast.getOrCreateInstance(toast, {
        delay: 2000
      });
      bsToast.show();
    }
  }
</script>
@endpush