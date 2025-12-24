@extends('layouts/contentNavbarLayout')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Program Pembelajaran Individual (PPI)</h4>
            <p class="text-body-secondary mb-0">Tampilkan daftar anak didik. Klik untuk melihat program jika Anda memiliki akses.</p>
          </div>
          @if(!(isset($isKonsultanPendidikan) && $isKonsultanPendidikan))
          <div>
            <a href="{{ route('ppi.create') }}" class="btn btn-primary">
              <i class="ri-add-line me-2"></i>Tambah PPI
            </a>
          </div>
          @endif
        </div>
      </div>
    </div>

    <div id="ppiDetailContainer${item.id}" class="ppi-detail-container d-none"></div>
  </div>

  <!-- Alert Messages -->
  <div id="ppi-alert-wrapper">@if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif</div>
  <!-- Toast container for request-access notifications -->
  <div id="ppi-toast-container" class="position-fixed top-0 end-0 p-3" style="z-index:1080"></div>

  <!-- Search & Filter -->
  <div class="row mb-4">
    <div class="col-12">
      <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div class="flex-grow-1" style="min-width:200px;">
          <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ $search ?? '' }}">
        </div>
        <div style="min-width:200px;">
          <select name="guru_fokus" class="form-select">
            <option value="">Guru Fokus</option>
            @if(!empty($guruOptions))
            @foreach($guruOptions as $g)
            <option value="{{ $g->id }}" {{ (isset($guru_fokus) && $guru_fokus == $g->id) ? 'selected' : '' }}>{{ $g->nama }}</option>
            @endforeach
            @endif
          </select>
        </div>
        <div>
          <button type="submit" class="btn btn-outline-primary" title="Cari">
            <i class="ri-search-line"></i>
          </button>
        </div>
        <div>
          <a href="{{ route('ppi.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="ri-refresh-line"></i></a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr class="table-light">
                <th>No</th>
                <th>Anak Didik</th>
                <th>Guru Fokus</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($anakList as $index => $anak)
              <tr>
                <td>{{ ($anakList->currentPage() - 1) * $anakList->perPage() + $index + 1 }}</td>
                <td>
                  <strong>{{ $anak->nama }}</strong><br>
                  <small class="text-body-secondary">{{ $anak->nis ?? '-' }}</small>
                </td>
                <td>{{ $anak->guruFokus ? $anak->guruFokus->nama : '-' }}</td>

                <td>
                  <div class="d-flex gap-2 align-items-center">
                    @if(isset($isKonsultanPendidikan) && $isKonsultanPendidikan)
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#riwayatPpiModal" data-anak-didik-id="{{ $anak->id }}" data-is-fokus="{{ isset($isFokusMap[$anak->id]) && $isFokusMap[$anak->id] ? '1' : '0' }}" onclick="loadRiwayatPpi(this)" title="Riwayat" aria-label="Riwayat">
                      <i class="ri-history-line"></i>
                    </button>
                    @else
                    @if(isset($accessMap[$anak->id]) && $accessMap[$anak->id])
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#riwayatPpiModal" data-anak-didik-id="{{ $anak->id }}" data-is-fokus="{{ isset($isFokusMap[$anak->id]) && $isFokusMap[$anak->id] ? '1' : '0' }}" onclick="loadRiwayatPpi(this)" title="Riwayat PPI">
                      <i class="ri-history-line"></i>
                    </button>
                    @else
                    <button class="btn btn-sm btn-icon btn-outline-danger btn-request-access" data-id="{{ $anak->id }}" title="Minta Akses" aria-label="Minta Akses" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-lock-line"></i></button>
                    @endif
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center py-5">
                  <div class="mb-3"><i class="ri-search-line" style="font-size:3rem;color:#ccc"></i></div>
                  <p class="text-body-secondary mb-0">Tidak ada data anak ditemukan</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer d-flex justify-content-between align-items-center">
          <div class="text-body-secondary">
            Menampilkan {{ $anakList->firstItem() ?? 0 }} hingga {{ $anakList->lastItem() ?? 0 }} dari {{ $anakList->total() }} data
          </div>
          <nav>
            {{ $anakList->links('pagination::bootstrap-4') }}
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Riwayat PPI -->
  <div class="modal fade" id="riwayatPpiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Riwayat PPI</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="riwayatPpiList">
            <div class="text-center text-muted">Memuat data...</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit PPI -->
  <div class="modal fade" id="editPpiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit PPI</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- form will be injected here -->
          <div class="text-center text-muted">Memuat...</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  @endsection

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // init bootstrap tooltips for action buttons
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      function showAlert(message, type = 'success') {
        const wrapper = document.getElementById('ppi-alert-wrapper');
        if (!wrapper) return;
        wrapper.innerHTML = `<div id="ppi-alert" class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
      }

      document.querySelectorAll('.btn-request-access').forEach(btn => {
        btn.addEventListener('click', function() {
          const anakId = this.dataset.id;
          if (!confirm('Kirim permintaan akses ke admin?')) return;
          const formData = new FormData();
          formData.append('anak_didik_id', anakId);
          // CSRF token from meta tag
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          fetch("{{ route('ppi.request-access') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: formData
          }).then(r => {
            if (!r.ok) {
              // try to get message from response, otherwise throw generic
              return r.text().then(t => {
                throw new Error((t && t.length) ? t : 'HTTP ' + r.status);
              });
            }
            return r.json();
          }).then(j => {
            // gunakan hanya showAlert untuk memberi umpan balik kepada pengguna
            if (j && j.success) showAlert(j.message || 'Permintaan berhasil dikirim');
            else showAlert(j && j.message ? j.message : 'Terjadi kesalahan', 'danger');
          }).catch(err => {
            showAlert(err && err.message ? err.message : 'Terjadi kesalahan jaringan', 'danger');
          });
        });
      });

      // Check unread notifications and show alert for approved access
      (function checkUnreadNotificationsForApproval() {
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        const token = tokenEl ? tokenEl.getAttribute('content') : '';
        fetch('/notifications/unread-json', {
            credentials: 'same-origin'
          })
          .then(r => r.json())
          .then(j => {
            if (!j || !j.success) return;
            const notifs = j.notifications || [];
            notifs.forEach(n => {
              try {
                const d = n.data || {};
                if (d.action === 'approved') {
                  // show alert to the requester
                  showAlert(d.message || 'Permintaan akses Anda telah disetujui');
                  // mark notification as read so we don't show it repeatedly
                  if (n.id && token) {
                    fetch("{{ route('notifications.mark-read') }}", {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                      },
                      body: JSON.stringify({
                        id: n.id
                      }),
                      credentials: 'same-origin'
                    }).catch(() => {});
                  }
                }
              } catch (e) {
                // ignore
              }
            });
          }).catch(() => {});
      })();

      // Load riwayat PPI for an anak didik and render list
      window.loadRiwayatPpi = function(btn) {
        var listDiv = document.getElementById('riwayatPpiList');
        listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
        var anakId = btn.getAttribute('data-anak-didik-id');
        var isFokus = (btn.getAttribute('data-is-fokus') === '1');
        var currentUserId = @json(Auth::id());
        var canApprove = @json($canApprovePPI ?? false);
        fetch('/ppi/riwayat/' + anakId)
          .then(r => r.json())
          .then(res => {
            if (!res.success || !res.riwayat || res.riwayat.length === 0) {
              listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat PPI.</div>';
              return;
            }
            // helper to format dates to Indonesian with weekday (no time)
            function formatDateIndo(dateStr) {
              if (!dateStr) return '';
              // normalize separator and remove time if present
              const d = new Date(dateStr.replace(' ', 'T'));
              if (isNaN(d)) return dateStr;
              const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
              const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
              const dayName = days[d.getDay()];
              const day = d.getDate();
              const month = months[d.getMonth()];
              const year = d.getFullYear();
              return `${dayName}, ${(''+day).padStart(2,'0')} ${month} ${year}`;
            }

            let html = '';
            // store riwayat globally for edit operations and build program options
            window._ppiRiwayat = res.riwayat || [];
            // build unique program options from riwayat items
            window._ppiProgramOptions = window._ppiProgramOptions || [];
            const progSet = new Set(window._ppiProgramOptions);
            (res.riwayat || []).forEach(r => {
              if (r.items && r.items.length) {
                r.items.forEach(it => {
                  if (it.nama_program) progSet.add(it.nama_program);
                });
              }
            });
            window._ppiProgramOptions = Array.from(progSet);
            res.riwayat.forEach(item => {
              // minimal card: only show day/date and action buttons; details are lazy-loaded
              html += `
              <div class="mb-3 p-3 border rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="small text-muted">${formatDateIndo(item.created_at)}</div>
                  </div>
                  <div class="text-end">
                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewPpiDetail(this)" data-ppi-id="${item.id}" title="Lihat"><i class='ri-eye-line'></i></button>`;
              if (isFokus) {
                html += `<button class="btn btn-sm btn-outline-secondary me-1" onclick="editPpi(this)" data-ppi-id="${item.id}" title="Edit"><i class='ri-edit-2-line'></i></button>`;
                html += `<button class="btn btn-sm btn-outline-danger" onclick="deletePpi(${item.id})" title="Hapus"><i class='ri-delete-bin-line'></i></button>`;
              } else if (canApprove && item.status !== 'disetujui') {
                html += `<button class="btn btn-sm btn-success me-1" onclick="approvePpi(${item.id})" title="Setujui"><i class='ri-check-line'></i></button>`;
              } else {
                if (item.status) html += `<span class="badge bg-secondary me-1">${item.status}</span>`;
              }
              html += `
                  </div>
                </div>
                <div id="ppiDetailContainer${item.id}" class="ppi-detail-container mt-2 d-none"></div>
              </div>`;
            });
            listDiv.innerHTML = html;
          }).catch(() => {
            listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
          });
      }

      // Delete PPI (only for guru fokus)
      window.deletePpi = function(id) {
        if (!confirm('Hapus PPI ini? Tindakan ini tidak dapat dibatalkan.')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/ppi/' + id, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        }).then(r => r.json()).then(res => {
          if (res.success) {
            // refresh modal list
            var lastBtn = document.querySelector('button[data-bs-target="#riwayatPpiModal"]:focus') || document.querySelector('button[data-bs-target="#riwayatPpiModal"]');
            if (lastBtn) loadRiwayatPpi(lastBtn);
            alert(res.message || 'PPI berhasil dihapus');
          } else {
            alert(res.message || 'Gagal menghapus PPI');
          }
        }).catch(() => alert('Terjadi kesalahan jaringan'));
      }

      window.approvePpi = function(id) {
        if (!confirm('Setujui PPI ini?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/ppi/' + id + '/approve', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        }).then(r => r.json()).then(res => {
          if (res.success) {
            // refresh list
            var lastBtn = document.querySelector('button[data-bs-target="#riwayatPpiModal"]:focus') || document.querySelector('button[data-bs-target="#riwayatPpiModal"]');
            if (lastBtn) loadRiwayatPpi(lastBtn);
            alert(res.message || 'Berhasil disetujui');
          } else {
            alert(res.message || 'Gagal menyetujui');
          }
        }).catch(() => alert('Terjadi kesalahan jaringan'));
      }

      window.viewPpiDetail = async function(btn, id) {
        try {
          // support calling as (btn) or (btn, id)
          if (btn && !id && btn.getAttribute) {
            id = btn.getAttribute('data-ppi-id') || btn.dataset.ppiId;
          }
          if (!id) return;

          const container = document.getElementById('ppiDetailContainer' + id);
          if (!container) return;

          // toggle if already loaded
          if (container.dataset.loaded === '1') {
            container.classList.toggle('d-none');
            return;
          }

          container.innerHTML = '<div class="text-center py-3">Memuat detail...</div>';
          container.classList.remove('d-none');

          const res = await fetch(`/ppi/${id}/detail-json`);
          if (!res.ok) throw new Error('Gagal memuat');
          const data = await res.json();

          let html = '';
          html += `<div class="card">`;
          html += `<div class="card-body">`;
          html += `<h6 class="card-title">Program Riwayat - ${data.anak ? data.anak.nama : ''}</h6>`;
          if (data.items && data.items.length) {
            // group items by kategori
            const groups = {};
            data.items.forEach(item => {
              const cat = item.kategori && item.kategori.trim() ? item.kategori.trim() : 'Lainnya';
              if (!groups[cat]) groups[cat] = [];
              groups[cat].push(item);
            });

            // render each category with its programs (show category as a colored badge)
            Object.keys(groups).forEach(cat => {
              // choose badge color per category
              let badgeClass = 'bg-info';
              switch ((cat || '').toLowerCase()) {
                case 'akademik':
                  badgeClass = 'bg-primary';
                  break;
                case 'bina diri':
                  badgeClass = 'bg-success';
                  break;
                case 'motorik':
                  badgeClass = 'bg-warning text-dark';
                  break;
                case 'perilaku':
                  badgeClass = 'bg-danger';
                  break;
                case 'vokasi':
                  badgeClass = 'bg-secondary';
                  break;
                default:
                  badgeClass = 'bg-info';
              }
              html += `<div class="mb-2"><span class="badge ${badgeClass}">${cat}</span></div>`;
              html += '<ul class="list-group list-group-flush mb-3">';
              groups[cat].forEach((item, idx) => {
                const num = idx + 1;
                const progName = (item.program_konsultan && item.program_konsultan.nama_program) ? item.program_konsultan.nama_program : (item.nama_program || '—');
                html += `<li class="list-group-item">`;
                html += `<div><strong>${num}. ${progName}</strong></div>`;
                if (item.notes) html += `<div class="small text-muted mt-1">${item.notes}</div>`;
                html += `</li>`;
              });
              html += '</ul>';
            });
          } else {
            html += '<div class="text-muted">Tidak ada program.</div>';
          }
          if (data.keterangan) {
            html += `<div class="mt-3"><strong>Catatan:</strong><div class="small text-muted mt-1">${data.keterangan}</div></div>`;
          }
          if (data.review_comment) {
            html += `<div class="mt-2"><strong>Catatan Review:</strong><div class="small text-muted mt-1">${data.review_comment}</div></div>`;
          }
          html += `</div></div>`;

          container.innerHTML = html;
          container.dataset.loaded = '1';
        } catch (e) {
          const container = document.getElementById('ppiDetailContainer' + (id || (btn && btn.getAttribute ? btn.getAttribute('data-ppi-id') : '')));
          if (container) container.innerHTML = '<div class="text-danger p-2">Gagal memuat detail.</div>';
        }
      }

      // Open edit modal for a PPI entry and populate form
      window.editPpi = async function(btn) {
        try {
          const ppiId = btn.getAttribute('data-ppi-id');
          if (!ppiId) return;
          const modalEl = document.getElementById('editPpiModal');
          const modalBody = modalEl.querySelector('.modal-body');
          modalBody.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
          const modal = new bootstrap.Modal(modalEl);
          modal.show();

          const res = await fetch(`/ppi/${ppiId}/detail-json`);
          if (!res.ok) throw new Error('Gagal memuat');
          const data = await res.json();

          const items = data.items || [];
          // build program options from global or items
          const programOptions = Array.from(new Set(((window._ppiProgramOptions || []).concat(items.map(i => i.nama_program || (i.program_konsultan && i.program_konsultan.nama_program) || '')))));

          let formHtml = `<form id="editPpiForm" onsubmit="event.preventDefault(); savePpiEdit(${ppiId}, this);">
            <div class="mb-2"><strong>Program tanggal: ${data.created_at ? data.created_at.split('T')[0] : ''}</strong></div>
            <div class="ppi-edit-items">`;

          if (items.length) {
            items.forEach(it => {
              // program select
              let progOpts = `<option value="">-- Pilih program --</option>`;
              programOptions.forEach(po => {
                const sel = (po === (it.nama_program || (it.program_konsultan && it.program_konsultan.nama_program))) ? 'selected' : '';
                progOpts += `<option ${sel}>${po}</option>`;
              });
              // categories
              const cats = ['Akademik', 'Bina Diri', 'Motorik', 'Perilaku', 'Vokasi'];
              let catOpts = `<option value="">-- Kategori --</option>`;
              cats.forEach(c => {
                const s = (c === (it.kategori || '')) ? 'selected' : '';
                catOpts += `<option ${s}>${c}</option>`;
              });

              formHtml += `<div class="d-flex gap-2 mb-2 align-items-start edit-item-row">
                <input type="hidden" name="item_id[]" value="${it.id || ''}">
                <select name="nama_program[]" class="form-select">${progOpts}</select>
                <select name="kategori[]" class="form-select">${catOpts}</select>
                <button type="button" class="btn btn-outline-danger btn-icon btn-sm" onclick="this.closest('.edit-item-row').remove()" aria-label="Hapus program"><i class='ri-delete-bin-line'></i></button>
              </div>`;
            });
          } else {
            formHtml += `<div class="text-muted small">(tidak ada program tercatat)</div>`;
          }

          formHtml += `</div>
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPpiEditRow(this)"><i class='ri-add-line'></i> Tambah</button>
              <button type="submit" class="btn btn-sm btn-primary"><i class='ri-save-3-line'></i> Simpan</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelPpiEdit(this)"><i class='ri-close-line'></i> Batal</button>
            </div>
          </form>`;

          modalBody.innerHTML = formHtml;
        } catch (e) {
          console.error(e);
          const modalEl = document.getElementById('editPpiModal');
          modalEl.querySelector('.modal-body').innerHTML = '<div class="text-danger">Gagal memuat data.</div>';
        }
      }

      window.addPpiEditRow = function(btn) {
        // find the .ppi-edit-items container within the current form/modal
        const form = btn.closest('form');
        const container = form ? form.querySelector('.ppi-edit-items') : null;
        if (!container) return;
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-2 align-items-start edit-item-row';
        const programs = window._ppiProgramOptions || [];
        let progOpts = `<option value="">-- Pilih program --</option>`;
        programs.forEach(p => {
          progOpts += `<option>${p}</option>`;
        });
        const cats = ['Akademik', 'Bina Diri', 'Motorik', 'Perilaku', 'Vokasi'];
        let catOpts = `<option value="">-- Kategori --</option>`;
        cats.forEach(c => {
          catOpts += `<option>${c}</option>`;
        });
        row.innerHTML = `<input type="hidden" name="item_id[]" value=""><select name="nama_program[]" class="form-select">${progOpts}</select><select name="kategori[]" class="form-select">${catOpts}</select><button type="button" class="btn btn-outline-danger btn-icon btn-sm" onclick="this.closest('.edit-item-row').remove()" aria-label="Hapus program"><i class='ri-delete-bin-line'></i></button>`;
        container.appendChild(row);
      }

      window.cancelPpiEdit = function(btn) {
        // if inside modal, hide modal
        const modal = btn.closest('.modal');
        if (modal) {
          const b = bootstrap.Modal.getInstance(modal);
          if (b) b.hide();
          return;
        }
        // otherwise try to restore inline details if present
        const details = btn.closest('.ppi-details');
        if (!details) return;
        if (details.dataset && details.dataset.prevHtml) {
          details.innerHTML = details.dataset.prevHtml;
          delete details.dataset.prevHtml;
        }
        details.style.display = 'none';
      }

      window.savePpiEdit = function(ppiId, form) {
        const data = new FormData(form);
        const items = [];
        const ids = data.getAll('item_id[]');
        const names = data.getAll('nama_program[]');
        const kategories = data.getAll('kategori[]');
        for (let i = 0; i < names.length; i++) {
          if (!names[i] || names[i].trim() === '') continue;
          items.push({
            id: ids[i] || null,
            nama_program: names[i].trim(),
            kategori: kategories[i] || null
          });
        }
        if (items.length === 0) {
          if (!confirm('Tidak ada program tersisa — simpan tetap akan menghapus semua item. Lanjutkan?')) return;
        }
        const payload = {
          program_items: items
        };
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/ppi/' + ppiId, {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        }).then(r => r.json()).then(res => {
          if (res.success) {
            // refresh modal list
            var lastBtn = document.querySelector('button[data-bs-target="#riwayatPpiModal"]:focus') || document.querySelector('button[data-bs-target="#riwayatPpiModal"]');
            if (lastBtn) loadRiwayatPpi(lastBtn);
            alert(res.message || 'PPI berhasil diperbarui');
          } else {
            alert(res.message || 'Gagal menyimpan perubahan');
          }
        }).catch(err => {
          console.error(err);
          alert('Terjadi kesalahan jaringan');
        });
      }
    });
  </script>
  @endpush