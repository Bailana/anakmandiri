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
          @if(!(auth()->user()->role === 'konsultan' && isset($currentKonsultanSpesRaw) && preg_match('/psikologi/i', $currentKonsultanSpesRaw)))
          <a href="{{ route('program-anak.daftar-program') }}" class="btn btn-outline-secondary me-2">
            <i class="ri-list-unordered me-2"></i>Daftar Program
          </a>
          @endif
          <a href="{{ route('program-anak.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Program Anak
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<div class="row">
  <div class="col-12">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('.alert-success[role="alert"]');
    if (!el) return;
    setTimeout(function() {
      try {
        bootstrap.Alert.getOrCreateInstance(el).close();
      } catch (e) {}
    }, 4000);
  });
</script>
@endif

<!-- Modal: Group Program List -->
<style>
  /* Smaller badges for program-anak view only */
  #programAnakTable .badge,
  #riwayatObservasiList .badge,
  #programGroupModal .badge,
  #programAllModal .badge,
  #programDetailModal .badge,
  #modalAddProgramMaster .badge {
    font-size: .65rem !important;
    padding: .18rem .35rem !important;
    border-radius: .35rem !important;
  }
</style>
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
        <div id="groupLatestKeterangan" class="mt-3 pt-2 border-top text-muted" style="display:block">
          <strong>Keterangan :</strong>
          <div id="groupLatestKeteranganText" class="mt-1">-</div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="me-auto">
          <div id="groupSuggestContainer" style="display:none">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="groupSuggestToggle">
              <label class="form-check-label" for="groupSuggestToggle">Sarankan Terapi</label>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-outline-secondary restore-previous-on-close" data-bs-dismiss="modal">Tutup</button>
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

  // Generic modal manager: hide any currently open modal, show the target, and restore previous when target closes
  function pushModalAndShow(targetModalEl) {
    try {
      window._modalStack = window._modalStack || [];
      const openModal = document.querySelector('.modal.show');
      if (openModal && openModal !== targetModalEl) {
        const prevId = openModal.id || null;
        if (prevId) window._modalStack.push(prevId);
        try {
          bootstrap.Modal.getOrCreateInstance(openModal).hide();
        } catch (e) {}

        // mark that this target's restore is already managed
        targetModalEl.dataset.modalStackManaged = '1';
        const restore = function() {
          try {
            // only restore previous modal when close was triggered by a designated button
            if (targetModalEl.dataset.restoreOnClose === '1') {
              const last = (window._modalStack && window._modalStack.length) ? window._modalStack.pop() : null;
              if (last) {
                const prevEl = document.getElementById(last);
                if (prevEl) bootstrap.Modal.getOrCreateInstance(prevEl).show();
              }
            } else {
              // user closed via backdrop/X or other means: drop the stacked id
              if (window._modalStack && window._modalStack.length) window._modalStack.pop();
            }
          } catch (e) {}
          targetModalEl.removeEventListener('hidden.bs.modal', restore);
          delete targetModalEl.dataset.modalStackManaged;
          delete targetModalEl.dataset.restoreOnClose;
        };
        targetModalEl.addEventListener('hidden.bs.modal', restore);
      }
    } catch (e) {}
    try {
      bootstrap.Modal.getOrCreateInstance(targetModalEl).show();
    } catch (e) {}
  }

  // Global modal show handler: ensure only one modal is visible at a time.
  // When a modal is about to show, hide any other open modal and restore it when the new modal closes.
  document.addEventListener('show.bs.modal', function(e) {
    try {
      const target = e.target;
      // if this modal's restore is already managed by pushModalAndShow, skip global handling
      if (target && target.dataset && target.dataset.modalStackManaged) return;
      window._modalStack = window._modalStack || [];
      const openModal = document.querySelector('.modal.show');
      if (openModal && openModal !== target) {
        const prevId = openModal.id || null;
        if (prevId) window._modalStack.push(prevId);
        try {
          bootstrap.Modal.getOrCreateInstance(openModal).hide();
        } catch (err) {}
        const restore = function() {
          try {
            if (target.dataset && target.dataset.restoreOnClose === '1') {
              const last = (window._modalStack && window._modalStack.length) ? window._modalStack.pop() : null;
              if (last) {
                const prev = document.getElementById(last);
                if (prev) bootstrap.Modal.getOrCreateInstance(prev).show();
              }
            } else {
              if (window._modalStack && window._modalStack.length) window._modalStack.pop();
            }
          } catch (err) {}
          target.removeEventListener('hidden.bs.modal', restore);
          delete target.dataset.restoreOnClose;
        };
        target.addEventListener('hidden.bs.modal', restore);
      }
    } catch (err) {}
  });

  window.showDetailProgramGroup = function(anakDidikId, konsultanId, fallbackId) {
    if (!konsultanId) {
      // fallback to single program detail
      return window.showDetailProgram(fallbackId);
    }
    const modalEl = document.getElementById('programGroupModal');
    const modal = new bootstrap.Modal(modalEl);
    const listDiv = document.getElementById('groupProgramList');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    // ensure any open modal is hidden and will be restored when this modal closes
    pushModalAndShow(modalEl);
    fetch(`/program-anak/riwayat-program/${anakDidikId}/konsultan/${konsultanId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Belum ada program dari konsultan ini.</div>';
          try {
            const latestEl = document.getElementById('groupLatestKeteranganText');
            if (latestEl) latestEl.textContent = '-';
          } catch (e) {}
          modal.show();
          return;
        }
        // render as table with columns matching 'Semua Program Anak' style
        window._lastGroup = {
          anakDidikId: anakDidikId,
          konsultanId: konsultanId,
          dateKey: null
        };
        // determine if any row is editable by current user so we can show/hide the Aksi column entirely
        let canEditAny = false;
        try {
          data.programs.forEach(p => {
            let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
            if (window.currentUser) {
              if (window.currentUser.role === 'admin') canEditAny = true;
              else if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEditAny = true;
            }
          });
        } catch (e) {}
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th>' + (canEditAny ? '<th>AKSI</th>' : '') + '</tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          // determine if current user may edit/delete this program
          let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
          let canEdit = false;
          if (window.currentUser) {
            if (window.currentUser.role === 'admin') canEdit = true;
            else if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEdit = true;
          }
          const actionsHtml = canEdit ? `<div class="d-flex gap-1"><button class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button></div>` : '';
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>`;
          if (canEditAny) html += `<td>${actionsHtml}</td>`;
          html += `</tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        // set latest keterangan (outside the table)
        try {
          const latestEl = document.getElementById('groupLatestKeteranganText');
          if (latestEl) {
            const latest = (data.programs[0] && (data.programs[0].keterangan || data.programs[0].keterangan === '')) ? (data.programs[0].keterangan || '-') : '-';
            latestEl.textContent = latest || '-';
          }
        } catch (e) {}
        // show/hide suggest toggle depending on konsultan spesialisasi (hide for 'pendidikan')
        try {
          const kt = (data.programs[0] && data.programs[0].konsultan && (data.programs[0].konsultan.spesialisasi || data.programs[0].konsultan.tipe || data.programs[0].konsultan.type)) ? (data.programs[0].konsultan.spesialisasi || data.programs[0].konsultan.tipe || data.programs[0].konsultan.type) : null;
          const groupSuggestContainer = document.getElementById('groupSuggestContainer');
          if (groupSuggestContainer) {
            if (kt && String(kt).toLowerCase().includes('pendidikan')) groupSuggestContainer.style.display = 'none';
            else groupSuggestContainer.style.display = 'block';
          }
        } catch (e) {}
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
    pushModalAndShow(modalEl);
    const listDiv = document.getElementById('groupProgramList');
    listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
    const url = `/program-anak/riwayat-program/${anakDidikId}/konsultan/${konsultanId}/date/${encodeURIComponent(dateKey)}`;
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          listDiv.innerHTML = '<div class="text-center text-muted">Tidak ada program pada tanggal tersebut.</div>';
          // ensure the suggest toggle reflects absence of suggested programs for this date
          const toggleEl = document.getElementById('groupSuggestToggle');
          if (toggleEl) toggleEl.checked = false;
          window._groupSuggest = false;
          const groupSuggestContainer = document.getElementById('groupSuggestContainer');
          if (groupSuggestContainer) groupSuggestContainer.style.display = 'none';
          try {
            const latestEl = document.getElementById('groupLatestKeteranganText');
            if (latestEl) latestEl.textContent = '-';
          } catch (e) {}
          modal.show();
          return;
        }
        // render as table with columns matching 'Semua Program Anak' style
        window._lastGroup = {
          anakDidikId: anakDidikId,
          konsultanId: konsultanId,
          dateKey: dateKey
        };
        // initialize Sarankan Terapi toggle from server-provided is_suggested flag
        try {
          const anySuggested = data.programs.some(p => p && (p.is_suggested === 1 || p.is_suggested === '1' || p.is_suggested === true));
          const toggleEl = document.getElementById('groupSuggestToggle');
          if (toggleEl) toggleEl.checked = !!anySuggested;
          window._groupSuggest = !!anySuggested;
        } catch (e) {
          window._groupSuggest = false;
        }
        // show/hide suggest toggle depending on konsultan spesialisasi (hide for 'pendidikan')
        try {
          const kt = (data.programs[0] && data.programs[0].konsultan && (data.programs[0].konsultan.spesialisasi || data.programs[0].konsultan.tipe || data.programs[0].konsultan.type)) ? (data.programs[0].konsultan.spesialisasi || data.programs[0].konsultan.tipe || data.programs[0].konsultan.type) : null;
          const groupSuggestContainer = document.getElementById('groupSuggestContainer');
          if (groupSuggestContainer) {
            if (kt && String(kt).toLowerCase().includes('pendidikan')) groupSuggestContainer.style.display = 'none';
            else groupSuggestContainer.style.display = 'block';
          }
        } catch (e) {}
        // enable/disable toggle depending on current user: only admin or konsultan owner may change
        try {
          const toggleEl = document.getElementById('groupSuggestToggle');
          if (toggleEl && window.currentUser) {
            const isAdmin = (window.currentUser.role === 'admin');
            const isOwnerKonsultan = (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanId));
            toggleEl.disabled = !(isAdmin || isOwnerKonsultan);
          }
        } catch (e) {}
        // determine if any row is editable by current user so we can show/hide the Aksi column entirely
        let canEditAny2 = false;
        try {
          data.programs.forEach(p => {
            let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
            if (window.currentUser) {
              if (window.currentUser.role === 'admin') canEditAny2 = true;
              else if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEditAny2 = true;
            }
          });
        } catch (e) {}
        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th>' + (canEditAny2 ? '<th>AKSI</th>' : '') + '</tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
          let canEdit = false;
          if (window.currentUser) {
            if (window.currentUser.role === 'admin') canEdit = true;
            else if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEdit = true;
          }
          const actionsHtml = canEdit ? `<div class="d-flex gap-1"><button class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button></div>` : '';
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>`;
          if (canEditAny2) html += `<td>${actionsHtml}</td>`;
          html += `</tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        try {
          const latestEl = document.getElementById('groupLatestKeteranganText');
          if (latestEl) {
            const latest = (data.programs[0] && (data.programs[0].keterangan || data.programs[0].keterangan === '')) ? (data.programs[0].keterangan || '-') : '-';
            latestEl.textContent = latest || '-';
          }
        } catch (e) {}
        modal.show();
      })
      .catch(() => {
        listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        modal.show();
      });
  }
</script>
<script>
  // keep group suggest toggle state available for other actions
  document.addEventListener('DOMContentLoaded', function() {
    window._groupSuggest = false;
    window.currentUser = {
      id: @json(Auth::id()),
      role: @json(optional(Auth::user()) - > role),
      konsultanId: @json($currentKonsultanId ?? null)
    };
    const toggle = document.getElementById('groupSuggestToggle');
    if (toggle) {
      toggle.addEventListener('change', function() {
        window._groupSuggest = !!toggle.checked;
        // if currently viewing a konsultan+date group, persist the change to the server
        if (window._lastGroup && window._lastGroup.dateKey) {
          const anakId = window._lastGroup.anakDidikId;
          const konsultanId = window._lastGroup.konsultanId;
          const dateKey = window._lastGroup.dateKey;
          const url = `/program-anak/${anakId}/konsultan/${konsultanId}/date/${encodeURIComponent(dateKey)}/suggest`;
          fetch(url, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              suggest: toggle.checked ? 1 : 0
            })
          }).then(res => res.json()).then(resp => {
            if (resp && resp.success) {
              showToast('Perubahan saran terapi tersimpan', 'success');
              // refresh current group view
              showProgramsByKonsultanAndDate(anakId, konsultanId, dateKey);
              // refresh riwayat modal list if open
              if (window.currentRiwayatAnakId) {
                const dummy = document.createElement('button');
                dummy.setAttribute('data-anak-didik-id', window.currentRiwayatAnakId);
                loadRiwayatObservasi(dummy);
              }
              // update table Saran Terapi cell for this anak
              refreshSaranTerapiForAnak(anakId);
            } else {
              showToast((resp && resp.message) || 'Gagal menyimpan saran', 'danger');
            }
          }).catch(() => {
            showToast('Gagal menyimpan saran', 'danger');
          });
        }
      });
    }
  });
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
              <th>Guru Fokus</th>
              <th>Saran Terapi</th>
              <!-- <th>Status</th> -->
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programAnak as $index => $program)
            <tr data-anak-id="{{ $program->anak_didik_id }}">
              <td>{{ ($programAnak->currentPage() - 1) * $programAnak->perPage() + $index + 1 }}</td>
              <td>{{ $program->anakDidik->nama ?? '-' }}</td>
              <td>{{ $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-' }}</td>
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
          Menampilkan {{ $programAnak->firstItem() ?? 0 }} hingga {{ $programAnak->lastItem() ?? 0 }} dari {{ $programAnak->total() ?? 0 }}
        </div>
        <nav>
          {{ $programAnak->links('pagination::bootstrap-4') }}
        </nav>
      </div>

      <script>
        // Wire restore-on-close buttons to mark their modal for restoration
        try {
          document.querySelectorAll('.restore-previous-on-close').forEach(btn => {
            btn.addEventListener('click', function(e) {
              const modal = btn.closest('.modal');
              if (modal) modal.dataset.restoreOnClose = '1';
            });
          });
        } catch (err) {}
      </script>
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
        // res.riwayat is an array of groups: {name, konsultan_id, spesialisasi, items: [...]}
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

            // determine if any item on this date was suggested
            const anySuggestedForDate = group.items.some(it => {
              let ik = '';
              if (it.created_at) {
                ik = (it.created_at.indexOf('T') !== -1) ? it.created_at.split('T')[0] : it.created_at.split(' ')[0];
              } else {
                ik = 'id_' + it.id;
              }
              return ik === dateKey && (it.is_suggested === 1 || it.is_suggested === '1' || it.is_suggested === true);
            });

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

            // determine badge from konsultan spesialisasi
            const spec = (group.spesialisasi || item.konsultan_spesialisasi || '') + '';
            const s = spec.toLowerCase();
            let badgeLabel = null,
              badgeClass = null;
            if (s.indexOf('wicara') !== -1 || s.indexOf('wic') !== -1) {
              badgeLabel = 'TW';
              badgeClass = 'bg-primary';
            } else if (s.indexOf('sensori') !== -1 || s.indexOf('integrasi') !== -1) {
              badgeLabel = 'SI';
              badgeClass = 'bg-success';
            } else if (s.indexOf('psikologi') !== -1 || s.indexOf('psiko') !== -1) {
              badgeLabel = 'TP';
              badgeClass = 'bg-warning text-dark';
            } else {
              // fallback: inspect kode of item
              const kode = (item.kode_program || '').toString().toUpperCase();
              if (kode.startsWith('SI')) {
                badgeLabel = 'SI';
                badgeClass = 'bg-success';
              } else if (kode.startsWith('WIC') || kode.startsWith('WICARA')) {
                badgeLabel = 'TW';
                badgeClass = 'bg-primary';
              } else if (kode.startsWith('PS')) {
                badgeLabel = 'TP';
                badgeClass = 'bg-warning text-dark';
              } else {
                badgeLabel = null;
                badgeClass = null;
              }
            }

            const konsultanId = group.konsultan_id || null;
            // show therapy-type badge only when any program on that date is suggested
            const badgeHtml = (anySuggestedForDate && badgeLabel) ? (' <span class="badge ' + badgeClass + ' ms-2">' + badgeLabel + '</span>') : '';
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <div><b>${hari}</b>, ${tanggal}${badgeHtml}</div>
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

  // Refresh the Saran Terapi badge for a specific anak row by fetching riwayat and checking is_suggested
  function refreshSaranTerapiForAnak(anakId) {
    if (!anakId) return;
    fetch('/program-anak/riwayat-program/' + anakId)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.riwayat)) return;
        // collect unique badges from all groups where any item is suggested
        const badges = [];
        data.riwayat.forEach(group => {
          const items = group.items || [];
          const anySuggested = items.some(it => (it.is_suggested === 1 || it.is_suggested === '1' || it.is_suggested === true));
          if (!anySuggested) return;
          // determine badge from konsultan spesialisasi or from a suggested item's kode
          const spec = (group.spesialisasi || (items[0] && items[0].konsultan_spesialisasi) || '') + '';
          const s = spec.toLowerCase();
          let label = null,
            cls = null;
          if (s.indexOf('wicara') !== -1 || s.indexOf('wic') !== -1) {
            label = 'TW';
            cls = 'bg-primary';
          } else if (s.indexOf('sensori') !== -1 || s.indexOf('integrasi') !== -1) {
            label = 'SI';
            cls = 'bg-success';
          } else if (s.indexOf('psikologi') !== -1 || s.indexOf('psiko') !== -1) {
            label = 'PS';
            cls = 'bg-warning text-dark';
          } else {
            // fallback: inspect kode_program from any suggested item
            const suggestedItem = items.find(it => (it.is_suggested === 1 || it.is_suggested === '1' || it.is_suggested === true));
            const kode = (suggestedItem && suggestedItem.kode_program) ? suggestedItem.kode_program.toString().toUpperCase() : '';
            if (kode.indexOf('SI') === 0) {
              label = 'SI';
              cls = 'bg-success';
            } else if (kode.indexOf('WIC') === 0 || kode.indexOf('WICARA') === 0) {
              label = 'TW';
              cls = 'bg-primary';
            } else if (kode.indexOf('PS') === 0) {
              label = 'PS';
              cls = 'bg-warning text-dark';
            } else {
              label = (spec.trim() ? spec.split(/\s+/).map(x => x[0].toUpperCase()).slice(0, 2).join('') : 'TW');
              cls = 'bg-info';
            }
          }
          if (label && !badges.some(b => b.label === label)) badges.push({
            label,
            cls
          });
        });

        const row = document.querySelector('tr[data-anak-id="' + anakId + '"]');
        if (!row) return;
        const cells = row.querySelectorAll('td');
        // Saran Terapi column is the 4th cell (index 3)
        const targetCell = cells[3];
        if (!targetCell) return;
        if (!badges || badges.length === 0) {
          targetCell.innerHTML = '-';
          return;
        }
        // render all badges (unique, in order found)
        const html = badges.map(b => `<span class="badge ${b.cls} me-1">${b.label}</span>`).join(' ');
        targetCell.innerHTML = html;
      }).catch(() => {});
  }

  // On page load, refresh Saran Terapi for all visible anak rows to reflect combined suggestions
  document.addEventListener('DOMContentLoaded', function() {
    try {
      document.querySelectorAll('tr[data-anak-id]').forEach(tr => {
        const id = tr.getAttribute('data-anak-id');
        if (id) refreshSaranTerapiForAnak(id);
      });
    } catch (e) {}
  });

  // Cleanup helper to remove stray backdrops and modal-open class
  function cleanupModalBackdrops() {
    try {
      // if no modal is currently visible, remove any leftover backdrops and body class
      const anyOpen = document.querySelectorAll('.modal.show').length > 0;
      if (!anyOpen) {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
      }
    } catch (e) {
      // ignore
    }
  }

  // Attach cleanup on modal hidden events to ensure UI is interactive after closing
  ['riwayatObservasiModal', 'programGroupModal', 'programAllModal', 'programEditModal', 'programDetailModal', 'modalAddProgramMaster'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('hidden.bs.modal', cleanupModalBackdrops);
  });
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
          <select id="editKodeProgram" class="form-select">
            <option value="">Memuat...</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Program</label>
          <input id="editNamaProgram" class="form-control" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Tujuan</label>
          <textarea id="editTujuan" class="form-control" rows="3" disabled></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Aktivitas</label>
          <textarea id="editAktivitas" class="form-control" rows="3" disabled></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary restore-previous-on-close" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveEditProgram">Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
  function openEditProgramModal(id) {
    const modalEl = document.getElementById('programEditModal');
    const modal = new bootstrap.Modal(modalEl);
    // If the programGroupModal is currently visible, hide it and restore it when edit modal closes.
    // Otherwise, fall back to hiding/restoring the riwayat modal as before.
    try {
      pushModalAndShow(modalEl);
    } catch (e) {
      hideRiwayatBeforeShow(modalEl);
    }
    fetch('/program-anak/' + id + '/json')
      .then(res => res.json())
      .then(data => {
        if (!data.success) return showToast('Gagal mengambil data', 'danger');
        const p = data.program;
        document.getElementById('editProgramId').value = p.id;
        const kodeEl = document.getElementById('editKodeProgram');
        const namaEl = document.getElementById('editNamaProgram');
        const tujuanEl = document.getElementById('editTujuan');
        const aktivitasEl = document.getElementById('editAktivitas');

        // reset fields
        namaEl.value = p.nama_program || '';
        tujuanEl.value = p.tujuan || '';
        aktivitasEl.value = p.aktivitas || '';

        // If program has konsultan info, try to load konsultan's master list to populate select
        const konsultanId = p.konsultan && p.konsultan.id ? p.konsultan.id : null;
        const parent = kodeEl.parentElement;
        if (konsultanId) {
          // fetch program_konsultan list
          fetch('/program-anak/program-konsultan/konsultan/' + konsultanId + '/list-json')
            .then(r => r.json())
            .then(listResp => {
              // ensure element still present
              let sel = document.getElementById('editKodeProgram');
              if (!sel) return;
              sel.innerHTML = '';
              if (!listResp.success || !Array.isArray(listResp.program_konsultan) || listResp.program_konsultan.length === 0) {
                // no master list: convert to input and allow editing
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'editKodeProgram';
                input.className = 'form-control';
                input.value = p.kode_program || '';
                parent.replaceChild(input, sel);
                namaEl.disabled = false;
                tujuanEl.disabled = false;
                aktivitasEl.disabled = false;
                return;
              }
              // populate select options
              listResp.program_konsultan.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = (item.kode_program ? item.kode_program + ' — ' : '') + (item.nama_program || '');
                opt.dataset.kode = item.kode_program || '';
                opt.dataset.nama = item.nama_program || '';
                opt.dataset.tujuan = item.tujuan || '';
                opt.dataset.aktivitas = item.aktivitas || '';
                sel.appendChild(opt);
              });
              // select the matching program_konsultan if available, otherwise try to match by kode_program
              let selected = null;
              if (p.program_konsultan_id) {
                selected = sel.querySelector('option[value="' + p.program_konsultan_id + '"]');
              }
              if (!selected && p.kode_program) {
                selected = Array.from(sel.options).find(o => (o.dataset.kode || '').toString().toUpperCase() === (p.kode_program || '').toString().toUpperCase());
              }
              if (selected) {
                sel.value = selected.value;
                namaEl.value = selected.dataset.nama || '';
                tujuanEl.value = selected.dataset.tujuan || '';
                aktivitasEl.value = selected.dataset.aktivitas || '';
                namaEl.disabled = true;
                tujuanEl.disabled = true;
                aktivitasEl.disabled = true;
              } else {
                // no matching: allow editing
                namaEl.disabled = false;
                tujuanEl.disabled = false;
                aktivitasEl.disabled = false;
              }

              // on change, update fields to reflect selected kode
              sel.addEventListener('change', function() {
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset) {
                  namaEl.value = opt.dataset.nama || '';
                  tujuanEl.value = opt.dataset.tujuan || '';
                  aktivitasEl.value = opt.dataset.aktivitas || '';
                  namaEl.disabled = true;
                  tujuanEl.disabled = true;
                  aktivitasEl.disabled = true;
                } else {
                  namaEl.disabled = false;
                  tujuanEl.disabled = false;
                  aktivitasEl.disabled = false;
                }
              });

            }).catch(() => {
              // on error, fallback to simple input
              const sel = document.getElementById('editKodeProgram');
              if (sel) {
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'editKodeProgram';
                input.className = 'form-control';
                input.value = p.kode_program || '';
                parent.replaceChild(input, sel);
              }
              namaEl.disabled = false;
              tujuanEl.disabled = false;
              aktivitasEl.disabled = false;
            });
        } else {
          // no konsultan info; show kode as input and allow editing
          const sel = document.getElementById('editKodeProgram');
          const input = document.createElement('input');
          input.type = 'text';
          input.id = 'editKodeProgram';
          input.className = 'form-control';
          input.value = p.kode_program || '';
          parent.replaceChild(input, sel);
          namaEl.disabled = false;
          tujuanEl.disabled = false;
          aktivitasEl.disabled = false;
        }
      }).catch(() => showToast('Gagal mengambil data', 'danger'));
  }

  document.getElementById('btnSaveEditProgram').addEventListener('click', function() {
    const id = document.getElementById('editProgramId').value;
    // determine kode_program value depending on whether editKodeProgram is a select or input
    const kodeEl = document.getElementById('editKodeProgram');
    let kodeVal = '';
    if (kodeEl) {
      if (kodeEl.tagName && kodeEl.tagName.toLowerCase() === 'select') {
        const opt = kodeEl.options[kodeEl.selectedIndex];
        kodeVal = (opt && opt.dataset && opt.dataset.kode) ? opt.dataset.kode : (opt ? opt.text : '');
      } else {
        kodeVal = kodeEl.value || '';
      }
    }
    const payload = {
      kode_program: kodeVal,
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
    // hide any open modal and restore it when this modal closes
    pushModalAndShow(modalEl);
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