@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Karyawan')

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
            <h4 class="mb-0">Daftar Karyawan</h4>
            <p class="text-body-secondary mb-0">Kelola data karyawan</p>
          </div>
          <a href="{{ route('karyawan.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('karyawan.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
            <i class="ri-add-line me-2"></i>Tambah Karyawan
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
    <form method="GET" action="{{ route('karyawan.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1">
        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, atau email..." value="{{ request('search') }}">
      </div>
      <!-- Filter Posisi (desktop) -->
      <select name="posisi" class="form-select d-none d-sm-block" style="max-width: 150px;">
        <option value="">Posisi</option>
        @foreach($posisiOptions as $posisi)
        <option value="{{ $posisi }}" {{ request('posisi') === $posisi ? 'selected' : '' }}>{{ $posisi }}</option>
        @endforeach
      </select>
      <!-- Filter Status (desktop) -->
      <select name="status_kepegawaian" class="form-select d-none d-sm-block" style="max-width: 150px;">
        <option value="">Status</option>
        <option value="tetap" {{ request('status_kepegawaian') === 'tetap' ? 'selected' : '' }}>Tetap</option>
        <option value="training" {{ request('status_kepegawaian') === 'training' ? 'selected' : '' }}>Training</option>
        <option value="nonaktif" {{ request('status_kepegawaian') === 'nonaktif' ? 'selected' : '' }}>Non Aktif</option>
      </select>
      <!-- Mobile: filter posisi & status side by side -->
      <div class="d-flex flex-row gap-2 w-100 d-flex d-sm-none">
        <select name="posisi" class="form-select" style="min-width:120px;">
          <option value="">Posisi</option>
          @foreach($posisiOptions as $posisi)
          <option value="{{ $posisi }}" {{ request('posisi') === $posisi ? 'selected' : '' }}>{{ $posisi }}</option>
          @endforeach
        </select>
        <select name="status_kepegawaian" class="form-select" style="min-width:120px;">
          <option value="">Status</option>
          <option value="tetap" {{ request('status_kepegawaian') === 'tetap' ? 'selected' : '' }}>Tetap</option>
          <option value="training" {{ request('status_kepegawaian') === 'training' ? 'selected' : '' }}>Training</option>
          <option value="nonaktif" {{ request('status_kepegawaian') === 'nonaktif' ? 'selected' : '' }}>Non Aktif</option>
        </select>
      </div>
      <!-- Mobile: tombol search & reset side by side -->
      <div class="d-flex flex-row gap-2 w-100 d-flex d-sm-none">
        <button type="submit" class="btn btn-outline-primary w-50 d-inline-flex align-items-center justify-content-center p-0" style="height:44px;border-radius:12px;min-height:44px;">
          <i class="ri-search-line" style="font-size:1.3em;"></i>
        </button>
        <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary w-50 d-inline-flex align-items-center justify-content-center p-0" style="height:44px;border-radius:12px;min-height:44px;">
          <i class="ri-refresh-line" style="font-size:1.3em;"></i>
        </a>
      </div>
      <!-- Desktop: tombol search & reset -->
      <button type="submit" class="btn btn-outline-primary d-none d-sm-inline-flex" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-flex" title="Reset">
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
        <table class="table table-hover" id="karyawanTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama</th>
              <th>NIP</th>
              <th>Posisi</th>
              {{-- <th>Departemen</th> --}}
              <th>Email</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($karyawans as $index => $karyawan)
            <tr id="row-{{ $karyawan->id }}">
              <td>{{ ($karyawans->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('assets/img/avatars/' . (($karyawan->id % 4) + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;aspect-ratio:1/1;" />
                  </div>
                  <div>
                    <p class="text-heading mb-0 fw-medium">{{ $karyawan->nama }}</p>
                  </div>
                </div>
              </td>
              <td>{{ $karyawan->nip ?? '-' }}</td>
              <td>{{ $karyawan->posisi ?? '-' }}</td>
              {{-- <td>{{ $karyawan->departemen ?? '-' }}</td> --}}
              <td>{{ $karyawan->email ?? '-' }}</td>
              <td>
                @if($karyawan->status_kepegawaian === 'nonaktif')
                <span class="badge bg-label-danger">Non Aktif</span>
                @else
                <span class="badge bg-label-{{ $karyawan->status_kepegawaian === 'tetap' ? 'success' : ($karyawan->status_kepegawaian === 'training' ? 'warning' : 'info') }}">
                  {{ ucfirst($karyawan->status_kepegawaian ?? '-') }}
                </span>
                @endif
              </td>
              <td>
                <!-- Tombol aksi untuk desktop -->
                <div class="d-none d-md-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#detailModal"
                    data-karyawan-id="{{ $karyawan->id }}"
                    data-bs-title="Detail Karyawan"
                    title="Lihat Detail"
                    onclick="showDetail(this)">
                    <i class="ri-eye-line"></i>
                  </button>
                  <a
                    href="{{ route('karyawan.edit', $karyawan->id) }}"
                    class="btn btn-sm btn-icon btn-outline-warning"
                    title="Edit Data">
                    <i class="ri-edit-line"></i>
                  </a>
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-danger"
                    data-karyawan-id="{{ $karyawan->id }}"
                    title="Hapus Data"
                    onclick="deleteData(this)">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
                <!-- Tombol titik tiga untuk mobile -->
                <div class="dropdown d-md-none">
                  <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="showDetailDropdown({{ $karyawan->id }});return false;"><i class="ri-eye-line me-1"></i> Lihat</a></li>
                    <li><a class="dropdown-item" href="{{ route('karyawan.edit', $karyawan->id) }}"><i class="ri-edit-line me-1"></i> Edit</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteDataDropdown(this, {{ $karyawan->id }});return false;"><i class="ri-delete-bin-line me-1"></i> Hapus</a></li>
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada data karyawan ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
        <style>
          @media (max-width: 767.98px) {
            .table .d-md-flex {
              display: none !important;
            }

            .table .d-md-none {
              display: block !important;
            }
          }

          @media (min-width: 768px) {
            .table .d-md-flex {
              display: flex !important;
            }

            .table .d-md-none {
              display: none !important;
            }
          }
        </style>
        <script>
          // Agar tombol hapus di dropdown mobile tetap bisa pakai fungsi hapus yang sama
          function deleteDataDropdown(el, karyawanId) {
            if (!confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) return;
            // Buat dummy button agar deleteData tetap dapat parameter button
            var dummyBtn = document.createElement('button');
            dummyBtn.setAttribute('data-karyawan-id', karyawanId);
            deleteData(dummyBtn);
          }
          // Agar tombol lihat di dropdown mobile tetap bisa pakai fungsi showDetail yang sama
          function showDetailDropdown(karyawanId) {
            var dummyBtn = document.createElement('button');
            dummyBtn.setAttribute('data-karyawan-id', karyawanId);
            showDetail(dummyBtn);
          }
        </script>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center pagination-footer-fix">
        <style>
          /* Pastikan pagination dan info tetap satu baris di mobile */
          .pagination-footer-fix {
            flex-wrap: nowrap !important;
            gap: 0.5rem;
          }

          .pagination-footer-fix>div,
          .pagination-footer-fix>nav {
            min-width: 0;
            max-width: 100%;
          }

          .pagination-footer-fix nav {
            flex-shrink: 1;
            flex-grow: 0;
          }

          @media (max-width: 767.98px) {
            .pagination-footer-fix {
              flex-direction: row !important;
              align-items: center !important;
              flex-wrap: nowrap !important;
            }

            .pagination-footer-fix>div,
            .pagination-footer-fix>nav {
              width: auto !important;
              max-width: 100%;
            }

            .pagination-footer-fix nav ul.pagination {
              flex-wrap: nowrap !important;
            }
          }
        </style>
        <div class="text-body-secondary">
          Menampilkan {{ $karyawans->firstItem() ?? 0 }} hingga {{ $karyawans->lastItem() ?? 0 }} dari {{ $karyawans->total() }} data
        </div>
        <nav>
          {{ $karyawans->links('pagination::bootstrap-4') }}
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
        <h5 class="modal-title" id="detailModalLabel">Detail Karyawan</h5>
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
            <p class="text-body-secondary text-sm mb-1">Nama Karyawan</p>
            <p class="fw-medium" id="detailNama"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">NIP</p>
            <p class="fw-medium" id="detailNip"></p>
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

        <!-- INFORMASI PEKERJAAN -->
        <h6 class="mb-3 text-primary"><i class="ri-briefcase-line me-2"></i>Informasi Pekerjaan</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Posisi</p>
            <p class="fw-medium" id="detailPosisi">-</p>
          </div>
          <div class="col-md-6">
            {{-- <p class="text-body-secondary text-sm mb-1">Departemen</p>
            <p class="fw-medium" id="detailDepartemen">-</p> --}}
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Status Kepegawaian</p>
            <p class="fw-medium"><span id="detailStatus" class="badge"></span></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Bergabung</p>
            <p class="fw-medium" id="detailTgBergabung">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Keahlian</p>
            <p class="fw-medium" id="detailKeahlian">-</p>
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


@push('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Format Date
  function formatDate(dateString) {
    if (!dateString) return '-';
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  }

  // Show Detail
  function showDetail(button) {
    const karyawanId = button.getAttribute('data-karyawan-id');

    fetch(`/karyawan/${karyawanId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        const karyawan = data.data;

        // Basic Info
        document.getElementById('detailNama').textContent = karyawan.nama || '-';
        document.getElementById('detailNip').textContent = karyawan.nip || '-';
        document.getElementById('detailTl').textContent = formatDate(karyawan.tanggal_lahir);
        document.getElementById('detailEmail').textContent = karyawan.email || '-';
        document.getElementById('detailTlp').textContent = karyawan.no_telepon || '-';
        document.getElementById('detailAlamat').textContent = karyawan.alamat || '-';

        // Jenis Kelamin Badge
        const jkBadge = document.getElementById('detailJk');
        jkBadge.textContent = (karyawan.jenis_kelamin || '').charAt(0).toUpperCase() + (karyawan.jenis_kelamin || '').slice(1);
        jkBadge.className = karyawan.jenis_kelamin === 'laki-laki' ? 'badge bg-label-info' : 'badge bg-label-warning';

        // Work Info
        document.getElementById('detailPosisi').textContent = karyawan.posisi || '-';
        // document.getElementById('detailDepartemen').textContent = karyawan.departemen || '-';
        document.getElementById('detailTgBergabung').textContent = formatDate(karyawan.tanggal_bergabung);
        document.getElementById('detailKeahlian').textContent = karyawan.keahlian || '-';

        // Status Badge
        const statusBadge = document.getElementById('detailStatus');
        statusBadge.textContent = (karyawan.status_kepegawaian || '-').charAt(0).toUpperCase() + (karyawan.status_kepegawaian || '').slice(1);
        statusBadge.className = karyawan.status_kepegawaian === 'tetap' ? 'badge bg-label-success' : (karyawan.status_kepegawaian === 'training' ? 'badge bg-label-warning' : 'badge bg-label-info');

        // Education
        document.getElementById('detailPendidikan').textContent = karyawan.pendidikan_terakhir || '-';
        document.getElementById('detailInstitusi').textContent = karyawan.institusi_pendidikan || '-';

        // Set Avatar
        const avatarNum = (karyawanId % 4) + 1;
        const avatarPath = '/assets/img/avatars/' + avatarNum + '.svg';
        document.getElementById('detailAvatar').src = avatarPath;
        console.log('Avatar path:', avatarPath); // Debug
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengambil data detail');
      });
  }

  // Delete Data
  function deleteData(button) {
    const karyawanId = button.getAttribute('data-karyawan-id');
    console.log('Delete karyawanId:', karyawanId);
    if (!karyawanId) {
      alert('ID karyawan tidak ditemukan. Tidak dapat menghapus.');
      return;
    }
    if (confirm('Apakah Anda yakin ingin menghapus karyawan ini?')) {
      // Ambil CSRF token dari meta tag
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      fetch(`/karyawan/${karyawanId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin'
        })
        .then(response => {
          if (response.ok) {
            // remove table row without full reload
            const row = document.getElementById('row-' + karyawanId);
            if (row) row.remove();

            // use global showToast if available, otherwise show alert as fallback
            if (typeof window.showToast === 'function') {
              window.showToast('Data karyawan berhasil dihapus', 'success');
            } else if (window.bootstrap && typeof window.bootstrap.Toast === 'function') {
              // create a temporary bootstrap toast
              const toast = document.createElement('div');
              toast.className = 'toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-4';
              toast.style.zIndex = 9999;
              toast.innerHTML = '<div class="d-flex"><div class="toast-body">Data karyawan berhasil dihapus</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
              document.body.appendChild(toast);
              const bsToast = window.bootstrap.Toast.getOrCreateInstance(toast, {
                delay: 2000
              });
              bsToast.show();
              setTimeout(() => toast.remove(), 2500);
            } else {
              alert('Data karyawan berhasil dihapus');
            }
          } else {
            return response.json().then(data => {
              throw new Error(data.message || 'Gagal menghapus data');
            });
          }
        })
        .catch(error => {
          alert(error.message);
        });
    }
  }
</script>
@endpush