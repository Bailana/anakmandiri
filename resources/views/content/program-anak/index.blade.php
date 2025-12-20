@extends('layouts/contentNavbarLayout')

@section('title', 'Program Anak')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Program Anak</h4>
          <p class="text-body-secondary mb-0">Kelola program anak didik</p>
        </div>
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
        <div class="d-flex align-items-center">
          <a href="{{ route('program-anak.daftar-program') }}" class="btn btn-outline-secondary me-2">
            <i class="ri-list-unordered me-2"></i>Daftar Program
          </a>
          <a href="{{ route('program-anak.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Program Anak
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Modal: Group Program List -->
<div class="modal fade" id="programGroupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Daftar Program dari Konsultan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="groupProgramList">
          <div class="text-center text-muted">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Helper: if riwayat modal is open, hide it before showing another modal and restore after
  function hideRiwayatBeforeShow(targetModalEl) {
    try {
      const riwayatEl = document.getElementById('riwayatObservasiModal');
      if (!riwayatEl) return;
      const riwayatInstance = bootstrap.Modal.getInstance(riwayatEl);
      window._riwayatWasShown = !!riwayatInstance;
      if (window._riwayatWasShown) riwayatInstance.hide();

      const restore = function() {
        if (window._riwayatWasShown) {
          bootstrap.Modal.getOrCreateInstance(riwayatEl).show();
        }
        targetModalEl.removeEventListener('hidden.bs.modal', restore);
        window._riwayatWasShown = false;
      };
      targetModalEl.addEventListener('hidden.bs.modal', restore);
    } catch (e) {
      // ignore
    }
  }

  window.showDetailProgramGroup = function(anakDidikId, konsultanId, fallbackId) {
    if (!konsultanId) {
      // fallback to single program detail
      return window.showDetailProgram(fallbackId);
    }
    const modalEl = document.getElementById('programGroupModal');
    const modal = new bootstrap.Modal(modalEl);
    const listDiv = document.getElementById('groupProgramList');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    // ensure riwayat modal (if open) is hidden and will be restored when this modal closes
    hideRiwayatBeforeShow(modalEl);
    fetch(`/program-anak/riwayat-program/${anakDidikId}/konsultan/${konsultanId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Belum ada program dari konsultan ini.</div>';
          modal.show();
          return;
        }
        // render as table with columns matching 'Semua Program Anak' style
        window._lastGroup = {
          anakDidikId: anakDidikId,
          konsultanId: konsultanId,
          dateKey: null
        };
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th><th>AKSI</th></tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button>
              </div>
            </td>
          </tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        modal.show();
      })
      .catch(() => {
        listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        modal.show();
      });
  }

  // Show programs from a specific konsultan for an anak on a given date (YYYY-MM-DD)
  window.showProgramsByKonsultanAndDate = function(anakDidikId, konsultanId, dateKey) {
    if (!konsultanId) {
      // fallback: show all programs for anak
      return window.showAllProgramsForAnak(anakDidikId);
    }
    const modalEl = document.getElementById('programGroupModal');
    const modal = new bootstrap.Modal(modalEl);
    hideRiwayatBeforeShow(modalEl);
    const listDiv = document.getElementById('groupProgramList');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    const url = `/program-anak/riwayat-program/${anakDidikId}/konsultan/${konsultanId}/date/${encodeURIComponent(dateKey)}`;
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Tidak ada program pada tanggal tersebut.</div>';
          modal.show();
          return;
        }
        // render as table with columns matching 'Semua Program Anak' style
        window._lastGroup = {
          anakDidikId: anakDidikId,
          konsultanId: konsultanId,
          dateKey: dateKey
        };
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th><th>AKSI</th></tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button>
              </div>
            </td>
          </tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        modal.show();
      })
      .catch(() => {
        listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        modal.show();
      });
  }
</script>
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('program-anak.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width:200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau program..."
          value="{{ request('search') }}">
      </div>
      <div>
        <button type="submit" class="btn btn-outline-primary" title="Cari">
          <i class="ri-search-line"></i>
        </button>
      </div>
      <div>
        <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary" title="Reset"><i
            class="ri-refresh-line"></i></a>
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="programAnakTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Nama Anak</th>
              <th>Program</th>
              <th>Saran Terapi</th>
              <!-- <th>Status</th> -->
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programAnak as $index => $program)
            <tr>
              <td>{{ ($programAnak->currentPage() - 1) * $programAnak->perPage() + $index + 1 }}</td>
              <td>{{ $program->anakDidik->nama ?? '-' }}</td>
              <td>{{ $program->nama_program }}</td>
              <td>
                @php
                $pk = $program->programKonsultan ?? null;
                $konsultanSpesRaw = optional($pk)->konsultan->spesialisasi ?? null;
                if (!$konsultanSpesRaw && isset($currentKonsultanSpesRaw)) {
                $konsultanSpesRaw = $currentKonsultanSpesRaw;
                }
                $konsultanSpes = strtolower($konsultanSpesRaw ?? '');
                $badge = null;
                if ($program->is_suggested && $konsultanSpesRaw) {
                if (str_contains($konsultanSpes, 'wicara')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_contains($konsultanSpes, 'sensori') || str_contains($konsultanSpes, 'integrasi')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_contains($konsultanSpes, 'psikologi')) {
                $badge = ['label' => 'PS', 'class' => 'bg-warning text-dark'];
                } else {
                // try to derive from kode_program if available (e.g. SI-001, WIC-001, PS-001)
                $kode = strtoupper($program->kode_program ?? '');
                if (str_starts_with($kode, 'SI')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_starts_with($kode, 'WIC') || str_starts_with($kode, 'WICARA')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_starts_with($kode, 'PS')) {
                $badge = ['label' => 'PS', 'class' => 'bg-warning text-dark'];
                } else {
                $parts = preg_split('/\s+/', trim($konsultanSpesRaw));
                $initials = '';
                foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= strtoupper(mb_substr($p, 0, 1));
                }
                $label = $initials ?: strtoupper(substr($konsultanSpesRaw, 0, 2));
                $badge = ['label' => $label, 'class' => 'bg-info'];
                }
                }
                }
                @endphp
                @if($badge)
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                @else
                -
                @endif
              </td>
              <!-- <td><span class="badge bg-label-success">{{ ucfirst($program->status) }}</span></td> -->
              <td>
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                  data-bs-target="#riwayatObservasiModal" data-anak-didik-id="{{ $program->anak_didik_id }}"
                  onclick="loadRiwayatObservasi(this)" title="Riwayat Program">
                  <i class="ri-history-line"></i>
                </button>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('program-anak.edit', $program->id) }}" class="btn btn-sm btn-outline-warning"
                  title="Edit"><i class="ri-edit-line"></i></a>
                <form action="{{ route('program-anak.destroy', $program->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Yakin ingin menghapus?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i
                      class="ri-delete-bin-line"></i></button>
                </form>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $programAnak->firstItem() ?? 0 }} hingga {{ $programAnak->lastItem() ?? 0 }} dari
          {{ $programAnak->total() }} data
        </div>
        <nav>
          {{ $programAnak->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>


<!-- Modal Riwayat Program Anak -->
<div class="modal fade" id="riwayatObservasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat Program Anak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatObservasiList">
          <div class="text-center text-muted">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  function resetDetailModal() {
    const ids = [
      'detailNamaProgram', 'detailKategori', 'detailAnakDidik', 'detailGuruFokus', 'detailKonsultan',
      'detailTanggalMulai', 'detailTanggalSelesai', 'detailDeskripsi', 'detailTargetPembelajaran',
      'detailCatatanKonsultan', 'detailStatus', 'detailKemampuan', 'detailWawancara',
      'detailKemampuanSaatIni', 'detailSaranRekomendasi'
    ];
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        if (id === 'detailKemampuan') {
          el.innerHTML = '';
        } else {
          el.textContent = '';
        }
      }
    });
  }

  window.loadRiwayatObservasi = function(btn) {
    resetDetailModal();
    var programId = btn.getAttribute('data-program-id');
    var listDiv = document.getElementById('riwayatObservasiList');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    var anakDidikId = btn.getAttribute('data-anak-didik-id') || programId;
    // remember current anak id for 'Lihat Semua' actions
    window.currentRiwayatAnakId = anakDidikId;
    var currentUserId = @json(Auth::id());
    fetch('/program-anak/riwayat-program/' + anakDidikId)
      .then(response => response.json())
      .then(res => {
        if (!res.success || !res.riwayat || res.riwayat.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat program.</div>';
          return;
        }
        // res.riwayat is an array of groups: {name, items: [...]}
        let html = '';
        res.riwayat.forEach(group => {
          html += `<div class="mb-3">
              <div class="fw-bold bg-light p-2 rounded border mb-2"><i class='ri-user-line me-1'></i> ${group.name}</div>
              <ul class="list-group">`;
          // collapse items with the same date per konsultan: show each date only once
          const seenDates = new Set();
          group.items.forEach(item => {
            // derive date-only key (YYYY-MM-DD)
            let dateKey = '';
            if (item.created_at) {
              // if datetime present, take date portion
              dateKey = (item.created_at.indexOf('T') !== -1) ? item.created_at.split('T')[0] : item.created_at.split(' ')[0];
            }
            if (!dateKey) {
              // fallback to unique id if no date
              dateKey = 'id_' + item.id;
            }
            if (seenDates.has(dateKey)) return; // skip duplicate date
            seenDates.add(dateKey);

            // format date and weekday for display
            let dt = item.created_at ? new Date(item.created_at) : null;
            let hari = dt ? dt.toLocaleDateString('id-ID', {
              weekday: 'long'
            }) : '';
            let tanggal = dt ? dt.toLocaleDateString('id-ID', {
              day: '2-digit',
              month: 'long',
              year: 'numeric'
            }) : (item.created_at || '');

            // clicking 'Lihat' opens the konsultan-specific group modal (shows all programs from that konsultan for the anak)
            const konsultanId = group.konsultan_id || null;
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <div><b>${hari}</b>, ${tanggal}</div>
                <div>
                  <button class="btn btn-sm btn-outline-info" onclick="showProgramsByKonsultanAndDate(${anakDidikId}, ${konsultanId}, '${dateKey}')" title="Lihat Program dari Konsultan"><i class="ri-eye-line"></i></button>
                </div>
              </li>`;
          });
          html += '</ul></div>';
        });
        listDiv.innerHTML = html;
      })
      .catch(() => {
        listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
      });
  }

  function editObservasi(id) {
    alert('Edit observasi ID: ' + id);
  }

  function hapusObservasi(id) {
    if (!confirm('Yakin ingin menghapus observasi ini?')) return;
    fetch('/program/observasi-program/' + id, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(res => {
        if (res.success) {
          showToast('Berhasil dihapus', 'success');
          var detailModalEl = document.getElementById('detailModal');
          var detailModal = detailModalEl ? bootstrap.Modal.getInstance(detailModalEl) : null;
          if (detailModal) detailModal.hide();
          var modal = document.getElementById('riwayatObservasiModal');
          if (modal) {
            var anakDidikId = null;
            var lastBtn = document.querySelector(
              'button[data-bs-target="#riwayatObservasiModal"].active, button[data-bs-target="#riwayatObservasiModal"]:focus'
            );
            if (!lastBtn) lastBtn = document.querySelector('button[data-bs-target="#riwayatObservasiModal"]');
            if (lastBtn) anakDidikId = lastBtn.getAttribute('data-anak-didik-id');
            if (anakDidikId) {
              var dummyBtn = document.createElement('button');
              dummyBtn.setAttribute('data-anak-didik-id', anakDidikId);
              loadRiwayatObservasi(dummyBtn);
            }
          }
        } else {
          showToast('Gagal menghapus data', 'danger');
        }
      });
  }

  function showToast(message, type = 'success') {
    let toast = document.getElementById('customToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'customToast';
      toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
      toast.style.zIndex = 9999;
      toast.innerHTML =
        '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      document.body.appendChild(toast);
    } else {
      toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
    }
    toast.querySelector('.toast-body').textContent = message;
    var bsToast = bootstrap.Toast.getOrCreateInstance(toast, {
      delay: 2000
    });
    bsToast.show();
  }
</script>
<!-- Modal: All Programs for Anak -->
<div class="modal fade" id="programAllModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Semua Program Anak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="programAllContent">
          <div class="text-center text-muted">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Program (AJAX) -->
<div class="modal fade" id="programEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Program Anak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editProgramId">
        <div class="mb-3">
          <label class="form-label">Kode Program</label>
          <input id="editKodeProgram" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Program</label>
          <input id="editNamaProgram" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Tujuan</label>
          <textarea id="editTujuan" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Aktivitas</label>
          <textarea id="editAktivitas" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveEditProgram">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
  function openEditProgramModal(id) {
    const modalEl = document.getElementById('programEditModal');
    const modal = new bootstrap.Modal(modalEl);
    hideRiwayatBeforeShow(modalEl);
    fetch('/program-anak/' + id + '/json')
      .then(res => res.json())
      .then(data => {
        if (!data.success) return showToast('Gagal mengambil data', 'danger');
        const p = data.program;
        document.getElementById('editProgramId').value = p.id;
        document.getElementById('editKodeProgram').value = p.kode_program || '';
        document.getElementById('editNamaProgram').value = p.nama_program || '';
        document.getElementById('editTujuan').value = p.tujuan || '';
        document.getElementById('editAktivitas').value = p.aktivitas || '';
        modal.show();
      }).catch(() => showToast('Gagal mengambil data', 'danger'));
  }

  document.getElementById('btnSaveEditProgram').addEventListener('click', function() {
    const id = document.getElementById('editProgramId').value;
    const payload = {
      kode_program: document.getElementById('editKodeProgram').value,
      nama_program: document.getElementById('editNamaProgram').value,
      tujuan: document.getElementById('editTujuan').value,
      aktivitas: document.getElementById('editAktivitas').value
    };
    fetch('/program-anak/' + id + '/update-json', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    }).then(res => res.json()).then(res => {
      if (res.success) {
        showToast(res.message || 'Berhasil diupdate', 'success');
        const modalEl = document.getElementById('programEditModal');
        bootstrap.Modal.getInstance(modalEl).hide();
        // refresh current group view
        if (window._lastGroup) {
          if (window._lastGroup.dateKey) {
            showProgramsByKonsultanAndDate(window._lastGroup.anakDidikId, window._lastGroup.konsultanId, window._lastGroup.dateKey);
          } else {
            showDetailProgramGroup(window._lastGroup.anakDidikId, window._lastGroup.konsultanId, id);
          }
        }
      } else {
        showToast(res.message || 'Gagal update', 'danger');
      }
    }).catch(() => showToast('Gagal update', 'danger'));
  });

  function deleteProgramAndRefresh(id) {
    if (!confirm('Yakin ingin menghapus program ini?')) return;
    fetch('/program-anak/' + id + '/delete-json', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      }
    }).then(res => res.json()).then(res => {
      if (res.success) {
        showToast(res.message || 'Berhasil dihapus', 'success');
        // refresh current group view
        if (window._lastGroup) {
          if (window._lastGroup.dateKey) {
            showProgramsByKonsultanAndDate(window._lastGroup.anakDidikId, window._lastGroup.konsultanId, window._lastGroup.dateKey);
          } else {
            showDetailProgramGroup(window._lastGroup.anakDidikId, window._lastGroup.konsultanId, 0);
          }
        }
      } else {
        showToast(res.message || 'Gagal hapus', 'danger');
      }
    }).catch(() => showToast('Gagal hapus', 'danger'));
  }
