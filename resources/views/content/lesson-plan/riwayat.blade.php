@extends('layouts/contentNavbarLayout')

@section('title', 'Riwayat Lesson Plan - ' . $anak->nama)

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
  <i class="ri-check-line me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Riwayat Lesson Plan</h4>
            <p class="text-body-secondary mb-0">{{ $anak->nama }}{{ $anak->nis ? ' &middot; NIS: ' . $anak->nis : '' }}</p>
          </div>
          <a href="{{ route('lesson-plan.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line"></i><span class="d-none d-sm-inline ms-1">Kembali</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="lpRiwayatTable" data-anak-id="{{ $anak->id }}">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Bulan</th>
              <th>Periode PPI</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lessonPlans as $i => $lp)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ \Carbon\Carbon::parse($lp->tanggal)->locale('id')->translatedFormat('F Y') }}</td>
              <td>
                @if($lp->ppi && $lp->ppi->periode_mulai && $lp->ppi->periode_selesai)
                {{ \Carbon\Carbon::parse($lp->ppi->periode_mulai)->locale('id')->translatedFormat('F Y') }}
                &ndash;
                {{ \Carbon\Carbon::parse($lp->ppi->periode_selesai)->locale('id')->translatedFormat('F Y') }}
                @else
                <span class="text-body-secondary">-</span>
                @endif
              </td>
              <td>
                {{-- Desktop: tombol icon biasa --}}
                <div class="d-none d-sm-flex gap-2 align-items-center">
                  <button type="button" class="btn btn-sm btn-icon btn-outline-primary lp-lihat-btn"
                    data-lp-id="{{ $lp->id }}" title="Lihat Detail">
                    <i class="ri-eye-line"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-icon btn-outline-warning lp-edit-btn"
                    data-lp-id="{{ $lp->id }}" title="Edit Lesson Plan">
                    <i class="ri-edit-line"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-icon btn-outline-danger lp-delete-btn"
                    data-lp-id="{{ $lp->id }}"
                    data-lp-bulan="{{ \Carbon\Carbon::parse($lp->tanggal)->locale('id')->translatedFormat('F Y') }}"
                    title="Hapus Lesson Plan">
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
                {{-- Mobile: tombol titik tiga (global menu) --}}
                <div class="d-sm-none">
                  <button class="btn btn-sm p-0 border-0 bg-transparent lp-mobile-menu-btn" type="button"
                    data-lp-id="{{ $lp->id }}"
                    data-lp-bulan="{{ \Carbon\Carbon::parse($lp->tanggal)->locale('id')->translatedFormat('F Y') }}"
                    style="box-shadow:none;">
                    <i class="ri-more-2-fill" style="font-size:1.5em;font-weight:bold;"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-5">
                <div class="mb-3"><i class="ri-file-list-line" style="font-size:3rem;color:#ccc"></i></div>
                <p class="text-body-secondary mb-0">Belum ada lesson plan untuk anak ini.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Global Mobile Action Menu (fixed, appended to body via JS) -->
<div id="lpMobileActionMenu" style="display:none;position:fixed;z-index:99999;background:#fff;border:1px solid rgba(0,0,0,.15);border-radius:.5rem;box-shadow:0 .5rem 1.5rem rgba(0,0,0,.175);min-width:11rem;overflow:hidden;">
  <ul class="list-unstyled m-0" style="padding:.375rem 0;">
    <li><button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" id="lpMobileMenuLihat" type="button"><i class="ri-eye-line" style="font-size:1.1em;"></i> Lihat</button></li>
    <li><button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" id="lpMobileMenuEdit" type="button"><i class="ri-edit-line" style="font-size:1.1em;"></i> Edit</button></li>
    <li>
      <hr class="dropdown-divider my-1">
    </li>
    <li><button class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 text-danger" id="lpMobileMenuHapus" type="button"><i class="ri-delete-bin-line" style="font-size:1.1em;"></i> Hapus</button></li>
  </ul>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteLpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="ri-delete-bin-line me-2"></i>Hapus Lesson Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1">Yakin ingin menghapus Lesson Plan bulan</p>
        <p class="fw-bold mb-1" id="confirmDeleteLpBulan"></p>
        <p class="text-body-secondary small mb-0">Program yang aktif pada bulan ini akan dinonaktifkan secara otomatis jika tidak dipakai di lesson plan lain.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="deleteLpForm" method="POST" style="display:inline;">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i>Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Lesson Plan -->
