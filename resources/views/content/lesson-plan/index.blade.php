@extends('layouts/contentNavbarLayout')

@section('title', 'Lesson Plan')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Lesson Plan</h4>
            <p class="text-body-secondary mb-0">Daftar Lesson Plan berdasarkan PPI anak didik.</p>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0"
              style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;"
              data-bs-toggle="modal" data-bs-target="#tambahLessonPlanModal">
              <i class="ri-add-line" style="font-size:1.7em;"></i>
            </button>
            <button type="button" class="btn btn-primary d-none d-sm-inline-flex align-items-center"
              data-bs-toggle="modal" data-bs-target="#tambahLessonPlanModal">
              <i class="ri-add-line me-2"></i>Tambah Lesson Plan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <form method="GET" action="{{ route('lesson-plan.index') }}">
      {{-- Desktop: input + tombol satu baris --}}
      <div class="d-none d-sm-flex gap-2 align-items-end flex-wrap">
        <div class="flex-grow-1" style="min-width:200px">
          <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..."
            value="{{ $search ?? '' }}">
        </div>
        <button type="submit" class="btn btn-outline-primary" title="Cari">
          <i class="ri-search-line"></i>
        </button>
        <a href="{{ route('lesson-plan.index') }}" class="btn btn-outline-secondary" title="Reset">
          <i class="ri-refresh-line"></i>
        </a>
      </div>
      {{-- Mobile: input penuh, lalu tombol satu baris penuh --}}
      <div class="d-sm-none">
        <input type="text" name="search" class="form-control mb-2" placeholder="Cari nama anak atau NIS..."
          value="{{ $search ?? '' }}">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary flex-grow-1">
            <i class="ri-search-line"></i>
          </button>
          <a href="{{ route('lesson-plan.index') }}" class="btn btn-outline-secondary flex-grow-1" title="Reset">
            <i class="ri-refresh-line"></i>
          </a>
        </div>
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
            @forelse($anakList as $i => $anak)
            @php $ppi = $ppis[$anak->id] ?? null; @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <p class="text-heading mb-0 fw-medium">{{ $anak->nama }}</p>
                @if($anak->nis)<small class="text-body-secondary">{{ $anak->nis }}</small>@endif
              </td>
              <td>{{ $anak->guruFokus ? $anak->guruFokus->nama : '-' }}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <a href="{{ route('lesson-plan.riwayat', $anak->id) }}"
                    class="btn btn-sm btn-icon btn-outline-info" title="Riwayat Lesson Plan">
                    <i class="ri-history-line"></i>
                  </a>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-5">
                <div class="mb-3"><i class="ri-search-line" style="font-size:3rem;color:#ccc"></i></div>
                <p class="text-body-secondary mb-0">Tidak ada data anak ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<!-- Modal Tambah Lesson Plan -->
