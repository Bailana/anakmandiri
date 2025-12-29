@extends('layouts.contentNavbarLayout')

@section('title', 'Pasien Terapis')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
@php
$canManagePatients = false;
$isKepalaTerapis = false;
if (isset($user)) {
// Admin can always manage
if ($user->role === 'admin') {
$canManagePatients = true;
} elseif ($user->role === 'terapis') {
// Check Karyawan table for posisi 'Kepala Klinik' by email or name
$isKepalaByEmail = \App\Models\Karyawan::where('email', $user->email)->where('posisi', 'Kepala Klinik')->exists();
$isKepalaByName = \App\Models\Karyawan::where('nama', $user->name)->where('posisi', 'Kepala Klinik')->exists();
if ($isKepalaByEmail || $isKepalaByName) {
$canManagePatients = true;
$isKepalaTerapis = true;
}
}
}
@endphp
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Pasien Terapis</h4>
            <p class="text-body-secondary mb-0">Daftar pasien yang mengikuti terapis</p>
          </div>
          <div class="d-flex gap-2">
            @if($canManagePatients)
            <a href="{{ route('terapis.pasien.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
              <i class="ri-add-line" style="font-size:1.7em;"></i>
            </a>
            <a href="{{ route('terapis.pasien.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
              <i class="ri-add-line me-2"></i>Tambah Pasien Terapis
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Alert Messages -->
@if ($message = Session::get('success'))
<div id="alert-success-placeholder">
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-checkbox-circle-line me-2"></i>{{ $message }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
</div>
@else
<div id="alert-success-placeholder"></div>
@endif

<!-- Smaller badges to match Program Anak view -->
<style>
  /* match program-anak: smaller badges in table and modals on this page */
  #patientsTable .badge,
  #therapyScheduleModal .badge {
    font-size: .65rem !important;
    padding: .18rem .35rem !important;
    border-radius: .35rem !important;
  }

  /* Konsisten lebar kolom pada modal jadwal terapi */
  #therapyScheduleModal table th,
  #therapyScheduleModal table td {
    vertical-align: middle;
    text-align: left;
  }

  #therapyScheduleModal table th:nth-child(1),
  #therapyScheduleModal table td:nth-child(1) {
    width: 120px;
  }

  #therapyScheduleModal table th:nth-child(2),
  #therapyScheduleModal table td:nth-child(2) {
    width: 70px;
  }

  #therapyScheduleModal table th:nth-child(3),
  #therapyScheduleModal table td:nth-child(3) {
    width: 220px;
  }

  #therapyScheduleModal table th:nth-child(4),
  #therapyScheduleModal table td:nth-child(4) {
    width: 90px;
    text-align: center;
  }

  /* Fade out animation for deleted row */
  #therapyScheduleModal tr.fade-out {
    transition: opacity 0.5s ease;
    opacity: 0;
  }
</style>