<div class="modal fade" id="lpDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-file-list-2-line me-2"></i>Detail Lesson Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="lpDetailBody">
        <div class="text-center py-4">
          <div class="spinner-border spinner-border-sm text-primary"></div> Memuat...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <a id="lpDetailPdfBtn" href="#" target="_blank" class="btn btn-outline-danger">
          <i class="ri-file-pdf-line me-1"></i>Export PDF
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Lesson Plan -->
<div class="modal fade" id="editLpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit Lesson Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editLpForm" method="POST" style="display:flex;flex-direction:column;flex:1;overflow:hidden;min-height:0;">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Periode PPI</label>
              <select name="ppi_id" id="elpPpiSelect" class="form-select" disabled>
                <option value="">-- Memuat... --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
              <input type="month" name="tanggal" id="elpTanggal" class="form-control" required>
            </div>
          </div>
          <hr>
          @foreach(['awal' => 'Awal', 'inti' => 'Inti', 'penutup' => 'Penutup'] as $sectionKey => $sectionLabel)
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold mb-0">
                <span class="badge bg-{{ $sectionKey === 'awal' ? 'info' : ($sectionKey === 'inti' ? 'primary' : 'success') }} me-2">{{ $sectionLabel }}</span>
              </h6>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="elpAddRow('{{ $sectionKey }}')">
                <i class="ri-add-line me-1"></i>Tambah Jadwal
              </button>
            </div>
            <div id="elpRows_{{ $sectionKey }}" class="d-flex flex-column gap-2">
              <div class="text-body-secondary small fst-italic elp-empty-hint">Belum ada jadwal.</div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Custom Program Detail Overlay -->
<style>
  #lpProgOverlay .pv-badge-gradient {
    background: linear-gradient(90deg, #6f42c1, #7b61ff);
    color: #fff;
    font-weight: 600;
    border-radius: 0.5rem;
    padding: 0.35rem 0.6rem;
    display: inline-block;
  }

  #lpProgOverlay .pv-left {
    background: #fafafa;
    border-radius: 0.5rem;
    padding: 1rem;
  }

  #lpProgOverlay .pv-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f0f4ff, #e8eefc);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #3b5bdb;
  }

  #lpProgOverlay .pv-meta-badge {
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    display: inline-block;
    max-width: 60%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
  }
</style>
<div id="lpProgOverlay" style="display:none;position:fixed;inset:0;z-index:1200;align-items:center;justify-content:center;">
  <div style="position:absolute;inset:0;background:rgba(0,0,0,.5);" onclick="closeProgDetail()"></div>
  <div style="position:relative;background:#fff;border-radius:12px;width:100%;max-width:640px;max-height:85vh;display:flex;flex-direction:column;margin:16px;box-shadow:0 8px 32px rgba(0,0,0,.25);">
    <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
      <h5 class="mb-0 fw-bold">Detail Program</h5>
      <button type="button" class="btn-close" onclick="closeProgDetail()"></button>
    </div>
    <div id="lpProgOverlayBody" style="padding:20px;overflow-y:auto;flex:1;"></div>
    <div style="padding:12px 20px;border-top:1px solid #e0e0e0;text-align:right;">
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeProgDetail()">Tutup</button>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<style>
  .lp-prog-badge {
    cursor: pointer;
    transition: opacity .15s;
  }

  .lp-prog-badge:hover {
    opacity: .75;
  }

  #lpProgOverlay {
    display: none;
  }

  #lpProgOverlay.show {
    display: flex !important;
  }
