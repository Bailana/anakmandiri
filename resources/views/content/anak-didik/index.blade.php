@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Anak Didik')

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
            <h4 class="mb-0">Daftar Anak Didik</h4>
            <p class="text-body-secondary mb-0">Kelola data anak didik</p>
          </div>
          @if(auth()->user() && auth()->user()->role === 'admin')
          <a href="{{ route('anak-didik.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Anak Didik
          </a>
          @endif
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
    <form method="GET" action="{{ route('anak-didik.index') }}" class="d-flex gap-2 align-items-end">
      <!-- Search Field -->
      <div class="flex-grow-1">
        <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
      </div>

      <!-- Filter Jenis Kelamin -->
      <select name="jenis_kelamin" class="form-select" style="max-width: 150px;">
        <option value="">Jenis Kelamin</option>
        <option value="laki-laki" {{ request('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
        <option value="perempuan" {{ request('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
      </select>

      <!-- Filter Guru Fokus -->
      <select name="guru_fokus" class="form-select" style="max-width: 200px;">
        <option value="">Guru Fokus</option>
        @foreach($guruOptions as $id => $name)
        <option value="{{ $id }}" {{ request('guru_fokus') == $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('anak-didik.index') }}" class="btn btn-outline-secondary" title="Reset">
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
        <table class="table table-hover" id="anakDidikTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama</th>
              <th>NIS</th>
              <th>Jenis Kelamin</th>
              <th>Guru Fokus</th>
              <th>Tanggal Lahir</th>
              <th>No Telepon Orang Tua</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anakDidiks as $index => $anak)
            <tr id="row-{{ $anak->id }}">
              <td>{{ ($anakDidiks->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ asset('assets/img/avatars/' . (($anak->id % 4) + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" />
                  </div>
                  <div>
                    <p class="text-heading mb-0 fw-medium">{{ $anak->nama }}</p>
                  </div>
                </div>
              </td>
              <td>{{ $anak->nis }}</td>
              <td>
                <span class="badge bg-label-{{ $anak->jenis_kelamin === 'laki-laki' ? 'info' : 'warning' }}">
                  {{ ucfirst($anak->jenis_kelamin) }}
                </span>
              </td>
              <td>
                @if($anak->guruFokus)
                <span class="badge bg-label-primary">{{ $anak->guruFokus->nama }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $anak->tanggal_lahir ? \Carbon\Carbon::parse($anak->tanggal_lahir)->format('d M Y') : '-' }}</td>
              <td>{{ $anak->no_telepon_orang_tua ?? '-' }}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <a
                      href="{{ route('anak-didik.show', $anak->id) }}"
                      class="btn btn-sm btn-icon btn-outline-primary"
                      title="Lihat Detail">
                      <i class="ri-eye-line"></i>
                    </a>
                  @if(auth()->user() && auth()->user()->role === 'admin')
                  <a
                    href="{{ route('anak-didik.edit', $anak->id) }}"
                    class="btn btn-sm btn-icon btn-outline-warning"
                    title="Edit Data">
                    <i class="ri-edit-line"></i>
                  </a>
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-danger"
                    data-anak-id="{{ $anak->id }}"
                    title="Hapus Data"
                    onclick="deleteData(this)">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                  @endif
                  <!-- <a
                    href="{{ route('anak-didik.export-pdf', $anak->id) }}"
                    class="btn btn-sm btn-icon btn-outline-info"
                    title="Export PDF"
                    target="_blank">
                    <i class="ri-file-pdf-line"></i>
                  </a> -->
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada data anak didik ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $anakDidiks->firstItem() ?? 0 }} hingga {{ $anakDidiks->lastItem() ?? 0 }} dari {{ $anakDidiks->total() }} data
        </div>
        <nav>
          {{ $anakDidiks->links('pagination::bootstrap-4') }}
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
        <h5 class="modal-title" id="detailModalLabel">Detail Anak Didik</h5>
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
            <p class="text-body-secondary text-sm mb-1">Nama Siswa</p>
            <p class="fw-medium" id="detailNama"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">NIS</p>
            <p class="fw-medium" id="detailNis"></p>
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
            <p class="text-body-secondary text-sm mb-1">Tempat Lahir</p>
            <p class="fw-medium" id="detailTempatLahir">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">No Telepon Orang Tua</p>
            <p class="fw-medium" id="detailTlp">-</p>
          </div>
        </div>
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

  // Format Decimal
  function formatDecimal(value) {
    return value ? parseFloat(value).toFixed(2) : '-';
  }

  // Show Detail
  function showDetail(button) {
    const anakId = button.getAttribute('data-anak-id');

    let url = `/anak-didik/${anakId}`;
    @if(auth()->user()&& auth()->user()->role === 'guru')
    url = `{{ url('anak-didik') }}/${anakId}`;
    @endif
    fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        const anak = data.data;

        // Basic Info
        document.getElementById('detailNama').textContent = anak.nama || '-';
        document.getElementById('detailNis').textContent = anak.nis || '-';
        document.getElementById('detailTl').textContent = formatDate(anak.tanggal_lahir);
        document.getElementById('detailTempatLahir').textContent = anak.tempat_lahir || '-';
        document.getElementById('detailAlamat').textContent = anak.alamat || '-';
        document.getElementById('detailTlp').textContent = anak.no_telepon_orang_tua || '-';

        // Jenis Kelamin Badge
        const jkBadge = document.getElementById('detailJk');
        jkBadge.textContent = (anak.jenis_kelamin || '').charAt(0).toUpperCase() + (anak.jenis_kelamin || '').slice(1);
        jkBadge.className = anak.jenis_kelamin === 'laki-laki' ? 'badge bg-label-info' : 'badge bg-label-warning';

        // Set Avatar with Proper Path
        const avatarNum = (anakId % 4) + 1;
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
    const anakId = button.getAttribute('data-anak-id');

    if (confirm('Apakah Anda yakin ingin menghapus anak didik ini?')) {
      fetch(`/anak-didik/${anakId}`, {
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