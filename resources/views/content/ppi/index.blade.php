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
          <div>
            <a href="{{ route('ppi.create') }}" class="btn btn-primary">
              <i class="ri-add-line me-2"></i>Tambah PPI
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alert Messages -->
<div id="ppi-alert-wrapper">@if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif</div>

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
              <th>Status</th>
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
                @php
                $s = strtolower(trim($statusMap[$anak->id] ?? ''));
                @endphp
                @if($s === 'menunggu' || $s === 'pending' || $s === '')
                <span class="badge bg-warning">Menunggu</span>
                @elseif($s === 'disetujui' || $s === 'approved')
                <span class="badge bg-success">Disetujui</span>
                @elseif($s === 'revisi' || $s === 'rejected' || $s === 'revise')
                <span class="badge bg-danger">Revisi</span>
                @else
                <span class="badge bg-secondary">{{ ucfirst($statusMap[$anak->id] ?? '-') }}</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  @if(isset($isKonsultanPendidikan) && $isKonsultanPendidikan)
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#riwayatPpiModal" data-anak-didik-id="{{ $anak->id }}" data-is-fokus="{{ isset($isFokusMap[$anak->id]) && $isFokusMap[$anak->id] ? '1' : '0' }}" onclick="loadRiwayatPpi(this)" title="Lihat" aria-label="Lihat">
                    <i class="ri-eye-line"></i>
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
      const el = document.getElementById('ppi-alert');
      el.innerHTML = `<div class="alert alert-${type} alert-dismissible">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
      el.style.display = 'block';
    }

    document.querySelectorAll('.btn-request-access').forEach(btn => {
      btn.addEventListener('click', function() {
        const anakId = this.dataset.id;
        if (!confirm('Kirim permintaan akses ke guru fokus?')) return;
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
        }).then(r => r.json()).then(j => {
          if (j.success) {
            showAlert(j.message || 'Permintaan berhasil dikirim');
          } else {
            showAlert(j.message || 'Terjadi kesalahan', 'danger');
          }
        }).catch(err => {
          showAlert('Terjadi kesalahan jaringan', 'danger');
        });
      });
    });

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
            // compact row: show created date and action buttons
            html += `<div class="mb-2 p-2 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                          <div class="text-muted small">${formatDateIndo(item.created_at)}</div>
                          <div class="text-end">`;
            // view button toggles details
            html += `<button class="btn btn-sm btn-outline-info me-1" onclick="viewPpiDetail(this)" data-ppi-id="${item.id}" title="Lihat"><i class='ri-eye-line'></i></button>`;
            if (isFokus) {
              html += `<button class="btn btn-sm btn-outline-secondary me-1" onclick="editPpi(this)" data-ppi-id="${item.id}" title="Edit"><i class='ri-edit-2-line'></i></button>`;
              html += `<button class="btn btn-sm btn-outline-danger" onclick="deletePpi(${item.id})" title="Hapus"><i class='ri-delete-bin-line'></i></button>`;
            } else if (canApprove && item.status !== 'disetujui') {
              html += `<button class="btn btn-sm btn-success" onclick="approvePpi(${item.id})" title="Setujui"><i class='ri-check-line'></i></button>`;
            } else {
              html += `<span class="badge bg-secondary">${item.status || 'aktif'}</span>`;
            }
            html += `</div>
                        </div>
                        <div class="ppi-details mt-2" style="display:none;">
                          <div><strong>Program yang diinput:</strong></div>
                          <ul class="mb-1">`;
            if (item.items && item.items.length) {
              item.items.forEach(it => {
                html += `<li>${it.nama_program}${it.kategori ? ' — ' + it.kategori : ''}</li>`;
              });
            } else {
              html += `<li>(tidak ada program tercatat)</li>`;
            }
            html += `</ul>
                          <div class="text-muted small">${item.periode_mulai ? formatDateIndo(item.periode_mulai) + (item.periode_selesai ? ' s/d ' + formatDateIndo(item.periode_selesai) : '') : ''}</div>
                          <div class="text-muted small mt-1">${item.keterangan || ''}</div>
                        </div>
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

    window.viewPpiDetail = function(btnOrId) {
      // If called with a DOM element (button), toggle inline details
      try {
        if (btnOrId && btnOrId.tagName) {
          const btn = btnOrId;
          const wrapper = btn.closest('.mb-2');
          if (!wrapper) return;
          const details = wrapper.querySelector('.ppi-details');
          if (!details) return;
          if (details.style.display === 'none' || details.style.display === '') {
            details.style.display = 'block';
            btn.classList.add('active');
          } else {
            details.style.display = 'none';
            btn.classList.remove('active');
          }
          return;
        }
      } catch (e) {
        // fall through to redirect if something unexpected
      }
      // fallback: if called with an id, redirect to show page
      if (typeof btnOrId === 'number' || (!btnOrId || !btnOrId.tagName)) {
        const id = btnOrId;
        window.location.href = '/ppi/' + id;
      }
    }

    // Enter edit mode for a PPI entry (guru fokus)
    window.editPpi = function(btn) {
      try {
        const ppiId = btn.getAttribute('data-ppi-id');
        if (!ppiId) return;
        const wrapper = btn.closest('.mb-2');
        const details = wrapper.querySelector('.ppi-details');
        if (!details) return;
        // find item data from global map
        const entry = (window._ppiRiwayat || []).find(x => String(x.id) === String(ppiId));
        const items = entry && entry.items ? entry.items : [];
        // build program options from all riwayat entries if available
        const programOptions = (window._ppiProgramOptions || []).slice();
        // ensure current item names are included
        items.forEach(it => {
          if (it.nama_program && !programOptions.includes(it.nama_program)) programOptions.push(it.nama_program);
        });

        // build editable form with dropdowns
        let formHtml = `<form class="ppi-edit-form" onsubmit="event.preventDefault(); savePpiEdit(${ppiId}, this);">
                          <div class="mb-2"><strong>Program yang diinput (edit):</strong></div>
                          <div class="ppi-edit-items">`;
        if (items.length) {
          items.forEach(it => {
            // build program select options
            let progOpts = '';
            progOpts += `<option value="">-- Pilih program --</option>`;
            programOptions.forEach(po => {
              const sel = (po === it.nama_program) ? 'selected' : '';
              progOpts += `<option ${sel}>${po}</option>`;
            });
            // category options
            const cats = ['Akademik', 'Bina Diri', 'Motorik', 'Perilaku', 'Vokasi'];
            let catOpts = `<option value="">-- Kategori --</option>`;
            cats.forEach(c => {
              const s = (c === it.kategori) ? 'selected' : '';
              catOpts += `<option ${s}>${c}</option>`;
            });

            formHtml += `<div class="d-flex gap-2 mb-2 align-items-start edit-item-row">
                          <input type="hidden" name="item_id[]" value="${it.id}">
                          <select name="nama_program[]" class="form-select">${progOpts}</select>
                          <select name="kategori[]" class="form-select">${catOpts}</select>
                          <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.edit-item-row').remove()"><i class='ri-close-line'></i></button>
                        </div>`;
          });
        } else {
          formHtml += `<div class="text-muted small">(tidak ada program tercatat)</div>`;
        }

        formHtml += `</div>
                     <div class="d-flex gap-2 mt-2">
                       <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPpiEditRow(this)"><i class='ri-add-line'></i></button>
                       <button type="submit" class="btn btn-sm btn-primary"><i class='ri-save-3-line'></i></button>
                       <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelPpiEdit(this)"><i class='ri-close-line'></i></button>
                     </div>
                   </form>`;
        details.dataset.prevHtml = details.innerHTML;
        details.innerHTML = formHtml;
        details.style.display = 'block';
      } catch (e) {
        console.error(e);
      }
    }

    window.addPpiEditRow = function(btn) {
      const container = btn.closest('.ppi-details').querySelector('.ppi-edit-items');
      if (!container) return;
      const row = document.createElement('div');
      row.className = 'd-flex gap-2 mb-2 align-items-start edit-item-row';
      // build select options from global program options
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
      row.innerHTML = `<input type="hidden" name="item_id[]" value=""><select name="nama_program[]" class="form-select">${progOpts}</select><select name="kategori[]" class="form-select">${catOpts}</select><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.edit-item-row').remove()"><i class='ri-close-line'></i></button>`;
      container.appendChild(row);
    }

    window.cancelPpiEdit = function(btn) {
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