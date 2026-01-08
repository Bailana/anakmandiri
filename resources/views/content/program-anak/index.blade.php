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
        @if(auth()->check())
        <div class="d-flex align-items-center">
          @if(auth()->user()->role === 'admin' || auth()->user()->role === 'konsultan')
          @if(!(auth()->user()->role === 'konsultan' && isset($currentKonsultanSpesRaw) && preg_match('/psikologi/i', $currentKonsultanSpesRaw)))
          <a href="{{ route('program-anak.daftar-program') }}" class="btn btn-outline-secondary me-2 d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-list-unordered" style="font-size:1.5em;"></i>
          </a>
          <a href="{{ route('program-anak.daftar-program') }}" class="btn btn-outline-secondary me-2 d-none d-sm-inline-flex align-items-center">
            <i class="ri-list-unordered me-2"></i>Daftar Program
          </a>
          @endif
          @endif
          @if(auth()->user()->role === 'konsultan')
          <a href="{{ route('program-anak.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('program-anak.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
            <i class="ri-add-line me-2"></i>Tambah Program Anak
          </a>
          @endif
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

  /* Read-only textarea styling for psikologi entries in the "Semua Program Anak" modal */
  #programAllModal .pa-rekomendasi textarea[disabled],
  #programAllModal .pa-keterangan textarea[disabled] {
    background-color: transparent;
    border: none;
    box-shadow: none;
    resize: none;
    padding: .375rem .5rem;
    min-height: 46px;
    color: #212529;
    cursor: default;
  }

  /* Slight row highlight for selected psikologi entry */
  #programAllModal .table-active {
    background-color: #f8fafb !important;
  }

  /* Ensure per-row action buttons render horizontally */
  /* Action cell: inline-flex centered vertically within the table cell */
  #programAllModal .pa-actions {
    display: inline-flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: .35rem !important;
    vertical-align: middle !important;
    height: 100%;
  }

  #programAllModal .pa-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    width: auto !important;
    padding: .35rem .5rem !important;
    margin: 0 !important;
  }

  /* Fix table row rendering: align cells vertically middle and reserve action column width */
  #programAllModal .table {
    table-layout: fixed;
    border-collapse: collapse;
  }

  /* move border to the row to ensure a continuous line across columns */
  #programAllModal .table th,
  #programAllModal .table td {
    vertical-align: middle !important;
    border-bottom: none !important;
    background: transparent !important;
  }

  #programAllModal .table tbody tr {
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    background: transparent !important;
  }

  #programAllModal .table tbody tr:last-child {
    border-bottom: none;
  }

  #programAllModal .table td.pa-rekomendasi textarea,
  #programAllModal .table td.pa-keterangan textarea {
    width: 100%;
    margin: 0;
    padding: .375rem .5rem;
    box-sizing: border-box;
    overflow: visible;
    background: transparent;
    border: none;
  }

  #programAllModal .table td.pa-actions {
    width: 120px;
    white-space: nowrap;
    padding-right: 0.5rem;
    padding-left: 0.5rem;
    background: transparent !important;
    overflow: visible;
  }

  /* remove button box-shadow/outline that may visually break the table line */
  #programAllModal .pa-actions .btn,
  #programAllModal .pa-actions .btn i {
    box-shadow: none !important;
    outline: none !important;
    margin: 0 !important;
    line-height: 1 !important;
    vertical-align: middle !important;
    background-clip: padding-box !important;
  }

  /* ensure buttons render above row background but do not create white blocks */
  #programAllModal .pa-actions .btn {
    z-index: 2;
    background-color: inherit;
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
          @if(auth()->check() && !in_array(optional(auth()->user())->role, ['admin','terapis','guru']))
          <div id="groupSuggestContainer" style="display:none">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="groupSuggestToggle">
              <label class="form-check-label" for="groupSuggestToggle">Sarankan Terapi</label>
            </div>
          </div>
          @endif
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
        // determine view/edit permissions for current user
        let canEditAny = false;
        let canViewAny = false;
        try {
          data.programs.forEach(p => {
            let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
            if (window.currentUser) {
              // konsultan owners may edit their own master items
              if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEditAny = true;
              // admin, guru, terapis may only view
              if (['admin', 'guru', 'terapis'].includes(String(window.currentUser.role))) canViewAny = true;
            }
          });
        } catch (e) {}
        let html = '<div class="table-responsive"><table class="table table-sm table-hover table-striped table-bordered">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th>' + ((canEditAny || canViewAny) ? '<th>AKSI</th>' : '') + '</tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          // determine if current user may edit/delete or only view this program
          let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
          let canEdit = false;
          let canView = false;
          if (window.currentUser) {
            if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEdit = true;
            if (['admin', 'guru', 'terapis'].includes(String(window.currentUser.role))) canView = true;
          }
          // build actions: view for viewers, view+edit+delete for owner konsultan
          let actionsParts = [];
          if (canView || canEdit) {
            actionsParts.push(`<button type="button" class="btn btn-sm btn-icon btn-outline-info" title="Lihat" onclick="window.showDetailProgram(${p.id})"><i class="ri-eye-line"></i></button>`);
          }
          if (canEdit) {
            actionsParts.push(`<button type="button" class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button>`);
            actionsParts.push(`<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button>`);
          }
          const actionsHtml = actionsParts.length ? `<div class="d-flex gap-1 pa-actions">${actionsParts.join('')}</div>` : '';
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>`;
          if (canEditAny || canViewAny) html += `<td>${actionsHtml}</td>`;
          html += `</tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        // set latest keterangan (outside the table)
        try {
          const latestEl = document.getElementById('groupLatestKeteranganText');
          if (latestEl) {
            const latest = (data.programs[0] && (data.programs[0].keterangan_master || data.programs[0].keterangan || data.programs[0].keterangan === '')) ? (data.programs[0].keterangan_master || data.programs[0].keterangan || '-') : '-';
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
        // determine view/edit permissions for current user
        let canEditAny2 = false;
        let canViewAny2 = false;
        try {
          data.programs.forEach(p => {
            let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
            if (window.currentUser) {
              if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEditAny2 = true;
              if (['admin', 'guru', 'terapis'].includes(String(window.currentUser.role))) canViewAny2 = true;
            }
          });
        } catch (e) {}
        let html = '<div class="table-responsive"><table class="table table-sm table-hover table-striped table-bordered">';
        html += '<thead><tr><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th>' + ((canEditAny2 || canViewAny2) ? '<th>AKSI</th>' : '') + '</tr></thead><tbody>';
        data.programs.forEach(p => {
          const konsultanName = p.konsultan ? p.konsultan.nama : (group.name || '-');
          let konsultanIdOfRow = (p.konsultan && p.konsultan.id) ? p.konsultan.id : (p.konsultan_id || null);
          let canEdit = false;
          let canView = false;
          if (window.currentUser) {
            if (window.currentUser.role === 'konsultan' && window.currentUser.konsultanId && parseInt(window.currentUser.konsultanId) === parseInt(konsultanIdOfRow)) canEdit = true;
            if (['admin', 'guru', 'terapis'].includes(String(window.currentUser.role))) canView = true;
          }
          let actionsParts = [];
          if (canView || canEdit) {
            actionsParts.push(`<button type="button" class="btn btn-sm btn-icon btn-outline-info" title="Lihat" onclick="window.showDetailProgram(${p.id})"><i class="ri-eye-line"></i></button>`);
          }
          if (canEdit) {
            actionsParts.push(`<button type="button" class="btn btn-sm btn-outline-warning" onclick="openEditProgramModal(${p.id})" title="Edit"><i class="ri-edit-line"></i></button>`);
            actionsParts.push(`<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProgramAndRefresh(${p.id})" title="Hapus"><i class="ri-delete-bin-line"></i></button>`);
          }
          const actionsHtml = actionsParts.length ? `<div class="d-flex gap-1 pa-actions">${actionsParts.join('')}</div>` : '';
          html += `<tr>
            <td>${p.kode_program || '-'}</td>
            <td>${p.nama_program || '-'}</td>
            <td>${p.tujuan || '-'}</td>
            <td>${p.aktivitas || '-'}</td>
            <td>${konsultanName}</td>`;
          if (canEditAny2 || canViewAny2) html += `<td>${actionsHtml}</td>`;
          html += `</tr>`;
        });
        html += '</tbody></table></div>';
        listDiv.innerHTML = html;
        try {
          const latestEl = document.getElementById('groupLatestKeteranganText');
          if (latestEl) {
            const latest = (data.programs[0] && (data.programs[0].keterangan_master || data.programs[0].keterangan || data.programs[0].keterangan === '')) ? (data.programs[0].keterangan_master || data.programs[0].keterangan || '-') : '-';
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
      role: @json(optional(Auth::user())->role),
      konsultanId: @json($currentKonsultanId ?? null)
    };
    window.currentKonsultanSpesRaw = @json($currentKonsultanSpesRaw ?? null);
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
      <!-- Tombol Cari & Reset: satu baris di mobile, terpisah di desktop -->
      <div class="d-flex flex-row gap-2 w-100 w-sm-auto mt-sm-0" style="max-width:100%;">
        <button type="submit" class="btn btn-outline-primary flex-fill flex-sm-unset" title="Cari">
          <i class="ri-search-line"></i>
        </button>
        <a href="{{ route('program-anak.index') }}" class="btn btn-outline-secondary flex-fill flex-sm-unset" title="Reset">
          <i class="ri-refresh-line"></i>
        </a>
      </div>
      <style>
        @media (max-width: 576px) {
          .w-sm-auto {
            width: 100% !important;
          }

          .flex-sm-unset {
            flex: 1 1 0 !important;
          }
        }

        @media (min-width: 577px) {
          .w-sm-auto {
            width: auto !important;
          }

          .flex-sm-unset {
            flex: unset !important;
          }
        }
      </style>
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
              <td>
                <p class="text-heading mb-0 fw-medium">{{ $program->anakDidik->nama ?? '-' }}</p>
              </td>
              <td>{{ $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-' }}</td>
              <td>
                @php
                $pk = $program->programKonsultan ?? null;
                $konsultanSpesRaw = optional($pk)->konsultan->spesialisasi ?? null;
                $konsultanSpes = strtolower($konsultanSpesRaw ?? '');
                $badge = null;
                if ($program->is_suggested) {
                if (str_contains($konsultanSpes, 'wicara')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_contains($konsultanSpes, 'sensori') || str_contains($konsultanSpes, 'integrasi')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_contains($konsultanSpes, 'psikologi')) {
                $badge = ['label' => 'TP', 'class' => 'bg-warning text-dark'];
                } else {
                // try to derive from kode_program if available (e.g. SI-001, WIC-001, PS-001)
                $kode = strtoupper($program->kode_program ?? '');
                if (str_starts_with($kode, 'SI')) {
                $badge = ['label' => 'SI', 'class' => 'bg-success'];
                } elseif (str_starts_with($kode, 'WIC') || str_starts_with($kode, 'WICARA')) {
                $badge = ['label' => 'TW', 'class' => 'bg-primary'];
                } elseif (str_starts_with($kode, 'PS')) {
                $badge = ['label' => 'TP', 'class' => 'bg-warning text-dark'];
                } else {
                // If program appears to be a psikologi recommendation (no program_konsultan and has rekomendasi), show TP
                if (($program->program_konsultan_id === null || $program->program_konsultan_id === 0) && ($program->rekomendasi || (isset($program->nama_program) && stripos($program->nama_program, 'rekomendasi') !== false))) {
                $badge = ['label' => 'TP', 'class' => 'bg-warning text-dark'];
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
                {{-- Tombol edit dan hapus dihilangkan, hanya tombol riwayat yang tampil --}}
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
        <div class="me-auto">
          <!-- placeholder for potential extra controls -->
        </div>
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
            // Additional detection: if no spesialisasi available but one of the suggested items
            // is a psikologi recommendation (e.g. program_konsultan_id == null or rekomendasi present),
            // render TP badge as well.
            try {
              if (!badgeLabel) {
                // restrict detection to items on the same dateKey
                const itemsForDate = (group.items || []).filter(it => {
                  let ik = '';
                  if (it.created_at) ik = (it.created_at.indexOf('T') !== -1) ? it.created_at.split('T')[0] : it.created_at.split(' ')[0];
                  else ik = 'id_' + it.id;
                  return ik === dateKey;
                });
                const psykDetected = itemsForDate.some(it => {
                  const suggested = (it.is_suggested === 1 || it.is_suggested === '1' || it.is_suggested === true);
                  if (!suggested) return false;
                  // psikologi recommendations are saved with program_konsultan_id == null and often have rekomendasi or nama_program contains 'Rekomendasi'
                  if ((it.program_konsultan_id === null || it.program_konsultan_id === undefined) && (it.rekomendasi || (it.nama_program && it.nama_program.toString().toLowerCase().includes('rekomendasi')))) return true;
                  // or konsultan spesialisasi explicitly says psikologi
                  if (it.konsultan_spesialisasi && it.konsultan_spesialisasi.toString().toLowerCase().includes('psiko')) return true;
                  // or the program was created_by current konsultan and nama_program indicates rekomendasi
                  if (it.created_by && window.currentUser && parseInt(it.created_by) === parseInt(window.currentUser.id) && it.nama_program && it.nama_program.toString().toLowerCase().includes('rekomendasi')) return true;
                  return false;
                });
                if (psykDetected) {
                  badgeLabel = 'TP';
                  badgeClass = 'bg-warning text-dark';
                }
              }
            } catch (e) {}
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
            label = 'TP';
            cls = 'bg-warning text-dark';
          }
          // If group/spec is empty, detect psikologi from the suggested items themselves
          // (program_konsultan_id null + rekomendasi present, or konsultan_spesialisasi contains 'psiko')
          else if (Array.isArray(items) && items.some(it => {
              const suggested = (it.is_suggested === 1 || it.is_suggested === '1' || it.is_suggested === true);
              if (!suggested) return false;
              if ((it.program_konsultan_id === null || it.program_konsultan_id === undefined) && (it.rekomendasi || (it.nama_program && it.nama_program.toString().toLowerCase().includes('rekomendasi')))) return true;
              if (it.konsultan_spesialisasi && it.konsultan_spesialisasi.toString().toLowerCase().includes('psiko')) return true;
              return false;
            })) {
            label = 'TP';
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
              label = 'TP';
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
  ['riwayatObservasiModal', 'programGroupModal', 'programAllModal', 'programEditModal', 'modalViewProgram', 'modalAddProgramMaster', 'programConfirmModal'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('hidden.bs.modal', cleanupModalBackdrops);
  });
</script>
<!-- Modal: All Programs for Anak -->
<style>
  /* Scoped styles to ensure table header and borders show inside the modal */
  #programAllModal .table thead th {
    border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
  }

  #programAllModal .table thead tr.table-light th {
    background-color: #f8f9fa !important;
  }

  #programAllModal .table.table-bordered td,
  #programAllModal .table.table-bordered th {
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
  }

  #programAllModal .table tbody td {
    border-top: 1px solid rgba(0, 0, 0, 0.04) !important;
  }
</style>
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
        <div class="me-auto">
          <!-- Edit/Delete apply to selected psikologi entry only -->
        </div>
        <!-- per-row actions now available in the table; footer buttons removed -->
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation modal used by in-modal delete actions -->
<div class="modal fade" id="programConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <p class="mb-0 confirm-msg">Yakin?</p>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-outline-secondary btn-sm btn-cancel" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger btn-sm btn-confirm">Hapus</button>
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
        <button type="button" class="btn btn-outline-secondary restore-previous-on-close" data-bs-dismiss="modal"><i class="ri-close-line me-2"></i>Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveEditProgram"><i class="ri-save-line me-2"></i>Simpan</button>
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
          // small cleanup after modal operations to ensure no stray backdrops remain
          setTimeout(function() {
            try {
              cleanupModalBackdrops();
            } catch (e) {}
          }, 50);
        }
      } else {
        showToast(res.message || 'Gagal hapus', 'danger');
      }
    }).catch(() => showToast('Gagal hapus', 'danger'));
  }
