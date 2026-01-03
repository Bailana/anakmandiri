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
          <a href="{{ route('anak-didik.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('anak-didik.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
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
    <form method="GET" action="{{ route('anak-didik.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1">
        <input id="searchDesktop" type="text" name="search" class="form-control" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
      </div>
      <!-- Filter Jenis Kelamin -->
      <select name="jenis_kelamin" class="form-select d-none d-sm-block" style="max-width: 150px;">
        <option value="">Jenis Kelamin</option>
        <option value="laki-laki" {{ request('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
        <option value="perempuan" {{ request('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
      </select>
      <!-- Filter Guru Fokus -->
      <select name="guru_fokus" class="form-select d-none d-sm-block" style="max-width: 200px;">
        <option value="">Guru Fokus</option>
        @foreach($guruOptions as $id => $name)
        <option value="{{ $id }}" {{ request('guru_fokus') == $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>
      <div class="d-flex flex-row gap-2 w-100 d-flex d-sm-none">
        <select name="jenis_kelamin" class="form-select" style="min-width:120px;">
          <option value="">Jenis Kelamin</option>
          <option value="laki-laki" {{ request('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
          <option value="perempuan" {{ request('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>
        <select name="guru_fokus" class="form-select" style="min-width:120px;">
          <option value="">Guru Fokus</option>
          @foreach($guruOptions as $id => $name)
          <option value="{{ $id }}" {{ request('guru_fokus') == $id ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>
      <div class="d-flex flex-row gap-2 w-100 d-flex d-sm-none">
        <button type="submit" class="btn btn-outline-primary w-50 d-inline-flex align-items-center justify-content-center p-0" style="height:44px;border-radius:12px;min-height:44px;">
          <i class="ri-search-line" style="font-size:1.3em;"></i>
        </button>
        <a href="{{ route('anak-didik.index') }}" class="btn btn-outline-secondary w-50 d-inline-flex align-items-center justify-content-center p-0" style="height:44px;border-radius:12px;min-height:44px;">
          <i class="ri-refresh-line" style="font-size:1.3em;"></i>
        </a>
      </div>
      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary d-none d-sm-inline-flex" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('anak-didik.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-flex" title="Reset">
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
              {{-- <th>NIS</th> --}}
              <th>Jenis Kelamin</th>
              <th>Guru Fokus</th>
              <th>Status</th>
              <th>Usia</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anakDidiks as $index => $anak)
            <tr id="row-{{ $anak->id }}">
              <td>{{ ($anakDidiks->currentPage() - 1) * $anakDidiks->perPage() + $index + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('assets/img/avatars/' . (($anak->id % 4) + 1) . '.svg') }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;aspect-ratio:1/1;" />
                  </div>
                  <div>
                    <p class="text-heading mb-0 fw-medium">{{ $anak->nama }}</p>
                  </div>
                </div>
              </td>
              {{-- <td>{{ $anak->nis ?: '-' }}</td> --}}
              <td>
                <span class="badge bg-label-{{ $anak->jenis_kelamin === 'laki-laki' ? 'info' : 'warning' }}">
                  {{ ucfirst($anak->jenis_kelamin) }}
                </span>
              </td>
              <td>
                @if($anak->guruFokus)
                <span class="badge bg-label-primary" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;vertical-align:middle;">{{ $anak->guruFokus->nama }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @if(isset($anak->status))
                <span class="badge bg-label-{{ $anak->status === 'aktif' ? 'success' : ($anak->status === 'nonaktif' ? 'danger' : 'warning') }}">
                  {{ ucfirst($anak->status) }}
                </span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @if($anak->tanggal_lahir)
                <span style="white-space: nowrap;">{{ \Carbon\Carbon::parse($anak->tanggal_lahir)->age }} Tahun</span>
                @else
                -
                @endif
              </td>
              <td>
                <!-- Tombol aksi untuk desktop -->
                <div class="d-none d-md-flex gap-2 align-items-center">
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
                    class="btn btn-sm btn-icon btn-outline-danger btn-hapus-anak"
                    data-anak-id="{{ $anak->id }}"
                    title="Hapus Data"
                    onclick="deleteData(this)">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                  @endif
                </div>
                <!-- Tombol titik tiga untuk mobile -->
                <div class="dropdown d-md-none">
                  <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('anak-didik.show', $anak->id) }}"><i class="ri-eye-line me-1"></i> Lihat</a></li>
                    @if(auth()->user() && auth()->user()->role === 'admin')
                    <li><a class="dropdown-item" href="{{ route('anak-didik.edit', $anak->id) }}"><i class="ri-edit-line me-1"></i> Edit</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteDataDropdown(this, {{ $anak->id }});return false;"><i class="ri-delete-bin-line me-1"></i> Hapus</a></li>
                    @endif
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
                <p class="text-body-secondary mb-0">Tidak ada data anak didik ditemukan</p>
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
          function deleteDataDropdown(el, anakId) {
            if (!confirm('Apakah Anda yakin ingin menghapus anak didik ini?')) return;
            // Buat dummy button agar deleteData tetap dapat parameter button
            var dummyBtn = document.createElement('button');
            dummyBtn.setAttribute('data-anak-id', anakId);
            deleteData(dummyBtn);
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
            {{-- <p class="text-body-secondary text-sm mb-1">NIS</p>
            <p class="fw-medium" id="detailNis"></p> --}}
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
<script>
  (function() {
    var searchDesktop = document.getElementById('searchDesktop');
    var jenisKelaminMobile = document.querySelector('.d-sm-none select[name="jenis_kelamin"]');
    var guruFokusMobile = document.querySelector('.d-sm-none select[name="guru_fokus"]');
    var jenisKelaminDesktop = document.querySelector('.d-none.d-sm-block select[name="jenis_kelamin"]') || document.querySelector('select[name="jenis_kelamin"]');
    var guruFokusDesktop = document.querySelector('.d-none.d-sm-block select[name="guru_fokus"]') || document.querySelector('select[name="guru_fokus"]');
    var form = searchDesktop && searchDesktop.closest('form');

    function syncNames() {
      var isMobile = window.matchMedia('(max-width: 575.98px)').matches;
      // Always keep desktop search input named 'search' so mobile submit uses it
      if (searchDesktop) searchDesktop.name = 'search';

      if (isMobile) {
        if (jenisKelaminMobile) jenisKelaminMobile.name = 'jenis_kelamin';
        if (jenisKelaminDesktop) jenisKelaminDesktop.removeAttribute('name');
        if (guruFokusMobile) guruFokusMobile.name = 'guru_fokus';
        if (guruFokusDesktop) guruFokusDesktop.removeAttribute('name');
      } else {
        if (jenisKelaminDesktop) jenisKelaminDesktop.name = 'jenis_kelamin';
        if (jenisKelaminMobile) jenisKelaminMobile.removeAttribute('name');
        if (guruFokusDesktop) guruFokusDesktop.name = 'guru_fokus';
        if (guruFokusMobile) guruFokusMobile.removeAttribute('name');
      }
    }

    if (form) {
      form.addEventListener('submit', function() {
        var isMobile = window.matchMedia('(max-width: 575.98px)').matches;
        if (isMobile) {
          // copy mobile selects into desktop selects so server receives filters
          if (jenisKelaminMobile && jenisKelaminDesktop) jenisKelaminDesktop.value = jenisKelaminMobile.value;
          if (guruFokusMobile && guruFokusDesktop) guruFokusDesktop.value = guruFokusMobile.value;
        } else {
          if (jenisKelaminDesktop && jenisKelaminMobile) jenisKelaminMobile.value = jenisKelaminDesktop.value;
          if (guruFokusDesktop && guruFokusMobile) guruFokusMobile.value = guruFokusDesktop.value;
        }

        // Ensure `search` is always submitted: prefer desktop input, fallback to any text input, otherwise create hidden input
        var searchVal = '';
        if (searchDesktop && typeof searchDesktop.value !== 'undefined') searchVal = searchDesktop.value;
        else {
          var anyText = form.querySelector('input[type="text"]');
          if (anyText) searchVal = anyText.value;
        }

        var existingSearch = form.querySelector('input[name="search"]');
        if (!existingSearch) {
          existingSearch = document.createElement('input');
          existingSearch.type = 'hidden';
          existingSearch.name = 'search';
          form.appendChild(existingSearch);
        }
        existingSearch.value = searchVal;

        // Diagnostic log for debugging network params
        try {
          console.log('Daftar AnakDidik submit ->', {
            search: existingSearch.value,
            jenis_kelamin: (jenisKelaminDesktop && jenisKelaminDesktop.value) || (jenisKelaminMobile && jenisKelaminMobile.value) || '',
            guru_fokus: (guruFokusDesktop && guruFokusDesktop.value) || (guruFokusMobile && guruFokusMobile.value) || ''
          });
        } catch (e) {
          // ignore
        }
      });
    }

    window.addEventListener('resize', syncNames);
    document.addEventListener('DOMContentLoaded', syncNames);
    syncNames();
  })();

  window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  window.formatDate = function(dateString) {
    if (!dateString) return '-';
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  }

  window.formatDecimal = function(value) {
    return value ? parseFloat(value).toFixed(2) : '-';
  }

  window.showDetail = function(button) {
    const anakId = button.getAttribute('data-anak-id');
    let url = `/anak-didik/${anakId}`;
    @if(auth()->user() && auth()->user()->role === 'guru')
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
        document.getElementById('detailNama').textContent = anak.nama || '-';
        // document.getElementById('detailNis').textContent = anak.nis || '-';
        document.getElementById('detailTl').textContent = window.formatDate(anak.tanggal_lahir);
        document.getElementById('detailTempatLahir').textContent = anak.tempat_lahir || '-';
        document.getElementById('detailAlamat').textContent = anak.alamat || '-';
        document.getElementById('detailTlp').textContent = anak.no_telepon_orang_tua || '-';
        const jkBadge = document.getElementById('detailJk');
        jkBadge.textContent = (anak.jenis_kelamin || '').charAt(0).toUpperCase() + (anak.jenis_kelamin || '').slice(1);
        jkBadge.className = anak.jenis_kelamin === 'laki-laki' ? 'badge bg-label-info' : 'badge bg-label-warning';
        const avatarNum = (anakId % 4) + 1;
        const avatarPath = '/assets/img/avatars/' + avatarNum + '.svg';
        document.getElementById('detailAvatar').src = avatarPath;
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Gagal mengambil data detail');
      });
  }

  window.deleteData = function(button) {
    if (!button || typeof button.getAttribute !== 'function') {
      console.error('deleteData: argumen button tidak valid', button);
      alert('Tombol hapus tidak valid.');
      return;
    }
    const anakId = button.getAttribute('data-anak-id');
    if (!anakId) {
      console.error('deleteData: data-anak-id tidak ditemukan pada button', button);
      alert('ID anak didik tidak ditemukan.');
      return;
    }
    if (confirm('Apakah Anda yakin ingin menghapus anak didik ini?')) {
      fetch(`/anak-didik/${anakId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Tampilkan alert success HTML di atas tabel
            var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
              '<i class="ri-checkbox-circle-line me-2"></i>Data anak didik berhasil dihapus' +
              '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
              '</div>';
            var container = document.querySelector('.card.mb-4');
            if (container) {
              container.insertAdjacentHTML('afterend', alertHtml);
            } else {
              document.body.insertAdjacentHTML('afterbegin', alertHtml);
            }
            setTimeout(function() {
              location.reload();
            }, 2000);
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