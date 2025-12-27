@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Konsultan')

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
            <h4 class="mb-0">Daftar Konsultan</h4>
            <p class="text-body-secondary mb-0">Kelola data konsultan</p>
          </div>
          <a href="{{ route('konsultan.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Konsultan
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

<!-- Toasts are handled via shared `showToast` helper -->

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('konsultan.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 250px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIK, atau email..." value="{{ request('search') }}">
      </div>

      <!-- Filter Jenis Kelamin -->
      <select name="jenis_kelamin" class="form-select" style="max-width: 150px;">
        <option value="">Jenis Kelamin</option>
        <option value="laki-laki" {{ request('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
        <option value="perempuan" {{ request('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
      </select>

      <!-- Filter Spesialisasi -->
      <select name="spesialisasi" class="form-select" style="max-width: 150px;">
        <option value="">Spesialisasi</option>
        @foreach($spesialisasiOptions as $spesialisasi)
        <option value="{{ $spesialisasi }}" {{ request('spesialisasi') === $spesialisasi ? 'selected' : '' }}>{{ $spesialisasi }}</option>
        @endforeach
      </select>

      <!-- Filter Status Hubungan -->
      <select name="status_hubungan" class="form-select" style="max-width: 150px;">
        <option value="">Status</option>
        <option value="aktif" {{ request('status_hubungan') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="non-aktif" {{ request('status_hubungan') === 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
      </select>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('konsultan.index') }}" class="btn btn-outline-secondary" title="Reset">
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
        <table class="table table-hover" id="konsultanTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama</th>
              <th>Spesialisasi</th>
              <th>Email</th>
              <th>Pengalaman</th>
              <th>Sertifikasi</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($konsultans as $index => $konsultan)
            <tr id="row-{{ $konsultan->id }}">
              <td>{{ ($konsultans->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('assets/img/avatars/' . (($konsultan->id % 4) + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;aspect-ratio:1/1;" />
                  </div>
                  <div>
                    <p class="text-heading mb-0 fw-medium">{{ $konsultan->nama }}</p>
                  </div>
                </div>
              </td>
              <td>{{ $konsultan->spesialisasi ?? '-' }}</td>
              <td>{{ $konsultan->email ?? '-' }}</td>
              <td>{{ $konsultan->pengalaman_tahun ? $konsultan->pengalaman_tahun . ' tahun' : '-' }}</td>
              <td>{{ $konsultan->sertifikasi ?? '-' }}</td>
              <td>
                <span class="badge bg-label-{{ $konsultan->status_hubungan === 'aktif' ? 'success' : 'danger' }}">
                  {{ ucfirst($konsultan->status_hubungan ?? '-') }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-primary btn-detail"
                    data-bs-toggle="modal"
                    data-bs-target="#detailModal"
                    data-konsultan-id="{{ $konsultan->id }}"
                    data-bs-title="Detail Konsultan"
                    title="Lihat Detail">
                    <i class="ri-eye-line"></i>
                  </button>
                  <a
                    href="{{ route('konsultan.edit', $konsultan->id) }}"
                    class="btn btn-sm btn-icon btn-outline-warning"
                    title="Edit Data">
                    <i class="ri-edit-line"></i>
                  </a>
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-danger btn-delete"
                    data-konsultan-id="{{ $konsultan->id }}"
                    title="Hapus Data">
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
                <p class="text-body-secondary mb-0">Tidak ada data konsultan ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $konsultans->firstItem() ?? 0 }} hingga {{ $konsultans->lastItem() ?? 0 }} dari {{ $konsultans->total() }} data
        </div>
        <nav>
          {{ $konsultans->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Konsultan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div class="row mb-3">
          <div class="col-12 text-center mb-3">
            <div class="avatar" style="width: 80px; height: 80px; margin: 0 auto;">
              <img id="detailAvatar" src="" alt="Avatar" class="rounded-circle w-100 h-100" />
            </div>
          </div>
        </div>

        <!-- INFORMASI DASAR -->
        <h6 class="mb-3 text-primary"><i class="ri-information-line me-2"></i>Informasi Dasar</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Nama Konsultan</p>
            <p class="fw-medium" id="detailNama"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">NIK</p>
            <p class="fw-medium" id="detailNik"></p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Jenis Kelamin</p>
            <p class="fw-medium"><span id="detailJk" class="badge"></span></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Lahir</p>
            <p class="fw-medium" id="detailTl"></p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Email</p>
            <p class="fw-medium" id="detailEmail">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">No Telepon</p>
            <p class="fw-medium" id="detailTlp">-</p>
          </div>
        </div>

        <!-- INFORMASI PROFESIONAL -->
        <h6 class="mb-3 text-primary"><i class="ri-briefcase-line me-2"></i>Informasi Profesional</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Spesialisasi</p>
            <p class="fw-medium" id="detailSpesialisasi">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Pengalaman</p>
            <p class="fw-medium" id="detailPengalaman">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Bidang Keahlian</p>
            <p class="fw-medium" id="detailKeahlian">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Sertifikasi</p>
            <p class="fw-medium" id="detailSertifikasi">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Status Hubungan</p>
            <p class="fw-medium"><span id="detailStatus" class="badge"></span></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Registrasi</p>
            <p class="fw-medium" id="detailTgRegistrasi">-</p>
          </div>
        </div>

        <!-- INFORMASI PENDIDIKAN -->
        <h6 class="mb-3 text-primary"><i class="ri-graduation-cap-line me-2"></i>Informasi Pendidikan</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Pendidikan Terakhir</p>
            <p class="fw-medium" id="detailPendidikan">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Institusi</p>
            <p class="fw-medium" id="detailInstitusi">-</p>
          </div>
        </div>

        <!-- INFORMASI KONTAK -->
        <h6 class="mb-3 text-primary"><i class="ri-map-pin-line me-2"></i>Informasi Kontak</h6>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Alamat</p>
            <p class="fw-medium" id="detailAlamat">-</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script>
  window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Format Date
  window.formatDate = function(dateString) {
    if (!dateString) return '-';
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  }

  // Show Detail
  window.showDetail = function(button) {
    const konsultanId = button.getAttribute('data-konsultan-id');

    fetch(`/konsultan/${konsultanId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.text())
      .then(text => {
        let data = null;
        try {
          data = JSON.parse(text);
        } catch (err) {
          console.warn('detail response is not valid JSON, response text:', text);
          // show raw response in modal for debugging
          console.debug('Raw response text:', text);
          throw new Error('Invalid JSON response');
        }

        console.debug('detail response', data);
        const konsultan = (data && data.data) ? data.data : data;

        // Basic Info
        document.getElementById('detailNama').textContent = (konsultan && konsultan.nama) ? konsultan.nama : '-';
        document.getElementById('detailNik').textContent = konsultan.nik || '-';
        document.getElementById('detailTl').textContent = formatDate(konsultan.tanggal_lahir);
        document.getElementById('detailEmail').textContent = konsultan.email || '-';
        document.getElementById('detailTlp').textContent = konsultan.no_telepon || '-';
        document.getElementById('detailAlamat').textContent = konsultan.alamat || '-';

        // Jenis Kelamin Badge
        const jkBadge = document.getElementById('detailJk');
        const jkText = (konsultan && konsultan.jenis_kelamin) ? (konsultan.jenis_kelamin.charAt(0).toUpperCase() + konsultan.jenis_kelamin.slice(1)) : '-';
        jkBadge.textContent = jkText;
        jkBadge.className = (konsultan && konsultan.jenis_kelamin === 'laki-laki') ? 'badge bg-label-info' : ((konsultan && konsultan.jenis_kelamin === 'perempuan') ? 'badge bg-label-warning' : 'badge bg-secondary');

        // Professional Info
        document.getElementById('detailSpesialisasi').textContent = konsultan.spesialisasi || '-';
        document.getElementById('detailPengalaman').textContent = konsultan.pengalaman_tahun ? konsultan.pengalaman_tahun + ' tahun' : '-';
        document.getElementById('detailKeahlian').textContent = konsultan.bidang_keahlian || '-';
        document.getElementById('detailSertifikasi').textContent = konsultan.sertifikasi || '-';
        document.getElementById('detailTgRegistrasi').textContent = formatDate(konsultan.tanggal_registrasi);

        // Status Badge
        const statusBadge = document.getElementById('detailStatus');
        const statusText = (konsultan && konsultan.status_hubungan) ? (konsultan.status_hubungan.charAt(0).toUpperCase() + konsultan.status_hubungan.slice(1)) : '-';
        statusBadge.textContent = statusText;
        statusBadge.className = (konsultan && konsultan.status_hubungan === 'aktif') ? 'badge bg-label-success' : ((konsultan && konsultan.status_hubungan === 'non-aktif') ? 'badge bg-label-danger' : 'badge bg-secondary');

        // Education
        document.getElementById('detailPendidikan').textContent = konsultan.pendidikan_terakhir || '-';
        document.getElementById('detailInstitusi').textContent = konsultan.institusi_pendidikan || '-';

        // Set Avatar
        const avatarNum = (konsultanId % 4) + 1;
        const avatarPath = (konsultan && konsultan.avatar_path) ? konsultan.avatar_path : '/assets/img/avatars/' + avatarNum + '.svg';
        document.getElementById('detailAvatar').src = avatarPath;
        console.debug('Avatar path:', avatarPath); // Debug
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengambil data detail');
      });
  }

  // Delete Data
  window.deleteData = function(button) {
    const konsultanId = button.getAttribute('data-konsultan-id');

    if (confirm('Apakah Anda yakin ingin menghapus konsultan ini?')) {
      // Some servers or environments block DELETE; send POST with _method override
      fetch(`/konsultan/${konsultanId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams({
            _method: 'DELETE',
            _token: window.csrfToken
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // remove table row
            const row = document.getElementById('row-' + konsultanId);
            if (row) row.remove();

            // show success toast (use shared helper)
            window.showToast && window.showToast('Data konsultan berhasil dihapus', 'success');
          } else {
            alert(data.message || 'Gagal menghapus data');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan');
        });
    }
  }

  // Note: explicit button bindings are used below; removed global delegation to avoid double-calls

  // Ensure buttons have explicit listeners (in case delegation misses due to event ordering)
  function bindDetailButtons() {
    const detailButtons = document.querySelectorAll('.btn-detail');
    detailButtons.forEach(btn => {
      // avoid double-binding
      if (!btn.__detailBound) {
        btn.addEventListener('click', function(ev) {
          try {
            if (typeof window.showDetail === 'function') {
              window.showDetail(btn);
            } else {
              console.error('showDetail not defined on click');
            }
          } catch (err) {
            console.error('Error in detail button handler', err);
          }
        });
        btn.__detailBound = true;
      }
    });

    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(btn => {
      if (!btn.__deleteBound) {
        btn.addEventListener('click', function(ev) {
          try {
            if (typeof window.deleteData === 'function') {
              window.deleteData(btn);
            } else {
              console.error('deleteData not defined on click');
            }
          } catch (err) {
            console.error('Error in delete button handler', err);
          }
        });
        btn.__deleteBound = true;
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindDetailButtons);
  } else {
    bindDetailButtons();
  }

  // Generic toast helper (matches other pages)
  window.showToast = function(message, type = 'success') {
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
    var bsToast = window.bootstrap && typeof window.bootstrap.Toast === 'function' ? window.bootstrap.Toast.getOrCreateInstance(toast, {
      delay: 2000
    }) : null;
    if (bsToast) bsToast.show();
    else {
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 2000);
    }
  }
</script>
@endpush