</script>
<script>
  // showConfirm(message) -> Promise<boolean>
  function showConfirm(message) {
    return new Promise(function(resolve) {
      try {
        const modalEl = document.getElementById('programConfirmModal');
        const msgEl = modalEl.querySelector('.confirm-msg');
        msgEl.textContent = message || 'Yakin?';
        const bs = bootstrap.Modal.getOrCreateInstance(modalEl);
        const onConfirm = function() {
          cleanup();
          resolve(true);
        };
        const onCancel = function() {
          cleanup();
          resolve(false);
        };
        const confirmBtn = modalEl.querySelector('.btn-confirm');
        const cancelBtn = modalEl.querySelector('.btn-cancel');

        function cleanup() {
          try {
            confirmBtn.removeEventListener('click', onConfirm);
          } catch (e) {}
          try {
            cancelBtn.removeEventListener('click', onCancel);
          } catch (e) {}
          try {
            bs.hide();
          } catch (e) {}
        }
        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
        bs.show();
      } catch (e) {
        resolve(false);
      }
    });
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
        // If any program looks like a psikologi recommendation (no konsultan but has rekomendasi),
        // render a psikologi-style editable form table. Otherwise render default program table.
        const anyPsikologi = data.programs.some(p => (!p.konsultan) && (p.rekomendasi || p.created_by_name));
        // determine if any psikologi row would show action buttons for current user
        let canEditAny = false;
        try {
          if (anyPsikologi && window.currentUser) {
            data.programs.forEach(p => {
              if ((!p.konsultan) && (p.rekomendasi || p.created_by_name)) {
                // grant edit-any only to konsultan owners or the original creator (not admin)
                if (window.currentUser.role === 'konsultan') {
                  if (window.currentUser.konsultanId && p.konsultan && p.konsultan.id && parseInt(window.currentUser.konsultanId) === parseInt(p.konsultan.id)) canEditAny = true;
                  if (!canEditAny && p.created_by && parseInt(window.currentUser.id) === parseInt(p.created_by)) canEditAny = true;
                } else {
                  if (p.created_by && parseInt(window.currentUser.id) === parseInt(p.created_by)) canEditAny = true;
                }
              }
            });
          }
        } catch (e) {}

        let html = '<div class="table-responsive"><table class="table table-sm table-hover table-striped table-bordered">';
        if (anyPsikologi) {
          html += '<thead><tr class="table-light"><th>REKOMENDASI</th><th>KETERANGAN</th><th>DIBUAT OLEH</th>' + (canEditAny ? '<th style="width:120px">AKSI</th>' : '') + '</tr></thead><tbody>';
          data.programs.forEach(p => {
            if ((!p.konsultan) && (p.rekomendasi || p.created_by_name)) {
              // determine per-row action visibility: admin, creator, or owning konsultan
              let canEditRow = false;
              try {
                if (window.currentUser) {
                  // do NOT grant edit rights to admin; only konsultan owners or creators may edit
                  if (window.currentUser.role === 'konsultan') {
                    if (window.currentUser.konsultanId && p.konsultan && p.konsultan.id && parseInt(window.currentUser.konsultanId) === parseInt(p.konsultan.id)) canEditRow = true;
                    if (!canEditRow && p.created_by && parseInt(window.currentUser.id) === parseInt(p.created_by)) canEditRow = true;
                  } else {
                    if (p.created_by && parseInt(window.currentUser.id) === parseInt(p.created_by)) canEditRow = true;
                  }
                }
              } catch (e) {}

              html += `<tr data-program-id="${p.id}">
                <td class="pa-rekomendasi"><textarea class="form-control form-control-sm" rows="2" data-field="rekomendasi" disabled>${p.rekomendasi ? p.rekomendasi : ''}</textarea></td>
                <td class="pa-keterangan"><textarea class="form-control form-control-sm" rows="2" data-field="keterangan" disabled>${p.keterangan ? p.keterangan : ''}</textarea></td>
                <td class="pa-created-by">${p.created_by_name ? p.created_by_name : '-'}</td>`;

              if (canEditAny) {
                if (canEditRow) {
                  html += `
                <td class="pa-actions">
                  <button type="button" class="btn btn-sm btn-outline-warning btn-row-edit" title="Edit"><i class="ri-edit-line"></i></button>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-row-delete" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                </td>`;
                } else {
                  html += `<td class="pa-actions"></td>`;
                }
              }

              html += `</tr>`;
            }
          });
        } else {
          html += '<thead><tr class="table-light"><th>KODE</th><th>NAMA PROGRAM</th><th>TUJUAN</th><th>AKTIVITAS</th><th>KONSULTAN</th></tr></thead><tbody>';
          data.programs.forEach(p => {
            html += `<tr>
              <td>${p.kode_program || '-'}</td>
              <td>${p.nama_program || '-'}</td>
              <td>${p.tujuan || '-'}</td>
              <td>${p.aktivitas || '-'}</td>
              <td>${p.konsultan ? p.konsultan.nama : '-'}</td>
            </tr>`;
          });
        }
        html += '</tbody></table></div>';
        target.innerHTML = html;

        // If psikologi table, wire per-row action buttons (Edit/Delete)
        if (anyPsikologi) {
          const rows = target.querySelectorAll('tr[data-program-id]');
          rows.forEach(row => {
            const id = row.dataset.programId;
            const editBtnRow = row.querySelector('.btn-row-edit');
            const delBtnRow = row.querySelector('.btn-row-delete');
            let inEdit = false;

            if (editBtnRow) {
              editBtnRow.addEventListener('click', function() {
                if (!inEdit) {
                  // enable editing
                  row.querySelectorAll('textarea[data-field]').forEach(el => el.disabled = false);
                  editBtnRow.innerHTML = '<i class="ri-save-line"></i>';
                  editBtnRow.title = 'Simpan';
                  if (delBtnRow) delBtnRow.disabled = true;
                  inEdit = true;
                  return;
                }
                // save
                const rekom = row.querySelector('textarea[data-field="rekomendasi"]').value;
                const keterangan = row.querySelector('textarea[data-field="keterangan"]').value;
                fetch('/program-anak/' + id + '/update-json', {
                  method: 'PUT',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                    rekomendasi: rekom,
                    keterangan: keterangan
                  })
                }).then(r => r.json()).then(resp => {
                  if (resp && resp.success) {
                    showToast('Perubahan tersimpan', 'success');
                    row.querySelectorAll('textarea[data-field]').forEach(el => el.disabled = true);
                    editBtnRow.innerHTML = '<i class="ri-edit-line"></i>';
                    editBtnRow.title = 'Edit';
                    if (delBtnRow) delBtnRow.disabled = false;
                    inEdit = false;
                    showAllProgramsForAnak(anakId);
                    // small safety cleanup in case a backdrop or body.modal-open remains
                    setTimeout(function() {
                      try {
                        cleanupModalBackdrops();
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        document.body.classList.remove('modal-open');
                      } catch (e) {}
                    }, 80);
                  } else {
                    showToast((resp && resp.message) || 'Gagal menyimpan', 'danger');
                  }
                }).catch(() => showToast('Gagal menyimpan', 'danger'));
              });
            }

            if (delBtnRow) {
              delBtnRow.addEventListener('click', function() {
                showConfirm('Yakin ingin menghapus entri ini?').then(function(confirmed) {
                  if (!confirmed) return;
                  fetch('/program-anak/' + id + '/delete-json', {
                    method: 'DELETE',
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                      'Accept': 'application/json'
                    }
                  }).then(r => r.json()).then(resp => {
                    if (resp && resp.success) {
                      showToast('Terhapus', 'success');
                      showAllProgramsForAnak(anakId);
                      // ensure any stray backdrops/modal-open state are cleaned up after refresh
                      setTimeout(function() {
                        try {
                          cleanupModalBackdrops();
                        } catch (e) {}
                      }, 50);
                      // if there are no psikologi recommendation rows left for this anak, close this modal and reopen riwayat
                      try {
                        fetch('/program-anak/' + anakId + '/all-json').then(r => r.json()).then(j => {
                          if (j && j.success && Array.isArray(j.programs)) {
                            const anyPsik = j.programs.some(p => (!p.konsultan) && (p.rekomendasi || p.created_by_name));
                            if (!anyPsik) {
                              try {
                                bootstrap.Modal.getInstance(modalEl).hide();
                              } catch (e) {}
                              const dummy = document.createElement('button');
                              dummy.setAttribute('data-anak-didik-id', anakId);
                              loadRiwayatObservasi(dummy);
                            }
                          }
                        }).catch(() => {});
                      } catch (e) {}
                      if (window.currentRiwayatAnakId) {
                        const dummy = document.createElement('button');
                        dummy.setAttribute('data-anak-didik-id', window.currentRiwayatAnakId);
                        loadRiwayatObservasi(dummy);
                      }
                    } else {
                      showToast((resp && resp.message) || 'Gagal hapus', 'danger');
                    }
                  }).catch(() => showToast('Gagal hapus', 'danger'));
                });
              });
            }
          });
        }
        modal.show();
      })
      .catch(() => {
        target.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        modal.show();
      });
  }
