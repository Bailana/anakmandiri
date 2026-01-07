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
        @if(auth()->check())

        <!-- Modal: View Program (refreshed UI with gradient badge and icons) -->
        <style>
          /* Scoped modal styles */
          #modalViewProgram .pv-badge-gradient {
            background: linear-gradient(90deg, #6f42c1, #7b61ff);
            color: #fff;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.35rem 0.6rem;
            display: inline-block;
          }

          /* responsive, truncating meta badges (kategori, konsultan) */
          #modalViewProgram .pv-meta-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-block;
            max-width: 45%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
          }

          /* allow flex children to shrink so badges respect max-width */
          #modalViewProgram .d-flex .flex-grow-1 {
            min-width: 0;
          }

          #modalViewProgram .pv-left {
            background: #fafafa;
            border-radius: 0.5rem;
            padding: 1rem;
          }

          #modalViewProgram .pv-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f0f4ff, #e8eefc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #3b5bdb
          }

          /* smaller screens: reduce badge max-width and font-size */
          @media (max-width: 576px) {
            #modalViewProgram .pv-meta-badge {
              max-width: 60%;
              font-size: .9rem;
            }

            #modalViewProgram .pv-left {
              padding: .75rem;
            }

            #modalViewProgram .pv-badge-gradient {
              padding: .25rem .45rem;
            }
          }
        </style>
        <div class="modal fade" id="modalViewProgram" tabindex="-1" aria-labelledby="modalViewProgramLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header border-0">
                <h5 class="modal-title" id="modalViewProgramLabel">Detail Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="d-flex gap-3 mb-3">
                  <div class="pv-left d-flex gap-3 align-items-center">
                    <div class="pv-icon"><i class="ri-archive-line"></i></div>
                    <div>
                      <div class="text-muted small">Kode Program</div>
                      <div id="viewKode" class="pv-badge-gradient">-</div>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <h4 id="viewNama" class="mb-1 fw-bold">-</h4>
                    <div class="d-flex gap-2 align-items-center mb-2">
                      <span id="viewKategori" class="pv-meta-badge badge bg-light text-muted">-</span>
                      <span id="viewKonsultan" class="pv-meta-badge badge bg-warning text-dark">-</span>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="mb-3">
                      <div class="text-muted small mb-1">Tujuan</div>
                      <div id="viewTujuan" class="text-body-secondary">-</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <div class="text-muted small mb-1">Aktivitas</div>
                      <div id="viewAktivitas" class="text-body-secondary">-</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <div class="text-muted small mb-1">Keterangan</div>
                      <div id="viewKeterangan" class="text-body-secondary">-</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal: Edit Program -->
        <div class="modal fade modalScrollable" id="modalEditProgram" tabindex="-1" aria-labelledby="modalEditProgramLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-scrollable">
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
                <div class="mb-3">
                  <label class="form-label">Keterangan</label>
                  <textarea name="keterangan" id="editKeterangan" class="form-control" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                  <i class="ri-close-line me-2"></i>Batal
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="ri-refresh-line me-2"></i>Perbarui
                </button>
              </div>
            </form>
          </div>
        </div>

        @push('scripts')
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            // Helper to decode HTML entities (e.g. &quot; -> ")
            function decodeHtmlEntities(str) {
              if (typeof str !== 'string') return str;
              const txt = document.createElement('textarea');
              txt.innerHTML = str;
              return txt.value;
            }

            // View button
            document.querySelectorAll('.btn-view-program').forEach(function(btn) {
              btn.addEventListener('click', function() {
                document.getElementById('viewKode').textContent = decodeHtmlEntities(this.dataset.kode) || '-';
                document.getElementById('viewNama').textContent = decodeHtmlEntities(this.dataset.nama) || '-';
                document.getElementById('viewTujuan').textContent = decodeHtmlEntities(this.dataset.tujuan) || '-';
                document.getElementById('viewAktivitas').textContent = decodeHtmlEntities(this.dataset.aktivitas) || '-';
                document.getElementById('viewKeterangan').textContent = decodeHtmlEntities(this.dataset.keterangan) || '-';

                // Populate kategori and konsultan badges; hide if empty or '-'
                const katEl = document.getElementById('viewKategori');
                const konsEl = document.getElementById('viewKonsultan');
                const kat = (decodeHtmlEntities(this.dataset.kategori) || '').trim();
                const kons = (decodeHtmlEntities(this.dataset.konsultan) || '').trim();
                if (!kat || kat === '-') {
                  katEl.style.display = 'none';
                } else {
                  katEl.style.display = '';
                  katEl.textContent = kat;
                }
                if (!kons || kons === '-') {
                  konsEl.style.display = 'none';
                } else {
                  konsEl.style.display = '';
                  konsEl.textContent = kons;
                }
              });
            });

            // Edit button
            document.querySelectorAll('.btn-edit-program').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var form = document.getElementById('editProgramForm');
                form.action = '{{ url("program-anak/program-konsultan") }}' + '/' + id;
                document.getElementById('editKode').value = decodeHtmlEntities(this.dataset.kode) || '';
                document.getElementById('editNama').value = decodeHtmlEntities(this.dataset.nama) || '';
                document.getElementById('editTujuan').value = decodeHtmlEntities(this.dataset.tujuan) || '';
                document.getElementById('editAktivitas').value = decodeHtmlEntities(this.dataset.aktivitas) || '';
                document.getElementById('editKeterangan').value = decodeHtmlEntities(this.dataset.keterangan) || '';
              });
            });
          });
        </script>
        @endpush
        <script>
          // Provide fallback showToast if not defined globally
          if (typeof showToast !== 'function') {
            function showToast(message, type = 'success') {
              try {
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
                toast.querySelector('.toast-body').textContent = message || '';
                var bsToast = bootstrap.Toast.getOrCreateInstance(toast, {
                  delay: 2000
                });
                bsToast.show();
              } catch (e) {
                // fallback to alert
                try {
                  console.log(message);
                } catch (e) {}
                alert(message);
              }
            }
          }
          // AJAX submit for editProgramForm to show success toast without full page redirect
          document.addEventListener('DOMContentLoaded', function() {
            try {
              const form = document.getElementById('editProgramForm');
              if (!form) return;
              form.addEventListener('submit', function(e) {
                e.preventDefault();
                const action = form.action;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const fd = new FormData(form);
                // ensure method override
                fd.set('_method', 'PUT');

                const body = new URLSearchParams();
                for (const pair of fd.entries()) {
                  body.append(pair[0], pair[1]);
                }

                fetch(action, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: body.toString()
                }).then(r => {
                  // If server returns JSON, parse it and check 'success'
                  const ct = r.headers.get('content-type') || '';
                  if (r.ok && ct.indexOf('application/json') !== -1) {
                    return r.json().then(resp => ({
                      status: r.status,
                      json: resp
                    }));
                  }
                  // If server returned OK but not JSON (likely a redirect), treat as success
                  if (r.ok) {
                    return {
                      status: r.status,
                      json: {
                        success: true,
                        message: 'Daftar program berhasil diupdate (redirect)'
                      }
                    };
                  }
                  // non-OK response
                  return r.text().then(text => ({
                    status: r.status,
                    json: {
                      success: false,
                      message: text || 'Gagal update'
                    }
                  }));
                }).then(({
                  status,
                  json: resp
                }) => {
                  if (resp && resp.success) {
                    // Remove existing toast if present
                    try {
                      const t = document.getElementById('customToast');
                      if (t) t.remove();
                    } catch (e) {}

                    // Create Bootstrap alert similar to server flash
                    try {
                      const firstRow = document.querySelector('.row');
                      const alertDiv = document.createElement('div');
                      alertDiv.className = 'row';
                      alertDiv.innerHTML = `
                        <div class="col-12">
                          <div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert">
                            <i class="ri-checkbox-circle-line me-2"></i>
                            <div class="flex-grow-1">${(resp && resp.message) ? resp.message : 'Daftar program berhasil diupdate'}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>
                        </div>`;
                      if (firstRow && firstRow.parentNode) {
                        // insert AFTER the first row so it matches server flash position
                        firstRow.parentNode.insertBefore(alertDiv, firstRow.nextSibling);
                      } else {
                        document.body.insertBefore(alertDiv, document.body.firstChild);
                      }
                      // auto-dismiss after 4s like other alerts
                      setTimeout(() => {
                        try {
                          const bsAlertEl = alertDiv.querySelector('.alert');
                          if (bsAlertEl) bootstrap.Alert.getOrCreateInstance(bsAlertEl).close();
                        } catch (e) {}
                      }, 4000);
                    } catch (e) {
                      // fallback to toast if alert injection fails
                      showToast(resp.message || 'Berhasil diupdate', 'success');
                    }

                    try {
                      bootstrap.Modal.getInstance(document.getElementById('modalEditProgram')).hide();
                    } catch (e) {}
                    // Soft update: update the table row in-place instead of full reload
                    try {
                      const pid = resp.program && resp.program.id ? resp.program.id : null;
                      if (pid) {
                        const row = document.querySelector('tr[data-id="' + pid + '"]');
                        if (row) {
                          const tds = row.querySelectorAll('td');
                          const truncate = (s, n) => {
                            if (!s) return '-';
                            return s.length > n ? s.substring(0, n - 1) + '…' : s;
                          };
                          if (tds[1]) tds[1].textContent = resp.program.kode_program || '-';
                          if (tds[2]) tds[2].textContent = resp.program.nama_program || '-';
                          if (tds[3]) tds[3].textContent = truncate(resp.program.tujuan || '', 100);
                          if (tds[4]) tds[4].textContent = truncate(resp.program.aktivitas || '', 100);
                          // update action buttons' data attributes
                          const viewBtn = row.querySelector('.btn-view-program');
                          const editBtn = row.querySelector('.btn-edit-program');
                          if (viewBtn) {
                            viewBtn.dataset.kode = resp.program.kode_program || '';
                            viewBtn.dataset.nama = resp.program.nama_program || '';
                            viewBtn.dataset.tujuan = resp.program.tujuan || '';
                            viewBtn.dataset.aktivitas = resp.program.aktivitas || '';
                            viewBtn.dataset.keterangan = resp.program.keterangan || '';
                          }
                          if (editBtn) {
                            editBtn.dataset.kode = resp.program.kode_program || '';
                            editBtn.dataset.nama = resp.program.nama_program || '';
                            editBtn.dataset.tujuan = resp.program.tujuan || '';
                            editBtn.dataset.aktivitas = resp.program.aktivitas || '';
                            editBtn.dataset.keterangan = resp.program.keterangan || '';
                          }
                        }
                      }
                    } catch (e) {
                      console.error('Soft update failed', e);
                    }
                  } else {
                    console.warn('Update failed response:', status, resp);
                    showToast((resp && resp.message) || 'Gagal update', 'danger');
                  }
                }).catch(err => {
                  console.error('AJAX update error', err);
                  showToast('Gagal update', 'danger');
                });
              });
            } catch (e) {}
          });
        </script>
        <div class="d-flex gap-2 flex-row">
          @if(auth()->user()->role === 'konsultan')
          <button type="button" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" data-bs-toggle="modal" data-bs-target="#modalAddProgramMaster" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </button>
          <button type="button" class="btn btn-primary d-none d-sm-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalAddProgramMaster" style="border-radius:12px;">
            <i class="ri-add-line me-2"></i>Tambah Daftar Program
          </button>
          @endif
          @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
          <a href="{{ route('program-anak.index') }}" class="btn p-0 border-0 bg-transparent d-inline-flex d-sm-none align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-arrow-left-circle-fill" style="font-size:2em;font-weight:bold;"></i>
          </a>
          <a href="{{ route('program-anak.index') }}" class="btn btn-secondary btn-sm d-none d-sm-inline-flex align-items-center" style="border-radius:12px;">
            <i class="ri-arrow-left-line me-2"></i>Kembali
          </a>
          @endif
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
    <form method="GET" action="{{ route('program-anak.daftar-program') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width:160px;">
        <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, tujuan atau aktivitas..." value="{{ request('search') }}">
      </div>

      @if(auth()->check() && auth()->user()->role === 'admin')
      <div style="min-width:180px;">
        <select name="filter_konsultan" class="form-select">
          <option value="" {{ request('filter_konsultan') == '' ? 'selected' : '' }}>Semua Konsultan</option>
          <option value="Psikologi" {{ request('filter_konsultan') == 'Psikologi' ? 'selected' : '' }}>Psikologi</option>
          <option value="Pendidikan" {{ request('filter_konsultan') == 'Pendidikan' ? 'selected' : '' }}>Pendidikan</option>
          <option value="Wicara" {{ request('filter_konsultan') == 'Wicara' ? 'selected' : '' }}>Wicara</option>
          <option value="Sensori Integrasi" {{ request('filter_konsultan') == 'Sensori Integrasi' ? 'selected' : '' }}>Sensori Integrasi</option>
        </select>
      </div>
      @endif

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
    <div class="card @if(auth()->check() && auth()->user()->role === 'admin') mt-3 @endif">
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
              @if(auth()->check())
              <th>Aksi</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($programs as $i => $p)
            <tr data-id="{{ $p->id }}">
              <td>{{ ($programs->currentPage() - 1) * $programs->perPage() + $i + 1 }}</td>
              <td class="text-heading mb-0 fw-medium">{{ $p->kode_program ?? '-' }}</td>
              <td>{{ $p->nama_program }}</td>
              <td>{{ Str::limit($p->tujuan, 100) }}</td>
              <td>{{ Str::limit($p->aktivitas, 100) }}</td>
              <td>{{ optional($p->konsultan)->nama ?? optional($p->konsultan)->spesialisasi ?? '-' }}</td>
              @if(auth()->check())
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <div class="d-none d-sm-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-view-program"
                      data-id="{{ $p->id }}"
                      data-kode="{{ $p->kode_program }}"
                      data-nama="{{ htmlentities($p->nama_program) }}"
                      data-tujuan="{{ htmlentities($p->tujuan) }}"
                      data-aktivitas="{{ htmlentities($p->aktivitas) }}"
                      data-keterangan="{{ htmlentities($p->keterangan) }}"
                      data-konsultan="{{ optional($p->konsultan)->nama ?? optional($p->konsultan)->spesialisasi ?? '-' }}"
                      data-kategori="{{ $p->kategori ?? ($p->kategori_program ?? '-') }}"
                      data-bs-toggle="modal" data-bs-target="#modalViewProgram"
                      title="Lihat Detail">
                      <i class="ri-eye-line"></i>
                    </button>
                    @if(auth()->user()->role !== 'admin')
                    <button type="button" class="btn btn-sm btn-icon btn-outline-warning btn-edit-program"
                      data-id="{{ $p->id }}"
                      data-kode="{{ $p->kode_program }}"
                      data-nama="{{ htmlentities($p->nama_program) }}"
                      data-tujuan="{{ htmlentities($p->tujuan) }}"
                      data-aktivitas="{{ htmlentities($p->aktivitas) }}"
                      data-keterangan="{{ htmlentities($p->keterangan) }}"
                      data-bs-toggle="modal" data-bs-target="#modalEditProgram"
                      title="Edit Program">
                      <i class="ri-edit-line"></i>
                    </button>
                    <form method="POST" action="{{ route('program-anak.program-konsultan.destroy', $p->id) }}" class="d-inline m-0 p-0 align-self-center">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Program" onclick="return confirm('Yakin ingin menghapus program ini?')">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </form>
                    @endif
                  </div>
                  <div class="d-inline-block d-sm-none dropdown">
                    <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                      <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-id="{{ $p->id }}" data-bs-toggle="modal" data-bs-target="#modalViewProgram" onclick="document.querySelector('.btn-view-program[data-id=\'{{ $p->id }}\']').click();return false;"><i class='ri-eye-line me-1'></i> Lihat</a></li>
                      @if(auth()->user()->role !== 'admin')
                      <li><a class="dropdown-item" href="#" data-id="{{ $p->id }}" onclick="document.querySelector('.btn-edit-program[data-id=\'{{ $p->id }}\']').click();return false;"><i class='ri-edit-line me-1'></i> Edit</a></li>
                      <li>
                        <form method="POST" action="{{ route('program-anak.program-konsultan.destroy', $p->id) }}" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Yakin ingin menghapus program ini?')"><i class='ri-delete-bin-line me-1'></i> Hapus</button>
                        </form>
                      </li>
                      @endif
                    </ul>
                  </div>
                </div>
              </td>
              @endif
            </tr>
            @empty
            <tr>
              <td colspan="{{ auth()->check() ? 7 : 6 }}" class="text-center">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
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
@if(auth()->check() && auth()->user()->role === 'konsultan')
<div class="modal fade modalScrollable" id="modalAddProgramMaster" tabindex="-1" aria-labelledby="modalAddProgramMasterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
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
        <div class="mb-3">
          <label class="form-label">Keterangan</label>
          <textarea name="keterangan" class="form-control" rows="3"></textarea>
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