</script>
<script>
  window.showAllProgramsForAnak = function(anakId) {
    if (!anakId) {
      alert('ID anak tidak tersedia');
      return;
    }
    const modalEl = document.getElementById('programAllModal');
    const modal = new bootstrap.Modal(modalEl);
    // if riwayat modal is open, hide it and restore after closing this modal
    hideRiwayatBeforeShow(modalEl);
    const target = document.getElementById('programAllContent');
    target.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    fetch('/program-anak/' + anakId + '/all-json')
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          target.innerHTML = '<div class="text-center text-muted">Belum ada program untuk anak ini.</div>';
          modal.show();
          return;
        }
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">'
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th></tr></thead><tbody>';
        data.programs.forEach(p => {
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${p.konsultan ? p.konsultan.nama : '-'}</td>
          </tr>`;
        });
        html += '</tbody></table></div>';
        target.innerHTML = html;
        modal.show();
      })
      .catch(() => {
        target.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        modal.show();
      });
  }
</script>
<!-- Modal: Tambah Daftar Program -->
<!-- Modal: Detail Program Anak -->
<div class="modal fade" id="programDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Nama Program</p>
            <p class="fw-medium" id="detailNamaProgram">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Kode Program</p>
            <p class="fw-medium" id="detailKodeProgram">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Anak Didik</p>
            <p class="fw-medium" id="detailAnakDidik">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Konsultan</p>
            <p class="fw-medium" id="detailKonsultan">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Tujuan</p>
            <p id="detailTujuan">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Aktivitas</p>
            <p id="detailAktivitas">-</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Periode</p>
            <p id="detailPeriode">-</p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Dibuat</p>
            <p id="detailCreatedAt">-</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.showDetailProgram = function(id) {
    const modalEl = document.getElementById('programDetailModal');
    const modal = new bootstrap.Modal(modalEl);
    hideRiwayatBeforeShow(modalEl);
    fetch('/program-anak/' + id + '/json')
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert('Gagal mengambil detail program');
          return;
        }
        const p = data.program;
        document.getElementById('detailNamaProgram').textContent = p.nama_program || '-';
        document.getElementById('detailKodeProgram').textContent = p.kode_program || '-';
        document.getElementById('detailAnakDidik').textContent = p.anak ? p.anak.nama : '-';
        document.getElementById('detailKonsultan').textContent = p.konsultan ? p.konsultan.nama : '-';
        document.getElementById('detailTujuan').textContent = p.tujuan || '-';
        document.getElementById('detailAktivitas').textContent = p.aktivitas || '-';
        document.getElementById('detailPeriode').textContent = (p.periode_mulai ? p.periode_mulai : '-') + ' — ' + (
          p.periode_selesai ? p.periode_selesai : '-');
        document.getElementById('detailCreatedAt').textContent = p.created_at || '-';
        modal.show();
      })
      .catch(() => alert('Gagal mengambil detail program'));
  }
</script>
<div class="modal fade" id="modalAddProgramMaster" tabindex="-1" aria-labelledby="modalAddProgramMasterLabel"
  aria-hidden="true">
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
          <input type="text" name="kode_program" class="form-control">
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

@endsection