<div class="row">
  <div class="col-12">
    <!-- Search & Filter (like /program) -->
    <div class="row mb-4">
      <div class="col-12">
        <form method="GET" action="{{ route('terapis.pasien.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
          <div class="flex-grow-1" style="min-width:200px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ request('search') }}">
          </div>
          <select name="status" class="form-select" style="max-width:150px;">
            <option value="">Semua Status</option>
            <option value="aktif" {{ (isset($selectedStatus) && $selectedStatus === 'aktif') ? 'selected' : '' }}>Aktif</option>
            <option value="non-aktif" {{ (isset($selectedStatus) && $selectedStatus === 'non-aktif') ? 'selected' : '' }}>Non Aktif</option>
          </select>
          @if(isset($user) && $user->role === 'admin')
          <select name="user_id" class="form-select" style="max-width:200px;">
            <option value="">-- Semua Terapis --</option>
            @foreach($therapists as $t)
            <option value="{{ $t->id }}" {{ request('user_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
          </select>
          @endif
          <button type="submit" class="btn btn-outline-primary" title="Filter">
            <i class="ri-search-line"></i>
          </button>
          <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary" title="Reset">
            <i class="ri-refresh-line"></i>
          </a>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="patientsTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Jenis Terapi</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assignments as $index => $assign)
            <tr>
              <td>{{ (method_exists($assignments, 'currentPage') ? ($assignments->currentPage() - 1) * $assignments->perPage() : 0) + $index + 1 }}</td>
              <td>
                @if($assign->anakDidik)
                <p class="text-heading mb-0 fw-medium"><a href="{{ route('anak-didik.show', $assign->anakDidik->id) }}" class="text-decoration-none text-reset">{{ $assign->anakDidik->nama }}</a></p>
                @else
                -
                @endif
              </td>
              <td>
                @php
                // Only show badge if ada jadwal terapi (schedules)
                $jenisRaw = $assign->jenis_terapi ?? '';
                $schedules = isset($assign->schedules) ? $assign->schedules : [];
                $badgeShown = false;
                if (empty(trim($jenisRaw))) {
                echo '-';
                } else {
                $parts = preg_split('/[|,]+/', $jenisRaw);
                foreach ($parts as $p) {
                $t = trim($p);
                // cek apakah ada jadwal untuk jenis terapi ini
                $adaJadwal = false;
                foreach ($schedules as $sch) {
                if (isset($sch['jenis_terapi']) && trim(mb_strtolower($sch['jenis_terapi'])) === mb_strtolower($t)) {
                $adaJadwal = true;
                break;
                }
                }
                if (!$adaJadwal) continue;
                $badgeShown = true;
                $abbr = null;
                $cls = null;
                $low = mb_strtolower($t);
                if (mb_strpos($low, 'wicara') !== false) {
                $abbr = 'TW';
                $cls = 'bg-primary';
                } elseif (mb_strpos($low, 'sensori') !== false || mb_strpos($low, 'integrasi') !== false) {
                $abbr = 'SI';
                $cls = 'bg-success';
                } elseif (mb_strpos($low, 'perilaku') !== false) {
                $abbr = 'TP';
                $cls = 'bg-warning text-dark';
                } else {
                $partsWords = preg_split('/\s+/', trim($t));
                $initials = '';
                foreach (array_slice($partsWords, 0, 2) as $pw) {
                $initials .= strtoupper(mb_substr($pw, 0, 1));
                }
                $abbr = $initials ?: strtoupper(mb_substr($t, 0, 2));
                $cls = 'bg-info';
                }
                echo '<span class="badge '.e($cls).' me-1">'.e($abbr).'</span>';
                }
                if (!$badgeShown) echo '-';
                }
                @endphp
              </td>
              <td>
                @if(isset($assign->status) && $assign->status === 'aktif')
                <span class="badge bg-success">Aktif</span>
                @elseif(isset($assign->status) && $assign->status === 'non-aktif')
                <span class="badge bg-danger">Non Aktif</span>
                @else
                <span class="badge bg-secondary">{{ $assign->status ?? '-' }}</span>
                @endif
              </td>
              <td>
                @if(isset($user) && in_array($user->role, ['admin','terapis']))
                <!-- Tombol aksi untuk desktop -->
                <div class="d-none d-md-flex gap-1 align-items-center">
                  <button type="button" class="btn btn-icon btn-sm btn-outline-info me-1" title="Lihat Jadwal" onclick="showTherapySchedules(this)" data-anak-id="{{ $assign->anak_didik_id }}" data-anak-nama="{{ $assign->anakDidik->nama ?? '-' }}">
                    <i class="ri-eye-line"></i>
                  </button>
                  <a href="{{ route('terapis.pasien.edit', $assign->id) }}" class="btn btn-icon btn-sm btn-outline-warning me-1" title="Edit Status">
                    <i class="ri-edit-line"></i>
                  </a>
                  <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete-assign" title="Hapus Pasien" data-assign-id="{{ $assign->id }}">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
                <!-- Tombol titik tiga untuk mobile -->
                <div class="dropdown d-md-none">
                  <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="showTherapySchedulesDropdown({{ $assign->anak_didik_id }}, '{{ $assign->anakDidik->nama ?? '-' }}');return false;"><i class="ri-eye-line me-1"></i> Lihat Jadwal</a></li>
                    <li><a class="dropdown-item" href="{{ route('terapis.pasien.edit', $assign->id) }}"><i class="ri-edit-line me-1"></i> Edit Status</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteAssignDropdown(this, {{ $assign->id }});return false;"><i class="ri-delete-bin-line me-1"></i> Hapus</a></li>
                  </ul>
                </div>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5">
                <div class="alert alert-warning mb-0" role="alert">
                  <i class="ri-alert-line me-2"></i>Tidak ada pasien.
                </div>
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
          function deleteAssignDropdown(el, assignId) {
            if (!confirm('Hapus data pasien terapis ini?')) return;
            // Buat dummy button agar event tetap bisa diproses oleh event listener utama
            var dummyBtn = document.createElement('button');
            dummyBtn.setAttribute('data-assign-id', assignId);
            dummyBtn.className = 'btn-delete-assign';
            dummyBtn.type = 'button';
            // Trigger event click pada dummyBtn
            dummyBtn.click = function() {
              // Cari event listener utama dan panggil
              var event = new Event('click', {
                bubbles: true
              });
              this.dispatchEvent(event);
            };
            // Jika ada event listener global, bisa dipanggil manual jika perlu
            // Atau bisa langsung panggil fungsi utama jika tersedia
            if (typeof window.deleteAssign === 'function') {
              window.deleteAssign(dummyBtn);
            }
          }
          // Agar tombol lihat jadwal di dropdown mobile tetap bisa pakai fungsi showTherapySchedules yang sama
          function showTherapySchedulesDropdown(anakId, anakNama) {
            var dummyBtn = document.createElement('button');
            dummyBtn.setAttribute('data-anak-id', anakId);
            dummyBtn.setAttribute('data-anak-nama', anakNama);
            if (typeof window.showTherapySchedules === 'function') {
              window.showTherapySchedules(dummyBtn);
            }
          }
        </script>
      </div>
      <!-- Modal: Jadwal Terapi -->
      <div class="modal fade" id="therapyScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <div class="d-flex align-items-center w-100 justify-content-between">
                <h5 class="modal-title mb-0">Jadwal Terapi <small id="therapyScheduleModalName" class="text-muted"></small></h5>
                <div>
                  <div class="btn-group btn-group-sm me-2" role="group" aria-label="View toggle">
                    <button type="button" class="btn btn-outline-secondary active" id="viewTableBtn">Tabel</button>
                    <button type="button" class="btn btn-outline-secondary" id="viewAgendaBtn">Agenda</button>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
            </div>
            <div class="modal-body">
              <div id="therapyScheduleBody">
                <div class="text-center text-muted">Memuat jadwal...</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
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
          Menampilkan {{ method_exists($assignments, 'firstItem') ? ($assignments->firstItem() ?? 0) : $assignments->count() }} hingga {{ method_exists($assignments, 'lastItem') ? ($assignments->lastItem() ?? 0) : $assignments->count() }} dari {{ method_exists($assignments, 'total') ? $assignments->total() : $assignments->count() }} data
        </div>
        <nav>
          @if(method_exists($assignments, 'links'))
          {{ $assignments->links('pagination::bootstrap-4') }}
          @endif
        </nav>
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script>
  // Edit status pasien terapis inline
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-status').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const assignId = this.getAttribute('data-assign-id');
        const tr = this.closest('tr');
        if (!assignId || !tr) return;
        const statusTd = tr.querySelector('td:nth-child(4)');
        const currentStatus = statusTd.textContent.trim().toLowerCase();
        statusTd.innerHTML = `<select class='form-select form-select-sm status-select'>` +
          `<option value='aktif' ${currentStatus==='aktif'?'selected':''}>Aktif</option>` +
          `<option value='non-aktif' ${currentStatus==='non-aktif'?'selected':''}>Non Aktif</option>` +
          `</select>` +
          `<button type='button' class='btn btn-sm btn-success btn-save-status ms-1'><i class='ri-save-line'></i></button>` +
          `<button type='button' class='btn btn-sm btn-secondary btn-cancel-status ms-1'><i class='ri-close-line'></i></button>`;
        // Save
        statusTd.querySelector('.btn-save-status').onclick = async function() {
          const newStatus = statusTd.querySelector('.status-select').value;
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          try {
            const res = await fetch(`/terapis/pasien/${assignId}`, {
              method: 'PATCH',
              headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                status: newStatus
              })
            });
            if (!res.ok) throw new Error('Gagal update status');
            statusTd.innerHTML = newStatus === 'aktif' ? `<span class='badge bg-success'>Aktif</span>` : `<span class='badge bg-danger'>Non Aktif</span>`;
            showToast('Status berhasil diubah', 'success');
          } catch (err) {
            showToast('Gagal mengubah status', 'danger');
          }
        };
        // Cancel
        statusTd.querySelector('.btn-cancel-status').onclick = function() {
          statusTd.innerHTML = currentStatus === 'aktif' ? `<span class='badge bg-success'>Aktif</span>` : `<span class='badge bg-danger'>Non Aktif</span>`;
        };
      });
    });
    // Hapus pasien terapis
    document.querySelectorAll('.btn-delete-assign').forEach(function(btn) {
      btn.addEventListener('click', async function() {
        const assignId = this.getAttribute('data-assign-id');
        const tr = this.closest('tr');
        if (!assignId || !tr) return;
        if (!confirm('Hapus data pasien terapis ini?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
          const res = await fetch(`/terapis/pasien/${assignId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            }
          });
          if (!res.ok) throw new Error('Gagal menghapus data');
          tr.remove();
          // Tampilkan alert success di posisi yang sama seperti alert success setelah tambah pasien
          let alertPlaceholder = document.getElementById('alert-success-placeholder');
          if (!alertPlaceholder) {
            alertPlaceholder = document.createElement('div');
            alertPlaceholder.id = 'alert-success-placeholder';
            const ref = document.querySelector('.page-content, .content-wrapper, main, body');
            if (ref) ref.prepend(alertPlaceholder);
            else document.body.prepend(alertPlaceholder);
          }
          alertPlaceholder.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="ri-checkbox-circle-line me-2"></i>Data pasien terhapus
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          `;
          setTimeout(() => {
            const alertEl = alertPlaceholder.querySelector('.alert');
            if (alertEl) alertEl.remove();
          }, 3000);
        } catch (err) {
          showToast('Gagal menghapus data', 'danger');
        }
      });
    });
  });
  // whether current user (server-side) can edit/delete schedules (terapis Kepala Klinik)
  const canEditSchedules = @json($isKepalaTerapis ?? false);

  // store current anak id shown in modal so we can refresh later
  window._currentTherapyAnakId = null;

  // helper to reload modal contents by calling showTherapySchedules with a dummy button
  window.reloadTherapyModal = function() {
    const id = window._currentTherapyAnakId;
    if (!id) return;
    // Hapus backdrop jika ada (fix bug modal abu-abu tidak bisa klik)
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    const dummy = document.createElement('button');
    dummy.setAttribute('data-anak-id', id);
    // try to keep nama if present from modal title
    const nameEl = document.getElementById('therapyScheduleModalName');
    const nama = nameEl ? nameEl.textContent.replace(/^-\s*/, '') : '';
    dummy.setAttribute('data-anak-nama', nama || '');
    // call the same function to re-fetch and re-render
    try {
      showTherapySchedules(dummy);
    } catch (e) {
      console.error(e);
    }
  };

  // ensure showToast exists (reuse pattern from program-anak)
  if (typeof showToast !== 'function') {
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
  }

  // Edit schedule: (no-op, handled inline in modal)
  function editSchedule(id) {
    // No redirect; handled by inline modal logic
  }

  // Delete schedule via fetch DELETE and remove row from modal table on success
  async function deleteSchedule(id) {
    if (!id) return;
    if (!confirm('Hapus jadwal ini?')) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
      const res = await fetch(`/terapis/jadwal/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        }
      });
      if (!res.ok) throw new Error(`Status ${res.status}`);
      // show success toast
      showToast('Jadwal berhasil dihapus', 'success');
      // Hapus baris tr pada modal dengan animasi fade out
      const row = document.querySelector(`#therapyScheduleModal tr[data-schedule-id="${id}"]`);
      if (row) {
        row.classList.add('fade-out');
        setTimeout(() => row.remove(), 500);
      }
    } catch (err) {
      showToast('Gagal menghapus jadwal', 'danger');
      console.error(err);
    }
  }

  async function showTherapySchedules(btn) {
    const anakId = btn.getAttribute('data-anak-id');
    const anakNama = btn.getAttribute('data-anak-nama') || '';
    // remember current anak id for later reloads
    window._currentTherapyAnakId = anakId;
    if (!anakId) return;
    const modalEl = document.getElementById('therapyScheduleModal');
    const body = document.getElementById('therapyScheduleBody');
    const nameEl = document.getElementById('therapyScheduleModalName');
    const originalBtnHtml = btn.innerHTML;
    // show spinner on the button
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    body.innerHTML = '<div id="therapyScheduleView"><div id="tableView"></div><div id="agendaView" style="display:none;"></div></div>';
    nameEl.textContent = anakNama ? `- ${anakNama}` : '';
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    function formatDate(d) {
      if (!d) return '-';
      try {
        const dt = new Date(d + 'T00:00:00');
        return dt.toLocaleDateString(undefined, {
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        });
      } catch (err) {
        return d;
      }
    }
    try {
      const res = await fetch(`/terapis/pasien/${anakId}/jadwal`);
      if (!res.ok) {
        body.innerHTML = `<div class="text-danger">Gagal memuat jadwal. Status: ${res.status}</div>`;
        return;
      }
      const json = await res.json();
      if (!json.success) {
        body.innerHTML = `<div class="text-danger">Gagal memuat jadwal: ${json.message || 'response false'}</div>`;
        return;
      }
      const data = json.data;
      if (!data || data.length === 0) {
        body.innerHTML = '<div class="text-muted">Belum ada jadwal untuk anak ini.</div>';
        return;
      }
      // Render table view (separate table per therapy) and agenda view (grouped by date)
      function renderTableView(data) {
        let out = '';
        data.forEach(a => {
          // split jenis_terapi if merged with '|' delimiter
          const jenisRaw = a.jenis_terapi || '-';
          const jenisParts = jenisRaw.split('|').map(p => p.trim()).filter(Boolean);
          // if there are multiple jenis, create a section for each
          jenisParts.forEach((jenis, idx) => {
            // filter schedules by jenis_terapi when available. If schedules don't have jenis, show them only for the first jenis
            const schedulesForJenis = (a.schedules || []).filter(s => {
              if (s.jenis_terapi && s.jenis_terapi.trim() !== '') return s.jenis_terapi.trim() === jenis.trim();
              return idx === 0; // fallback: show schedules without jenis only under the first jenis
            });
            if (!schedulesForJenis || schedulesForJenis.length === 0) {
              // skip rendering this jenis if no schedule
              return;
            }
            out += `<div class="mb-3"><h6 class="mb-1">${jenis}</h6>`;
            let thead = '<thead><tr><th>Tanggal</th><th>Jam</th><th>Terapis</th>' + (canEditSchedules ? '<th>Aksi</th>' : '') + '</tr></thead>';
            out += `<div class="table-responsive"><table class="table table-sm">${thead}<tbody>`;
            schedulesForJenis.forEach(s => {
              const tanggal = s.tanggal_mulai ? formatDate(s.tanggal_mulai) : '-';
              const jam = s.jam_mulai || '-';
              const terapisNama = s.terapis_nama || a.terapis_nama || '-';
              if (typeof s.id === 'undefined' || s.id === null) {
                out += `<tr><td colspan="${canEditSchedules ? 4 : 3}" class="text-danger">Jadwal tidak valid (id kosong)</td></tr>`;
              } else {
                out += `<tr data-schedule-id="${s.id}"><td>${tanggal}</td><td>${jam}</td><td class="text-muted">${terapisNama}</td>`;
                if (canEditSchedules) {
                  out += `<td>` +
                    `<button type=\"button\" class=\"btn btn-icon btn-sm btn-outline-warning btn-edit-schedule me-1\" data-schedule-id=\"${s.id}\" title=\"Edit\"><i class=\"ri-edit-line\"></i></button>` +
                    `<button type=\"button\" class=\"btn btn-icon btn-sm btn-outline-danger btn-delete-schedule\" data-schedule-id=\"${s.id}\" title=\"Hapus\"><i class=\"ri-delete-bin-line\"></i></button>` +
                    `</td>`;
                }
                out += `</tr>`;
              }
            });
            out += '</tbody></table></div>';
            out += '</div>';
          });
        });
        return out;
      }

      function renderAgendaView(data) {
        // collect all schedule entries across assignments, associate with therapy
        const events = [];
        data.forEach(a => {
          const jenisParts = (a.jenis_terapi || '').split('|').map(p => p.trim()).filter(Boolean);
          const jenisForAll = jenisParts.length ? jenisParts : ['-'];
          if (a.schedules && a.schedules.length) {
            a.schedules.forEach(s => {
              const tanggal = s.tanggal_mulai || null;
              const jam = s.jam_mulai || null;
              // prefer schedule-specific jenis_terapi if present, otherwise use first jenis from assignment
              const terapisForEvent = (s.terapis_nama && s.terapis_nama.trim() !== '') ? s.terapis_nama.trim() : (a.terapis_nama || '-');
              if (s.jenis_terapi && s.jenis_terapi.trim() !== '') {
                events.push({
                  tanggal,
                  jam,
                  jenis: s.jenis_terapi.trim(),
                  terapis: terapisForEvent
                });
              } else {
                const fallbackJenis = jenisForAll.length ? jenisForAll[0] : '-';
                events.push({
                  tanggal,
                  jam,
                  jenis: fallbackJenis,
                  terapis: terapisForEvent
                });
              }
            });
          }
        });
        if (events.length === 0) return '<div class="text-muted">Belum ada jadwal untuk anak ini.</div>';
        // group by tanggal
        const grouped = {};
        events.forEach(e => {
          const key = e.tanggal || 'Tanpa Tanggal';
          if (!grouped[key]) grouped[key] = [];
          grouped[key].push(e);
        });
        // sort dates descending
        const dates = Object.keys(grouped).sort((a, b) => {
          if (a === 'Tanpa Tanggal') return 1;
          if (b === 'Tanpa Tanggal') return -1;
          return new Date(b) - new Date(a);
        });
        let out = '';
        dates.forEach(d => {
          out += `<div class="mb-3"><h6 class="mb-1">${d === 'Tanpa Tanggal' ? 'Tanpa Tanggal' : formatDate(d)}</h6>`;
          out += '<ul class="list-unstyled mb-0">';
          grouped[d].forEach(ev => {
            out += `<li class="py-1 border-bottom"><strong>${ev.jam || '-'}</strong> — ${ev.jenis} <small class="text-muted">(${ev.terapis})</small></li>`;
          });
          out += '</ul></div>';
        });
        return out;
      }

      const tableHtml = renderTableView(data);
      const agendaHtml = renderAgendaView(data);
      const tableViewEl = document.getElementById('tableView');
      const agendaViewEl = document.getElementById('agendaView');
      if (tableViewEl) {
        tableViewEl.innerHTML = tableHtml;
        if (canEditSchedules) {
          // attach listeners to edit/delete buttons rendered inside the modal
          // edit -> navigate to edit page; delete -> call deleteSchedule
          tableViewEl.querySelectorAll('.btn-edit-schedule').forEach(b => {
            b.addEventListener('click', function(e) {
              const id = this.getAttribute('data-schedule-id');
              if (!id) return;
              const tr = tableViewEl.querySelector(`tr[data-schedule-id="${id}"]`);
              if (!tr) return;
              // Ambil data dari kolom
              const tds = tr.querySelectorAll('td');
              const tanggal = tds[0].textContent.trim();
              const jam = tds[1].textContent.trim();
              const terapis = tds[2].textContent.trim();
              // Ganti baris dengan form
              tr.innerHTML = `<td><input type="date" class="form-control form-control-sm" value="${tanggal.split('-').reverse().join('-')}"></td>` +
                `<td><input type="time" class="form-control form-control-sm" value="${jam}"></td>` +
                `<td><input type="text" class="form-control form-control-sm" value="${terapis}"></td>` +
                `<td>` +
                `<button type="button" class="btn btn-sm btn-success btn-save-schedule" data-schedule-id="${id}"><i class="ri-save-line"></i></button>` +
                `<button type="button" class="btn btn-sm btn-secondary btn-cancel-edit" data-schedule-id="${id}"><i class="ri-close-line"></i></button>` +
                `</td>`;
              // Save handler
              tr.querySelector('.btn-save-schedule').onclick = async function() {
                const newTanggal = tr.querySelector('input[type="date"]').value;
                const newJam = tr.querySelector('input[type="time"]').value;
                const newTerapis = tr.querySelector('input[type="text"]').value;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                  const res = await fetch(`/terapis/jadwal/${id}`, {
                    method: 'PUT',
                    headers: {
                      'X-CSRF-TOKEN': token,
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                      tanggal_mulai: newTanggal,
                      jam_mulai: newJam,
                      terapis_nama: newTerapis
                    })
                  });
                  if (!res.ok) throw new Error('Gagal update jadwal');
                  showToast('Jadwal berhasil diubah', 'success');
                  // Reload modal agar data segar
                  window.reloadTherapyModal();
                } catch (err) {
                  showToast('Gagal mengubah jadwal', 'danger');
                }
              };
              // Cancel handler
              tr.querySelector('.btn-cancel-edit').onclick = function() {
                window.reloadTherapyModal();
              };
            });
          });
          tableViewEl.querySelectorAll('.btn-delete-schedule').forEach(b => {
            b.addEventListener('click', function(e) {
              const id = this.getAttribute('data-schedule-id');
              if (id) deleteSchedule(id);
            });
          });
        }
      }
      if (agendaViewEl) agendaViewEl.innerHTML = agendaHtml;
      // hook toggle buttons
      const btnTable = document.getElementById('viewTableBtn');
      const btnAgenda = document.getElementById('viewAgendaBtn');
      if (btnTable && btnAgenda) {
        btnTable.classList.add('active');
        btnAgenda.classList.remove('active');
        btnTable.onclick = function() {
          tableViewEl.style.display = '';
          agendaViewEl.style.display = 'none';
          btnTable.classList.add('active');
          btnAgenda.classList.remove('active');
        };
        btnAgenda.onclick = function() {
          tableViewEl.style.display = 'none';
          agendaViewEl.style.display = '';
          btnAgenda.classList.add('active');
          btnTable.classList.remove('active');
        };
      }
    } catch (e) {
      body.innerHTML = '<div class="text-danger">Terjadi kesalahan saat memuat jadwal.</div>';
    } finally {
      // restore button
      btn.innerHTML = originalBtnHtml;
    }
  }
</script>
@endpush