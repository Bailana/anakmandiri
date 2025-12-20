@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Program Konsultan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Daftar Program</h4>
          <p class="text-body-secondary mb-0">Daftar program master berdasarkan konsultan</p>
        </div>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')

        <!-- Modal: View Program -->
        <div class="modal fade" id="modalViewProgram" tabindex="-1" aria-labelledby="modalViewProgramLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalViewProgramLabel">Detail Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-2"><strong>Kode Program:</strong> <span id="viewKode"></span></div>
                <div class="mb-2"><strong>Nama Program:</strong>
                  <div id="viewNama"></div>
                </div>
                <div class="mb-2"><strong>Tujuan:</strong>
                  <div id="viewTujuan"></div>
                </div>
                <div class="mb-2"><strong>Aktivitas:</strong>
                  <div id="viewAktivitas"></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal: Edit Program -->
        <div class="modal fade" id="modalEditProgram" tabindex="-1" aria-labelledby="modalEditProgramLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="editProgramForm" method="POST" class="modal-content">
              @csrf
              @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title" id="modalEditProgramLabel">Edit Daftar Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Kode Program</label>
                  <input type="text" name="kode_program" id="editKode" class="form-control" readonly>
                </div>
                <div class="mb-3">
                  <label class="form-label">Nama Program</label>
                  <input type="text" name="nama_program" id="editNama" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Tujuan</label>
                  <textarea name="tujuan" id="editTujuan" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Aktivitas</label>
                  <textarea name="aktivitas" id="editAktivitas" class="form-control" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>

        @push('scripts')
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            // View button
            document.querySelectorAll('.btn-view-program').forEach(function(btn) {
              btn.addEventListener('click', function() {
                document.getElementById('viewKode').textContent = this.dataset.kode || '-';
                document.getElementById('viewNama').textContent = this.dataset.nama || '-';
                document.getElementById('viewTujuan').textContent = this.dataset.tujuan || '-';
                document.getElementById('viewAktivitas').textContent = this.dataset.aktivitas || '-';
              });
            });

            // Edit button
            document.querySelectorAll('.btn-edit-program').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var form = document.getElementById('editProgramForm');
                form.action = '{{ url('
                program - anak / program - konsultan ') }}' + '/' + id;
                document.getElementById('editKode').value = this.dataset.kode || '';
                document.getElementById('editNama').value = this.dataset.nama || '';
                document.getElementById('editTujuan').value = this.dataset.tujuan || '';
                document.getElementById('editAktivitas').value = this.dataset.aktivitas || '';
              });
            });
          });
        </script>
        @endpush
        <div>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddProgramMaster">
            <i class="ri-add-line me-2"></i>Tambah Daftar Program
          </button>
          <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary ms-2">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
<!-- Search & Filter -->
@if(session('success'))
<div class="row">
  <div class="col-12">
    <div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert">
      <i class="ri-checkbox-circle-line me-2"></i>
      <div class="flex-grow-1">{{ session('success') }}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

<div class="row">
  <div class="col-12">
    <form method="GET" action="{{ route('program-anak.daftar-program') }}" class="d-flex gap-2 align-items-end">
      <div class="flex-grow-1">
        <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, tujuan atau aktivitas..." value="{{ request('search') }}">
      </div>
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('program-anak.daftar-program') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Kode</th>
              <th>Nama Program</th>
              <th>Tujuan</th>
              <th>Aktivitas</th>
              <th>Konsultan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programs as $i => $p)
            <tr>
              <td>{{ ($programs->currentPage() - 1) * 15 + $i + 1 }}</td>
              <td>{{ $p->kode_program ?? '-' }}</td>
              <td>{{ $p->nama_program }}</td>
              <td>{{ Str::limit($p->tujuan, 100) }}</td>
              <td>{{ Str::limit($p->aktivitas, 100) }}</td>
              <td>{{ optional($p->konsultan)->nama ?? optional($p->konsultan)->spesialisasi ?? '-' }}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-view-program"
                    data-id="{{ $p->id }}"
                    data-kode="{{ $p->kode_program }}"
                    data-nama="{{ htmlentities($p->nama_program) }}"
                    data-tujuan="{{ htmlentities($p->tujuan) }}"
                    data-aktivitas="{{ htmlentities($p->aktivitas) }}"
                    data-bs-toggle="modal" data-bs-target="#modalViewProgram"
                    title="Lihat Detail">
                    <i class="ri-eye-line"></i>
                  </button>

                  <button type="button" class="btn btn-sm btn-icon btn-outline-warning btn-edit-program"
                    data-id="{{ $p->id }}"
                    data-kode="{{ $p->kode_program }}"
                    data-nama="{{ htmlentities($p->nama_program) }}"
                    data-tujuan="{{ htmlentities($p->tujuan) }}"
                    data-aktivitas="{{ htmlentities($p->aktivitas) }}"
                    data-bs-toggle="modal" data-bs-target="#modalEditProgram"
                    title="Edit">
                    <i class="ri-edit-line"></i>
                  </button>

                  <form action="{{ route('program-anak.program-konsultan.destroy', $p->id) }}" method="POST" class="d-inline-flex align-items-center" onsubmit="return confirm('Yakin ingin menghapus?')" style="margin:0;padding:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $programs->firstItem() ?? 0 }} hingga {{ $programs->lastItem() ?? 0 }} dari {{ $programs->total() }} data
        </div>
        <nav>
          {{ $programs->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal: Tambah Daftar Program -->
@if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
<div class="modal fade" id="modalAddProgramMaster" tabindex="-1" aria-labelledby="modalAddProgramMasterLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('program-anak.program-konsultan.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="modalAddProgramMasterLabel">Tambah Daftar Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Kode Program</label>
          <input type="text" name="kode_program" class="form-control" value="{{ $nextKode ?? '' }}" readonly aria-readonly="true">
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Program</label>
          <input type="text" name="nama_program" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tujuan</label>
          <textarea name="tujuan" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Aktivitas</label>
          <textarea name="aktivitas" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endif