<div class="modal fade" id="tambahLessonPlanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-file-list-2-line me-2"></i>Buat Lesson Plan</h5>
        <button type="button" class="btn-close" aria-label="Close" onclick="lpTryClose()"></button>
      </div>
      <form id="lessonPlanForm" method="POST" action="{{ route('lesson-plan.store') }}" style="display:flex;flex-direction:column;flex:1;overflow:hidden;min-height:0;">
        @csrf
        <div class="modal-body">

          <!-- Info atas -->
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nama Anak Didik <span class="text-danger">*</span></label>
              <select name="anak_didik_id" id="lpAnakSelect" class="form-select" required>
                <option value="">-- Pilih Anak Didik --</option>
                @foreach($anakList as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }}{{ $anak->nis ? ' ('.$anak->nis.')' : '' }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Periode PPI <span class="text-danger">*</span></label>
              <select name="ppi_id" id="lpPpiSelect" class="form-select" required disabled>
                <option value="">-- Pilih anak didik dulu --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
              <input type="month" name="tanggal" id="lpTanggal" class="form-control" required value="{{ date('Y-m') }}">
            </div>
          </div>

          <hr>

          <!-- Sections: Awal, Inti, Penutup -->
          @foreach(['awal' => 'Awal', 'inti' => 'Inti', 'penutup' => 'Penutup'] as $sectionKey => $sectionLabel)
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold mb-0">
                <span class="badge bg-{{ $sectionKey === 'awal' ? 'info' : ($sectionKey === 'inti' ? 'primary' : 'success') }} me-2">{{ $sectionLabel }}</span>
              </h6>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="lpAddRow('{{ $sectionKey }}')">
                <i class="ri-add-line me-1"></i>Tambah Jadwal
              </button>
            </div>
            <div id="lpRows_{{ $sectionKey }}" class="d-flex flex-column gap-2">
              <div class="text-body-secondary small fst-italic lp-empty-hint">Belum ada jadwal. Klik &ldquo;Tambah Jadwal&rdquo; untuk menambahkan.</div>
            </div>
          </div>
          @endforeach

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" onclick="lpTryClose()">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Buat Lesson Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Konfirmasi Batalkan Lesson Plan -->
<div class="modal fade" id="lpConfirmCloseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold"><i class="ri-error-warning-line me-2 text-warning"></i>Batalkan Pembuatan Lesson Plan?</h5>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0 text-body-secondary">Anda memiliki data yang belum disimpan. Apakah Anda yakin ingin membatalkan? Semua inputan yang sudah diisi akan dihapus.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" onclick="lpCancelConfirm()"><i class="ri-arrow-left-line me-1"></i>Kembali ke Form</button>
        <button type="button" class="btn btn-danger" onclick="lpConfirmClose()"><i class="ri-delete-bin-line me-1"></i>Batalkan &amp; Hapus Inputan</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Row template per section
  function lpAddRow(section) {
    const container = document.getElementById('lpRows_' + section);
    // remove empty hint if present
    const hint = container.querySelector('.lp-empty-hint');
    if (hint) hint.remove();

    const idx = container.querySelectorAll('.lp-row').length;
    // Collect all program IDs already selected across all rows in the modal
    const alreadySelected = new Set(
      Array.from(document.querySelectorAll('#tambahLessonPlanModal input[type=hidden][data-prog]')).map(i => String(i.dataset.prog))
    );
    // Build program options excluding already-selected ones
    let programOpts = '<option value="">-- Pilih Program --</option>';
    // Urutkan program secara alfabetis A-Z
    const sortedPrograms = (window._lpProgramList || []).slice().sort((a, b) => a.nama.localeCompare(b.nama));
    sortedPrograms.forEach(p => {
      if (!alreadySelected.has(String(p.id))) {
        programOpts += `<option value="${p.id}">${p.nama}</option>`;
      }
    });
    const div = document.createElement('div');
    div.className = 'lp-row border rounded p-2';
    div.innerHTML = `
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label small mb-1">Jam Mulai</label>
        <input type="time" name="schedules[${section}_${idx}][jam_mulai]" class="form-control form-control-sm" required>
        <input type="hidden" name="schedules[${section}_${idx}][section]" value="${section}">
      </div>
      <div class="col-6">
        <label class="form-label small mb-1">Jam Selesai</label>
        <input type="time" name="schedules[${section}_${idx}][jam_selesai]" class="form-control form-control-sm" required>
      </div>
      <div class="col-12">
        <label class="form-label small mb-1">Keterangan / Aktivitas</label>
        <textarea name="schedules[${section}_${idx}][keterangan]" class="form-control form-control-sm" rows="3" placeholder="Deskripsi kegiatan..."></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small mb-1">Program (opsional)</label>
        <div class="d-flex gap-2 align-items-start">
          <div class="lp-program-wrap flex-grow-1" style="min-width:0">
            <select class="form-select form-select-sm lp-prog-picker" onchange="lpPickProgram(this,'${section}',${idx})">
              ${programOpts}
            </select>
            <div class="lp-prog-tags d-flex flex-wrap gap-1 mt-1"></div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="lpRemoveRow(this, '${section}')">
            <i class="ri-delete-bin-line"></i>
          </button>
        </div>
      </div>
    </div>
  `;
    container.appendChild(div);
  }

  function lpPickProgram(select, section, idx) {
    const val = select.value;
    if (!val) return;
    const wrap = select.closest('.lp-program-wrap');
    // prevent duplicate
    for (const inp of wrap.querySelectorAll('input[type=hidden]')) {
      if (inp.dataset.prog === val) {
        select.value = '';
        return;
      }
    }
    // hidden input for form submission (using ppi_item_id)
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = `schedules[${section}_${idx}][ppi_item_ids][]`;
    hidden.value = val;
    hidden.dataset.prog = val;
    wrap.appendChild(hidden);
    // badge shows program name looked up by ID
    const prog = (window._lpProgramList || []).find(p => String(p.id) === String(val));
    const label = prog ? prog.nama : val;
    // badge with truncation for long names
    const tag = document.createElement('span');
    tag.className = 'badge bg-primary d-inline-flex align-items-center gap-1 lp-prog-tag';
    tag.style.cssText = 'max-width:100%; overflow:hidden; word-break:break-word; white-space:normal;';
    tag.dataset.val = val;
    tag.dataset.progName = label;
    tag.innerHTML = `<span title="${label}">${label}</span><i class="ri-close-line ms-1" style="cursor:pointer;font-size:.85em;flex-shrink:0" onclick="lpRemoveProg(this)"></i>`;
    wrap.querySelector('.lp-prog-tags').appendChild(tag);
    // remove chosen option from ALL pickers in the modal so it can't be picked twice globally
    document.querySelectorAll('.lp-prog-picker').forEach(s => {
      Array.from(s.options).forEach(o => {
        if (String(o.value) === String(val)) o.remove();
      });
    });
    select.value = '';
  }

  function lpRemoveProg(icon) {
    const tag = icon.closest('.lp-prog-tag');
    const val = tag.dataset.val;
    const progName = tag.dataset.progName || val;
    const wrap = tag.closest('.lp-program-wrap');
    // remove hidden input and badge first
    wrap.querySelectorAll('input[type=hidden]').forEach(inp => {
      if (inp.dataset.prog === val) inp.remove();
    });
    tag.remove();
    // re-add option to ALL pickers that don't already have this program selected in their row
    document.querySelectorAll('.lp-prog-picker').forEach(s => {
      const w = s.closest('.lp-program-wrap');
      const alreadyPicked = Array.from(w.querySelectorAll('input[type=hidden]')).some(i => i.dataset.prog === val);
      if (!alreadyPicked && !Array.from(s.options).some(o => String(o.value) === String(val))) {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = progName;
        s.appendChild(opt);
      }
    });
  }

  function lpRemoveRow(btn, section) {
    const row = btn.closest('.lp-row');
    // collect programs selected in this row before removal
    const rowPrograms = Array.from(row.querySelectorAll('input[type=hidden][data-prog]')).map(i => i.dataset.prog);
    const container = row.parentElement;
    row.remove();
    // release this row's programs back to all remaining pickers
    // collect name labels before removal
    const rowProgLabels = {};
    row.querySelectorAll('.lp-prog-tag').forEach(t => {
      rowProgLabels[t.dataset.val] = t.dataset.progName || t.dataset.val;
    });
    rowPrograms.forEach(val => {
      const progName = rowProgLabels[val] || val;
      document.querySelectorAll('.lp-prog-picker').forEach(s => {
        const w = s.closest('.lp-program-wrap');
        const alreadyPicked = Array.from(w.querySelectorAll('input[type=hidden]')).some(i => i.dataset.prog === val);
        if (!alreadyPicked && !Array.from(s.options).some(o => String(o.value) === String(val))) {
          const opt = document.createElement('option');
          opt.value = val;
          opt.textContent = progName;
          s.appendChild(opt);
        }
      });
    });
    // re-index remaining rows
    container.querySelectorAll('.lp-row').forEach((r, i) => {
      r.querySelectorAll('input, select').forEach(el => {
        const name = el.getAttribute('name');
        if (name) {
          el.setAttribute('name', name.replace(/\[([a-z]+)_\d+\]/, `[${section}_${i}]`));
        }
      });
    });
    // show empty hint if no rows
    if (container.querySelectorAll('.lp-row').length === 0) {
      const hint = document.createElement('div');
      hint.className = 'text-body-secondary small fst-italic lp-empty-hint';
      hint.textContent = 'Belum ada jadwal. Klik "Tambah Jadwal" untuk menambahkan.';
      container.appendChild(hint);
    }
  }

  // Load PPI options when anak didik is selected
  document.getElementById('lpAnakSelect').addEventListener('change', function() {
    const anakId = this.value;
    const ppiSelect = document.getElementById('lpPpiSelect');
    ppiSelect.innerHTML = '<option value="">Memuat...</option>';
    ppiSelect.disabled = true;

    if (!anakId) {
      ppiSelect.innerHTML = '<option value="">-- Pilih anak didik dulu --</option>';
      return;
    }

    fetch('/ppi/riwayat/' + anakId)
      .then(r => r.json())
      .then(res => {
        ppiSelect.innerHTML = '<option value="">-- Pilih periode --</option>';
        if (res.success && res.riwayat && res.riwayat.length) {
          const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

          function fmtMonYear(d) {
            if (!d) return '';
            const dt = new Date(d.replace(' ', 'T'));
            return months[dt.getMonth()] + ' ' + dt.getFullYear();
          }
          // Store all items for program dropdown, keyed by ppi id
          window._lpRiwayat = {};
          res.riwayat.forEach(p => {
            const label = (p.periode_mulai && p.periode_selesai) ?
              fmtMonYear(p.periode_mulai) + ' s/d ' + fmtMonYear(p.periode_selesai) :
              'PPI #' + p.id;
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = label;
            ppiSelect.appendChild(opt);
            window._lpRiwayat[p.id] = (p.items || []).map(it => ({
              id: it.id,
              nama: it.nama_program
            })).filter(it => it.id && it.nama);
          });
          ppiSelect.disabled = false;
          // reset program list when no PPI selected yet
          window._lpProgramList = [];
        } else {
          ppiSelect.innerHTML = '<option value="">Belum ada PPI</option>';
        }
      })
      .catch(() => {
        ppiSelect.innerHTML = '<option value="">Gagal memuat</option>';
      });
  });

  // Update program dropdown in all existing rows when PPI selection changes
  document.getElementById('lpPpiSelect').addEventListener('change', function() {
    const ppiId = this.value;
    window._lpProgramList = (window._lpRiwayat && ppiId && window._lpRiwayat[ppiId]) ? window._lpRiwayat[ppiId] : [];
    // Rebuild picker options in all existing schedule rows
    document.querySelectorAll('.lp-row .lp-prog-picker').forEach(sel => {
      // collect already-picked values in this row (hidden inputs)
      const wrap = sel.closest('.lp-program-wrap');
      const picked = Array.from(wrap.querySelectorAll('input[type=hidden]')).map(i => i.dataset.prog);
      sel.innerHTML = '<option value="">-- Pilih Program --</option>';
      window._lpProgramList.forEach(p => {
        if (!picked.includes(String(p.id))) {
          const o = document.createElement('option');
          o.value = p.id;
          o.textContent = p.nama;
          sel.appendChild(o);
        }
      });
    });
  });

  // Check if the form has any user-entered data
  function lpHasInput() {
    if (document.getElementById('lpAnakSelect').value) return true;
    if (document.querySelectorAll('#tambahLessonPlanModal .lp-row').length > 0) return true;
    return false;
  }

  // Attempt to close: show confirm dialog if there's input, else close directly
  function lpTryClose() {
    if (lpHasInput()) {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('lpConfirmCloseModal')).show();
    } else {
      lpForceClose();
    }
  }

  // Close the main modal without confirmation
  function lpForceClose() {
    bootstrap.Modal.getInstance(document.getElementById('tambahLessonPlanModal')).hide();
  }

  // Dismiss the confirm dialog and go back to the form
  function lpCancelConfirm() {
    bootstrap.Modal.getInstance(document.getElementById('lpConfirmCloseModal')).hide();
  }

  // User confirmed cancellation: close confirm dialog then main modal
  function lpConfirmClose() {
    var confirmEl = document.getElementById('lpConfirmCloseModal');
    confirmEl.addEventListener('hidden.bs.modal', function handler() {
      confirmEl.removeEventListener('hidden.bs.modal', handler);
      lpForceClose();
    });
    bootstrap.Modal.getInstance(confirmEl).hide();
  }

  // Reset modal when closed
  document.getElementById('tambahLessonPlanModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('lessonPlanForm').reset();
    const _now = new Date();
    document.getElementById('lpTanggal').value = _now.getFullYear() + '-' + String(_now.getMonth() + 1).padStart(2, '0');
    ['awal', 'inti', 'penutup'].forEach(s => {
      const c = document.getElementById('lpRows_' + s);
      c.innerHTML = '<div class="text-body-secondary small fst-italic lp-empty-hint">Belum ada jadwal. Klik &ldquo;Tambah Jadwal&rdquo; untuk menambahkan.</div>';
    });
    const ppiSelect = document.getElementById('lpPpiSelect');
    ppiSelect.innerHTML = '<option value="">-- Pilih anak didik dulu --</option>';
    ppiSelect.disabled = true;
    window._lpProgramList = [];
    window._lpRiwayat = {};
  });
</script>
@endpush