</script>
<!-- Modal: Detail Program (reuse daftar-program layout/styles) -->
<style>
  /* Scoped modal styles (copied from daftar-program) */
  #modalViewProgram .pv-badge-gradient {
    background: linear-gradient(90deg, #6f42c1, #7b61ff);
    color: #fff;
    font-weight: 600;
    border-radius: 0.5rem;
    padding: 0.35rem 0.6rem;
    display: inline-block;
  }

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

  @media (max-width:576px) {
    #modalViewProgram .pv-meta-badge {
      max-width: 60%;
      font-size: .9rem
    }

    #modalViewProgram .pv-left {
      padding: .75rem
    }

    #modalViewProgram .pv-badge-gradient {
      padding: .25rem .45rem
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

<script>
  window.showDetailProgram = function(id) {
    const modalEl = document.getElementById('modalViewProgram');
    const modal = new bootstrap.Modal(modalEl);
    hideRiwayatBeforeShow(modalEl);

    function decodeHtmlEntities(str) {
      if (typeof str !== 'string') return str;
      const txt = document.createElement('textarea');
      txt.innerHTML = str;
      return txt.value;
    }

    fetch('/program-anak/' + id + '/json')
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert('Gagal mengambil detail program');
          return;
        }
        const p = data.program || {};
        document.getElementById('viewKode').textContent = decodeHtmlEntities(p.kode_program || '-') || '-';
        document.getElementById('viewNama').textContent = decodeHtmlEntities(p.nama_program || '-') || '-';
        const keterangan = p.keterangan_master || p.keterangan || '-';
        document.getElementById('viewKeterangan').textContent = decodeHtmlEntities(keterangan) || '-';
        document.getElementById('viewTujuan').textContent = decodeHtmlEntities(p.tujuan || '-') || '-';
        document.getElementById('viewAktivitas').textContent = decodeHtmlEntities(p.aktivitas || '-') || '-';

        const katEl = document.getElementById('viewKategori');
        const konsEl = document.getElementById('viewKonsultan');
        const kat = decodeHtmlEntities(p.kategori || p.kategori_program || '') || '';
        const kons = decodeHtmlEntities((p.konsultan && (p.konsultan.nama || p.konsultan.spesialisasi)) || p.konsultan_nama || '') || '';
        if (!kat || kat.trim() === '-') {
          katEl.style.display = 'none';
        } else {
          katEl.style.display = '';
          katEl.textContent = kat;
        }
        if (!kons || kons.trim() === '-') {
          konsEl.style.display = 'none';
        } else {
          konsEl.style.display = '';
          konsEl.textContent = kons;
        }

        modal.show();
      })
      .catch(() => alert('Gagal mengambil detail program'));
  }
</script>
<div class="modal fade modalScrollable" id="modalAddProgramMaster" tabindex="-1" aria-labelledby="modalAddProgramMasterLabel"
  aria-hidden="true">
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

@endsection