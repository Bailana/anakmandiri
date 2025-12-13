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
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ asset('assets/img/avatars/' . (($konsultan->id % 4) + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" />
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
                    class="btn btn-sm btn-icon btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#detailModal"
                    data-konsultan-id="{{ $konsultan->id }}"
                    data-bs-title="Detail Konsultan"
                    title="Lihat Detail"
                    onclick="showDetail(this)">
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
                    class="btn btn-sm btn-icon btn-outline-danger"
                    data-konsultan-id="{{ $konsultan->id }}"
                    title="Hapus Data"
                    onclick="deleteData(this)">
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

@section('page-script')
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
    const konsultanId = button.getAttribute('data-konsultan-id');

    fetch(`/konsultan/${konsultanId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        const konsultan = data.data;

        // Basic Info
        document.getElementById('detailNama').textContent = konsultan.nama || '-';
        document.getElementById('detailNik').textContent = konsultan.nik || '-';
        document.getElementById('detailTl').textContent = formatDate(konsultan.tanggal_lahir);
        document.getElementById('detailEmail').textContent = konsultan.email || '-';
        document.getElementById('detailTlp').textContent = konsultan.no_telepon || '-';
        document.getElementById('detailAlamat').textContent = konsultan.alamat || '-';

        // Jenis Kelamin Badge
        const jkBadge = document.getElementById('detailJk');
        jkBadge.textContent = (konsultan.jenis_kelamin || '').charAt(0).toUpperCase() + (konsultan.jenis_kelamin || '').slice(1);
        jkBadge.className = konsultan.jenis_kelamin === 'laki-laki' ? 'badge bg-label-info' : 'badge bg-label-warning';

        // Professional Info
        document.getElementById('detailSpesialisasi').textContent = konsultan.spesialisasi || '-';
        document.getElementById('detailPengalaman').textContent = konsultan.pengalaman_tahun ? konsultan.pengalaman_tahun + ' tahun' : '-';
        document.getElementById('detailKeahlian').textContent = konsultan.bidang_keahlian || '-';
        document.getElementById('detailSertifikasi').textContent = konsultan.sertifikasi || '-';
        document.getElementById('detailTgRegistrasi').textContent = formatDate(konsultan.tanggal_registrasi);

        // Status Badge
        const statusBadge = document.getElementById('detailStatus');
        statusBadge.textContent = (konsultan.status_hubungan || '-').charAt(0).toUpperCase() + (konsultan.status_hubungan || '').slice(1);
        statusBadge.className = konsultan.status_hubungan === 'aktif' ? 'badge bg-label-success' : 'badge bg-label-danger';

        // Education
        document.getElementById('detailPendidikan').textContent = konsultan.pendidikan_terakhir || '-';
        document.getElementById('detailInstitusi').textContent = konsultan.institusi_pendidikan || '-';

        // Set Avatar
        const avatarNum = (konsultanId % 4) + 1;
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
    const konsultanId = button.getAttribute('data-konsultan-id');

    if (confirm('Apakah Anda yakin ingin menghapus konsultan ini?')) {
      fetch(`/konsultan/${konsultanId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan');
        });
    }
  }
</script>
@endsection