</style>
<script>
  const sectionColors = {
    awal: 'info',
    inti: 'primary',
    penutup: 'success'
  };
  const sectionLabels = {
    awal: 'Awal',
    inti: 'Inti',
    penutup: 'Penutup'
  };

  let currentPpiId = null;

  // =====================
  // EDIT LESSON PLAN LOGIC
  // =====================
  let _elpProgramList = [];
  let _elpRiwayat = {};
  const elpAnakId = parseInt(document.getElementById('lpRiwayatTable').dataset.anakId, 10);
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

  function elpFmtMY(d) {
    if (!d) return '';
    const dt = new Date(d.replace(' ', 'T'));
    return months[dt.getMonth()] + ' ' + dt.getFullYear();
  }

  function elpAddRow(section, idx, prefill) {
    const container = document.getElementById('elpRows_' + section);
    const hint = container.querySelector('.elp-empty-hint');
    if (hint) hint.remove();
    const actualIdx = idx !== undefined ? idx : container.querySelectorAll('.elp-row').length;

    // Programs already picked in existing rows
    const globallyPicked = new Set(
      Array.from(document.querySelectorAll('#editLpForm .elp-row input[type=hidden][data-prog]')).map(i => i.dataset.prog)
    );
    // Support both ppi_item_ids (new) and nama_program (old compat) for prefill
    const prefillIds = (prefill?.ppi_item_ids || []).map(String).filter(v => v);
    const thisPrefill = new Set(prefillIds);
    const allPicked = new Set([...globallyPicked, ...thisPrefill]);

    let programOpts = '<option value="">-- Pilih Program --</option>';
    (_elpProgramList || []).forEach(p => {
      if (!allPicked.has(String(p.id))) programOpts += `<option value="${p.id}">${p.nama}</option>`;
    });

    const div = document.createElement('div');
    div.className = 'elp-row border rounded p-2';
    div.innerHTML = `
      <div class="row g-2">
        <div class="col-6">
          <label class="form-label small mb-1">Jam Mulai</label>
          <input type="time" name="schedules[${section}_${actualIdx}][jam_mulai]" class="form-control form-control-sm" required value="${prefill?.jam_mulai || ''}">
          <input type="hidden" name="schedules[${section}_${actualIdx}][section]" value="${section}">
        </div>
        <div class="col-6">
          <label class="form-label small mb-1">Jam Selesai</label>
          <input type="time" name="schedules[${section}_${actualIdx}][jam_selesai]" class="form-control form-control-sm" required value="${prefill?.jam_selesai || ''}">
        </div>
        <div class="col-12">
          <label class="form-label small mb-1">Keterangan / Aktivitas</label>
          <textarea name="schedules[${section}_${actualIdx}][keterangan]" class="form-control form-control-sm" rows="3" placeholder="Deskripsi kegiatan...">${(prefill?.keterangan || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
        </div>
        <div class="col-12">
          <label class="form-label small mb-1">Program (opsional)</label>
          <div class="d-flex gap-2 align-items-start">
            <div class="elp-program-wrap flex-grow-1" style="min-width:0">
              <select class="form-select form-select-sm elp-prog-picker" onchange="elpPickProgram(this,'${section}',${actualIdx})">${programOpts}</select>
              <div class="elp-prog-tags d-flex flex-wrap gap-1 mt-1"></div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="elpRemoveRow(this,'${section}')">
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>
      </div>
    `;
    container.appendChild(div);

    // Remove this row's programs from ALL other existing pickers
    if (thisPrefill.size > 0) {
      document.querySelectorAll('#editLpForm .elp-prog-picker').forEach(sel => {
        if (sel.closest('.elp-row') === div) return;
        Array.from(sel.options).forEach(o => {
          if (thisPrefill.has(String(o.value))) o.remove();
        });
      });
    }

    // Add pre-fill hidden inputs and badges
    if (thisPrefill.size > 0) {
      const wrap = div.querySelector('.elp-program-wrap');
      thisPrefill.forEach(idStr => {
        const prog = (_elpProgramList || []).find(p => String(p.id) === idStr);
        const label = prog ? prog.nama : idStr;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `schedules[${section}_${actualIdx}][ppi_item_ids][]`;
        hidden.value = idStr;
        hidden.dataset.prog = idStr;
        wrap.appendChild(hidden);
        const tag = document.createElement('span');
        tag.className = 'badge bg-primary d-inline-flex align-items-center gap-1 elp-prog-tag';
        tag.style.cssText = 'max-width:100%;overflow:hidden;word-break:break-word;white-space:normal;';
        tag.dataset.val = idStr;
        tag.dataset.progName = label;
        tag.innerHTML = `<span title="${label}">${label}</span><i class="ri-close-line ms-1" style="cursor:pointer;font-size:.85em;flex-shrink:0" onclick="elpRemoveProg(this)"></i>`;
        wrap.querySelector('.elp-prog-tags').appendChild(tag);
      });
    }
  }

  function elpPickProgram(select, section, idx) {
    const val = select.value;
    if (!val) return;
    const wrap = select.closest('.elp-program-wrap');
    for (const inp of wrap.querySelectorAll('input[type=hidden]')) {
      if (inp.dataset.prog === val) {
        select.value = '';
        return;
      }
    }
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = `schedules[${section}_${idx}][ppi_item_ids][]`;
    hidden.value = val;
    hidden.dataset.prog = val;
    wrap.appendChild(hidden);
    const prog = (_elpProgramList || []).find(p => String(p.id) === String(val));
    const label = prog ? prog.nama : val;
    const tag = document.createElement('span');
    tag.className = 'badge bg-primary d-inline-flex align-items-center gap-1 elp-prog-tag';
    tag.style.cssText = 'max-width:100%;overflow:hidden;word-break:break-word;white-space:normal;';
    tag.dataset.val = val;
    tag.dataset.progName = label;
    tag.innerHTML = `<span title="${label}">${label}</span><i class="ri-close-line ms-1" style="cursor:pointer;font-size:.85em;flex-shrink:0" onclick="elpRemoveProg(this)"></i>`;
    wrap.querySelector('.elp-prog-tags').appendChild(tag);
    document.querySelectorAll('#editLpForm .elp-prog-picker').forEach(s => {
      Array.from(s.options).forEach(o => {
        if (String(o.value) === String(val)) o.remove();
      });
    });
    select.value = '';
  }

  function elpRemoveProg(icon) {
    const tag = icon.closest('.elp-prog-tag');
    const val = tag.dataset.val;
    const progName = tag.dataset.progName || val;
    const wrap = tag.closest('.elp-program-wrap');
    wrap.querySelectorAll('input[type=hidden]').forEach(inp => {
      if (inp.dataset.prog === val) inp.remove();
    });
    tag.remove();
    document.querySelectorAll('#editLpForm .elp-prog-picker').forEach(s => {
      const w = s.closest('.elp-program-wrap');
      const already = Array.from(w.querySelectorAll('input[type=hidden]')).some(i => i.dataset.prog === val);
      if (!already && !Array.from(s.options).some(o => String(o.value) === String(val))) {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = progName;
        s.appendChild(opt);
      }
    });
  }

  function elpRemoveRow(btn, section) {
    const row = btn.closest('.elp-row');
    const rowPrograms = Array.from(row.querySelectorAll('input[type=hidden][data-prog]')).map(i => i.dataset.prog);
    const container = row.parentElement;
    row.remove();
    const rowProgLabels = {};
    row.querySelectorAll('.elp-prog-tag').forEach(t => {
      rowProgLabels[t.dataset.val] = t.dataset.progName || t.dataset.val;
    });
    rowPrograms.forEach(val => {
      const progName = rowProgLabels[val] || val;
      document.querySelectorAll('#editLpForm .elp-prog-picker').forEach(s => {
        const w = s.closest('.elp-program-wrap');
        const already = Array.from(w.querySelectorAll('input[type=hidden]')).some(i => i.dataset.prog === val);
        if (!already && !Array.from(s.options).some(o => String(o.value) === String(val))) {
          const opt = document.createElement('option');
          opt.value = val;
          opt.textContent = progName;
          s.appendChild(opt);
        }
      });
    });
    container.querySelectorAll('.elp-row').forEach((r, i) => {
      r.querySelectorAll('input, select').forEach(el => {
        const name = el.getAttribute('name');
        if (name) el.setAttribute('name', name.replace(/\[([a-z]+)_\d+\]/, `[${section}_${i}]`));
      });
    });
    if (container.querySelectorAll('.elp-row').length === 0) {
      const hint = document.createElement('div');
      hint.className = 'text-body-secondary small fst-italic elp-empty-hint';
      hint.textContent = 'Belum ada jadwal. Klik "Tambah Jadwal" untuk menambahkan.';
      container.appendChild(hint);
    }
  }

  // Edit button
  document.querySelectorAll('.lp-edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const lpId = this.dataset.lpId;
      document.getElementById('editLpForm').action = '/lesson-plan/' + lpId;
      ['awal', 'inti', 'penutup'].forEach(s => {
        document.getElementById('elpRows_' + s).innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
      });
      document.getElementById('elpPpiSelect').innerHTML = '<option value="">Memuat...</option>';
      document.getElementById('elpPpiSelect').disabled = true;
      document.getElementById('elpTanggal').value = '';
      bootstrap.Modal.getOrCreateInstance(document.getElementById('editLpModal')).show();

      fetch('/lesson-plan/' + lpId + '/edit-json')
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            alert('Gagal memuat data lesson plan.');
            return;
          }
          _elpProgramList = res.ppi_programs || [];
          document.getElementById('elpTanggal').value = res.tanggal;

          fetch('/ppi/riwayat/' + elpAnakId)
            .then(r => r.json())
            .then(ppiRes => {
              const ppiSelect = document.getElementById('elpPpiSelect');
              ppiSelect.innerHTML = '<option value="">-- Tanpa PPI --</option>';
              _elpRiwayat = {};
              if (ppiRes.success && ppiRes.riwayat) {
                ppiRes.riwayat.forEach(p => {
                  const label = (p.periode_mulai && p.periode_selesai) ?
                    elpFmtMY(p.periode_mulai) + ' s/d ' + elpFmtMY(p.periode_selesai) :
                    'PPI #' + p.id;
                  const opt = document.createElement('option');
                  opt.value = p.id;
                  opt.textContent = label;
                  if (p.id == res.ppi_id) opt.selected = true;
                  ppiSelect.appendChild(opt);
                  _elpRiwayat[p.id] = (p.items || []).map(it => ({
                    id: it.id,
                    nama: it.nama_program
                  })).filter(it => it.id && it.nama);
                });
                ppiSelect.disabled = false;
              }
              ['awal', 'inti', 'penutup'].forEach(sec => {
                const container = document.getElementById('elpRows_' + sec);
                container.innerHTML = '';
                const rows = res.schedules[sec] || [];
                if (rows.length === 0) {
                  container.innerHTML = '<div class="text-body-secondary small fst-italic elp-empty-hint">Belum ada jadwal. Klik &ldquo;Tambah Jadwal&rdquo; untuk menambahkan.</div>';
                } else {
                  rows.forEach((row, i) => elpAddRow(sec, i, row));
                }
              });
            });
        })
        .catch(() => alert('Gagal memuat data.'));
    });
  });

  // PPI change in edit modal — rebuild pickers
  document.getElementById('elpPpiSelect').addEventListener('change', function() {
    const ppiId = this.value;
    _elpProgramList = (ppiId && _elpRiwayat[ppiId]) ? _elpRiwayat[ppiId] : [];
    document.querySelectorAll('#editLpForm .elp-row .elp-prog-picker').forEach(sel => {
      const wrap = sel.closest('.elp-program-wrap');
      const picked = Array.from(wrap.querySelectorAll('input[type=hidden]')).map(i => i.dataset.prog);
      sel.innerHTML = '<option value="">-- Pilih Program --</option>';
      _elpProgramList.forEach(p => {
        if (!picked.includes(String(p.id))) {
          const o = document.createElement('option');
          o.value = p.id;
          o.textContent = p.nama;
          sel.appendChild(o);
        }
      });
    });
  });

  // Reset on close
  document.getElementById('editLpModal').addEventListener('hidden.bs.modal', function() {
    ['awal', 'inti', 'penutup'].forEach(s => {
      document.getElementById('elpRows_' + s).innerHTML = '<div class="text-body-secondary small fst-italic elp-empty-hint">Belum ada jadwal.</div>';
    });
    document.getElementById('elpPpiSelect').innerHTML = '<option value="">-- Tanpa PPI --</option>';
    document.getElementById('elpPpiSelect').disabled = true;
    _elpProgramList = [];
    _elpRiwayat = {};
  });

  // =====================
  // LIHAT DETAIL LOGIC
  // =====================
  document.querySelectorAll('.lp-lihat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const lpId = this.dataset.lpId;
      const body = document.getElementById('lpDetailBody');
      const pdfBtn = document.getElementById('lpDetailPdfBtn');
      pdfBtn.href = `/lesson-plan/${lpId}/preview`;
      body.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat...</div>';
      bootstrap.Modal.getOrCreateInstance(document.getElementById('lpDetailModal')).show();

      fetch(`/lesson-plan/${lpId}/detail-json`)
        .then(r => r.json())
        .then(res => {
          if (!res.success) {
            body.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
            return;
          }
          currentPpiId = res.ppi_id || null;
          let html = `
            <div class="mb-3">
              <table class="table table-sm table-borderless mb-0">
                <tr><td class="fw-semibold" style="width:160px;white-space:nowrap">Nama Anak Didik</td><td>: ${res.anak ? res.anak.nama : '-'}</td></tr>
                <tr><td class="fw-semibold">Guru Fokus</td><td>: ${res.anak ? res.anak.guru_fokus : '-'}</td></tr>
                <tr><td class="fw-semibold">Bulan</td><td>: ${res.tanggal}</td></tr>
                ${res.periode ? `<tr><td class="fw-semibold">Periode PPI</td><td>: ${res.periode}</td></tr>` : ''}
              </table>
            </div><hr>`;

          ['awal', 'inti', 'penutup'].forEach(sec => {
            const rows = res.schedules[sec] || [];
            html += `<div class="mb-3">
              <h6 class="fw-bold mb-2"><span class="badge bg-${sectionColors[sec]} me-1">${sectionLabels[sec]}</span></h6>`;
            if (rows.length === 0) {
              html += `<p class="text-body-secondary fst-italic small mb-0">Tidak ada jadwal.</p>`;
            } else {
              html += `<div class="table-responsive"><table class="table table-sm table-bordered mb-0">
                <thead class="table-light"><tr><th style="width:5%">No</th><th style="width:18%">Waktu</th><th style="width:30%">Program</th><th>Aktivitas</th></tr></thead><tbody>`;
              rows.forEach((r, i) => {
                const ids = r.ppi_item_ids || [];
                const names = Array.isArray(r.nama_program) ? r.nama_program : (r.nama_program ? r.nama_program.split(', ') : []);
                let progHtml = '';
                if (ids.length > 0) {
                  progHtml = ids.map((id, idx2) => {
                    const nm = (names[idx2] || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const nmRaw = names[idx2] || String(id);
                    return '<span class="badge bg-label-primary rounded-pill mb-1 me-1 d-inline-block lp-prog-badge" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle" title="' + nm + '" onclick="showProgDetail(' + id + ', \'' + nm + '\')">' + nmRaw + '</span>';
                  }).join('');
                } else if (names.filter(p => p).length > 0) {
                  progHtml = names.filter(p => p).map(p => {
                    const safe = p.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    return '<span class="badge bg-label-primary rounded-pill mb-1 me-1 d-inline-block lp-prog-badge" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle" title="' + safe + '" onclick="showProgDetail(null, \'' + safe + '\')">' + p + '</span>';
                  }).join('');
                } else {
                  progHtml = '<span class="text-body-secondary">-</span>';
                }
                const keteranganHtml = r.keterangan || '<span class="text-body-secondary">-</span>';
                html += '<tr>';
                html += '<td class="text-center">' + (i + 1) + '</td>';
                html += '<td>' + r.jam_mulai + ' &ndash; ' + r.jam_selesai + '</td>';
                html += '<td>' + progHtml + '</td>';
                html += '<td style="white-space:normal;word-break:break-word;min-width:120px;">' + keteranganHtml + '</td>';
                html += '</tr>';
              });
              html += '</tbody></table></div>';
            }
            html += `</div>`;
          });

          body.innerHTML = html;
        })
        .catch(() => {
          body.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
        });
    });
  });

  function closeProgDetail() {
    document.getElementById('lpProgOverlay').classList.remove('show');
  }

  function showProgDetail(ppiItemId, namaProgram) {
    const body = document.getElementById('lpProgOverlayBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat...</div>';
    document.getElementById('lpProgOverlay').classList.add('show');

    if (!ppiItemId && !namaProgram) {
      body.innerHTML = '<p class="text-body-secondary">Detail program tidak tersedia.</p>';
      return;
    }

    const params = new URLSearchParams();
    if (ppiItemId) params.set('ppi_item_id', ppiItemId);
    if (namaProgram) params.set('nama_program', namaProgram);
    if (!ppiItemId && currentPpiId) params.set('ppi_id', currentPpiId);
    fetch('/lesson-plan/program-detail?' + params.toString())
      .then(r => r.json())
      .then(res => {
        if (!res.success) {
          body.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
          return;
        }
        const prog = res.program;
        const nama = res.nama_program || '-';
        const kategori = res.kategori || '';

        const katLower = kategori.toLowerCase();
        let katBadgeClass = 'bg-info';
        if (katLower === 'akademik') katBadgeClass = 'bg-primary';
        else if (katLower === 'bina diri') katBadgeClass = 'bg-success';
        else if (katLower === 'motorik') katBadgeClass = 'bg-warning text-dark';
        else if (katLower === 'perilaku') katBadgeClass = 'bg-danger';
        else if (katLower === 'vokasi') katBadgeClass = 'bg-secondary';
        const katLabel = katLower === 'perilaku' ? 'Basic Learning' : kategori;

        let html = `
          <div class="d-flex gap-3 mb-3 flex-wrap">
            <div class="pv-left d-flex gap-3 align-items-center">
              <div class="pv-icon"><i class="ri-archive-line"></i></div>
              <div>
                <div class="text-muted small">Kode Program</div>
                <div class="pv-badge-gradient">${prog && prog.kode_program ? prog.kode_program : '-'}</div>
              </div>
            </div>
            <div class="flex-grow-1">
              <h5 class="mb-1 fw-bold">${nama}</h5>
              <div class="d-flex gap-2 align-items-center">
                ${kategori ? `<span class="pv-meta-badge badge ${katBadgeClass}">${katLabel}</span>` : ''}
              </div>
            </div>
          </div>`;

        if (!prog) {
          html += '<p class="text-body-secondary">Detail program tidak ditemukan.</p>';
        } else {
          html += '<div class="row">';
          const fields = [
            ['Tujuan', prog.tujuan],
            ['Aktivitas', prog.aktivitas],
            ['Keterangan', prog.keterangan],
          ];
          fields.forEach(([label, val]) => {
            html += `<div class="col-md-4"><div class="mb-3"><div class="text-muted small mb-1">${label}</div><div class="text-body-secondary">${val || '-'}</div></div></div>`;
          });
          html += '</div>';
          if (prog.metode || prog.durasi || prog.deskripsi) {
            html += '<div class="row">';
            const extra = [
              ['Metode', prog.metode],
              ['Durasi', prog.durasi],
              ['Deskripsi', prog.deskripsi]
            ];
            extra.forEach(([label, val]) => {
              if (!val) return;
              html += `<div class="col-md-4"><div class="mb-3"><div class="text-muted small mb-1">${label}</div><div class="text-body-secondary">${val}</div></div></div>`;
            });
            html += '</div>';
          }
        }

        body.innerHTML = html;
      })
      .catch(() => {
        body.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
      });
  }

  // Delete lesson plan
  document.querySelectorAll('.lp-delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const lpId = this.dataset.lpId;
      const bulan = this.dataset.lpBulan;
      document.getElementById('confirmDeleteLpBulan').textContent = bulan + '?';
      document.getElementById('deleteLpForm').action = '/lesson-plan/' + lpId;
      bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteLpModal')).show();
    });
  });

  // Global mobile action menu
  (function() {
    const menu = document.getElementById('lpMobileActionMenu');
    document.body.appendChild(menu);

    let activeId = null,
      activeBulan = null;

    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.lp-mobile-menu-btn');
      if (btn) {
        e.stopPropagation();
        activeId = btn.dataset.lpId;
        activeBulan = btn.dataset.lpBulan;
        const rect = btn.getBoundingClientRect();
        menu.style.top = (rect.bottom + 4) + 'px';
        const right = window.innerWidth - rect.right;
        menu.style.right = right + 'px';
        menu.style.left = 'auto';
        menu.style.display = 'block';
      } else if (!e.target.closest('#lpMobileActionMenu')) {
        menu.style.display = 'none';
      }
    });

    document.getElementById('lpMobileMenuLihat').addEventListener('click', function() {
      menu.style.display = 'none';
      document.querySelector(`.lp-lihat-btn[data-lp-id="${activeId}"]`).click();
    });

    document.getElementById('lpMobileMenuEdit').addEventListener('click', function() {
      menu.style.display = 'none';
      document.querySelector(`.lp-edit-btn[data-lp-id="${activeId}"]`).click();
    });

    document.getElementById('lpMobileMenuHapus').addEventListener('click', function() {
      menu.style.display = 'none';
      document.getElementById('confirmDeleteLpBulan').textContent = activeBulan + '?';
      document.getElementById('deleteLpForm').action = '/lesson-plan/' + activeId;
      bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteLpModal')).show();
    });
  })();
</script>
@endpush