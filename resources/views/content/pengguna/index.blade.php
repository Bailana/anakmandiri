@extends('layouts.contentNavbarLayout')

@section('title', 'Daftar Pengguna')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Daftar Pengguna</h4>
            <p class="text-body-secondary mb-0">Kelola akun pengguna</p>
          </div>
          <!-- Tombol tambah pengguna responsif -->
          <a href="{{ route('pengguna.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('pengguna.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
            <i class="ri-add-line me-2"></i>Tambah Pengguna
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('pengguna.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 250px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau role..." value="{{ request('search') }}">
      </div>

      <!-- Filter Role -->
      <select name="role" class="form-select" style="max-width: 150px;">
        <option value="">Role</option>
        @foreach($roleOptions as $role)
        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
        @endforeach
      </select>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover" id="penggunaTable">
      <thead>
        <tr class="table-light">
          <th>No</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Tanggal Dibuat</th>
          <th>Tanggal Diedit</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @php \Carbon\Carbon::setLocale('id'); @endphp
        @forelse($users as $index => $user)
        <tr>
          <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
          <td>
            <p class="text-heading mb-0 fw-medium">{{ $user->name }}</p>
          </td>
          <td>{{ $user->email }}</td>
          <td>
            @php
            $roleColors = [
            'admin' => 'primary',
            'guru' => 'info',
            'konsultan' => 'success',
            'karyawan' => 'warning',
            ];
            $color = $roleColors[$user->role] ?? 'secondary';
            @endphp
            <span class="badge bg-label-{{ $color }}">{{ ucfirst($user->role) }}</span>
          </td>
          <td>{{ $user->created_at ? $user->created_at->translatedFormat('l, d F Y') : '-' }}</td>
          <td>{{ $user->updated_at ? $user->updated_at->translatedFormat('l, d F Y') : '-' }}</td>
          <td>
            <!-- Tombol aksi untuk desktop -->
            <div class="d-none d-md-flex gap-2 align-items-center">
              <a href="{{ route('pengguna.edit', $user->id) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit Data">
                <i class="ri-edit-line"></i>
              </a>
              <form action="{{ route('pengguna.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Data" onclick="deletePengguna(this)">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </form>
            </div>
            <!-- Tombol titik tiga untuk mobile -->
            <div class="dropdown d-md-none">
              <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('pengguna.edit', $user->id) }}"><i class="ri-edit-line me-1"></i> Edit</a></li>
                <li>
                  <form action="{{ route('pengguna.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="dropdown-item text-danger" onclick="deletePenggunaDropdown(this);return false;"><i class="ri-delete-bin-line me-1"></i> Hapus</button>
                  </form>
                </li>
              </ul>
            </div>
          </td>
          @push('scripts')
          <script>
            function deletePengguna(button) {
              if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
                button.closest('form').submit();
              }
            }
          </script>
          @endpush
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">Tidak ada data pengguna.</td>
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
      function deletePenggunaDropdown(el) {
        if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
          // Cari form terdekat dan submit
          var form = el.closest('form');
          if (form) form.submit();
        }
      }
    </script>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center pagination-footer-fix">
    <style>
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
      Menampilkan {{ $users->firstItem() ?? 0 }} hingga {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
    </div>
    <nav>
      {{ $users->links('pagination::bootstrap-4') }}
    </nav>
  </div>
</div>
@endsection