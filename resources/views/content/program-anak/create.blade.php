@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Program Anak')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Program Anak</h5>
        <a href="{{ route('program-anak.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('program-anak.store') }}">
          @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="konsultan_id" class="form-label">Nama Konsultan</label>
              <select name="konsultan_id" id="konsultan_id" class="form-select" required
                @if(isset($currentKonsultanId) && isset($currentKonsultanSpesRaw) && preg_match('/pendidikan|wicara|psikologi|sensori/i', $currentKonsultanSpesRaw)) disabled @endif>
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" data-spesialisasi="{{ strtolower($konsultan->spesialisasi) }}"
                  @if(isset($currentKonsultanId) && $currentKonsultanId==$konsultan->id && isset($currentKonsultanSpesRaw) && preg_match('/pendidikan|wicara|psikologi|sensori/i', $currentKonsultanSpesRaw)) selected @endif>
                  {{ $konsultan->nama }}
                </option>
                @endforeach
              </select>
              @if(isset($currentKonsultanId) && isset($currentKonsultanSpesRaw) && preg_match('/pendidikan|wicara|psikologi|sensori/i', $currentKonsultanSpesRaw))
              <input type="hidden" name="konsultan_id" value="{{ $currentKonsultanId }}">
              @endif
            </div>
            <div class="col-md-6">
              <label for="anak_didik_id" class="form-label">Nama Anak Didik</label>
              <select name="anak_didik_id" id="anak_didik_id" class="form-select" required>
                <option value="">Pilih Anak Didik</option>
                @foreach(collect($anakDidiks)->sortBy(function($a) { return mb_strtoupper($a->nama ?? ''); }) as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-12" id="daftarProgramAnakWrapper">
            <label class="form-label">Daftar Program Anak</label>
            <div class="table-responsive" id="programTableWrapper">
              <table class="table table-bordered align-middle mb-0" id="programItemsTable" style="table-layout:fixed">
                <thead class="table-light">
                  <tr>
                    <th style="width:15%">Kode Program</th>
                    <th style="width:20%">Nama Program</th>
                    <th style="width:25%">Tujuan</th>
                    <th style="width:25%">Aktivitas</th>
                    @if(isset($currentKonsultanSpesRaw) && preg_match('/pendidikan/i', $currentKonsultanSpesRaw))
                    <th style="width:12%">Kategori</th>
                    @endif
                    <th style="width:10%">Aksi</th>
                  </tr>
                </thead>
                <tbody id="programItemsTbody">
                  <tr>
                    <td>
                      <select name="program_items[0][kode_program]" class="form-select kode-select" data-populated="false"></select>
                      <input type="hidden" name="program_items[0][program_konsultan_id]" class="program-konsultan-id">
                    </td>
                    <td><input type="text" name="program_items[0][nama_program]" class="form-control nama-input" required placeholder="Ketik nama untuk mencari..."></td>
                    <td><textarea name="program_items[0][tujuan]" class="form-control tujuan-input" rows="1" readonly required></textarea></td>
                    <td><textarea name="program_items[0][aktivitas]" class="form-control aktivitas-input" rows="1" readonly required></textarea></td>
                    @if(isset($currentKonsultanSpesRaw) && preg_match('/pendidikan/i', $currentKonsultanSpesRaw))
                    <td>
                      <select name="program_items[0][kategori]" class="form-select kategori-select">
                        <option value="">Pilih Kategori</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Bina Diri">Bina Diri</option>
                        <option value="Motorik">Motorik</option>
                        <option value="Perilaku">Basic Learning</option>
                        <option value="Vokasi">Vokasi</option>
                      </select>
                    </td>
                    @endif
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
                  </tr>
                </tbody>
                <!-- Tombol tambah baris di dalam tabel -->
                <tr>
                  <td colspan="{{ (isset($currentKonsultanSpesRaw) && preg_match('/pendidikan/i', $currentKonsultanSpesRaw)) ? 5 : 4 }}">
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnTambahBaris"><i class="ri-add-line"></i> Tambah Baris</button>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          @csrf

          <div class="row mb-3 mt-2">
            <div class="col-md-6">
              <label for="periode_mulai" class="form-label">Periode Mulai</label>
              <input type="month" name="periode_mulai" id="periode_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="periode_selesai" class="form-label">Periode Selesai</label>
              <input type="month" name="periode_selesai" id="periode_selesai" class="form-control" required>
            </div>
          </div>
          <!-- Kolom khusus untuk konsultan psikologi -->
          <div class="row" id="psikologiFields" style="display:none;">
            <div class="col-md-12 mb-2">
              <label for="latar_belakang" class="form-label">Latar Belakang</label>
              <textarea name="latar_belakang" id="latar_belakang" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="metode_assessment" class="form-label">Metode Assessment</label>
              <textarea name="metode_assessment" id="metode_assessment" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="hasil_assessment" class="form-label">Hasil Assessment</label>
              <textarea name="hasil_assessment" id="hasil_assessment" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="diagnosa" class="form-label">Diagnosa</label>
              <textarea name="diagnosa" id="diagnosa" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="kesimpulan" class="form-label">Kesimpulan</label>
              <textarea name="kesimpulan" id="kesimpulan" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="rekomendasi" class="form-label">Rekomendasi</label>
              <textarea name="rekomendasi" id="rekomendasi" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="keterangan" class="form-label">Keterangan</label>
              <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
            </div>
          </div>
          @if(!(isset($currentKonsultanSpesRaw) && preg_match('/pendidikan/i', $currentKonsultanSpesRaw)))
          <div class="row mb-3">
            <div class="col-md-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_suggested" name="is_suggested" value="1">
                <label class="form-check-label" for="is_suggested">Sarankan terapi</label>
              </div>
            </div>
          </div>
          @endif
          <div class="d-flex justify-content-start gap-2">
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-2"></i>Simpan</button>
            <a href="{{ route('program-anak.index') }}" class="btn btn-outline-danger"><i class="ri-close-line me-2"></i>Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- CSS: wrapper scrollable dengan sticky thead --}}
<style>
  #programTableWrapper {
    max-height: 360px;
    overflow-y: auto;
  }

  #programItemsTable thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #f8f9fa;
  }
</style>
{{-- Template baris program anak, di-parse sekali oleh browser, di-clone tiap tambah baris --}}
<template id="rowTemplate">
  <tr>
    <td class="kode-td"><input type="hidden" name="program_items[ROWIDX][program_konsultan_id]" class="program-konsultan-id"></td>
    <td><input type="text" name="program_items[ROWIDX][nama_program]" class="form-control nama-input" required placeholder="Ketik nama untuk mencari..."></td>
    <td><textarea name="program_items[ROWIDX][tujuan]" class="form-control tujuan-input" rows="1" readonly required></textarea></td>
    <td><textarea name="program_items[ROWIDX][aktivitas]" class="form-control aktivitas-input" rows="1" readonly required></textarea></td>
    @if(isset($currentKonsultanSpesRaw) && preg_match('/pendidikan/i', $currentKonsultanSpesRaw))
    <td>
      <select name="program_items[ROWIDX][kategori]" class="form-select kategori-select">
        <option value="">Pilih Kategori</option>
        <option value="Akademik">Akademik</option>
        <option value="Bina Diri">Bina Diri</option>
        <option value="Motorik">Motorik</option>
        <option value="Perilaku">Basic Learning</option>
        <option value="Vokasi">Vokasi</option>
      </select>
    </td>
    @endif
    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
  </tr>
</template>
@push('page-script')
<script>
  // program master templates grouped by konsultan_id
  const programMastersByKonsultan = @json($programMasters ?? []);

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  let barisIdx = 1;
  const isPendidikan = @json(isset($currentKonsultanSpesRaw) && (bool) preg_match('/pendidikan/i', $currentKonsultanSpesRaw));

  // Opsi kode-select diisi secara lazy (hanya saat user buka select)
  // sehingga cloneNode hanya menduplikasi 1 option, bukan ratusan
  let _kodeItems = null;

  function getKodeItems() {
    if (!_kodeItems) {
      const konsultanId = document.getElementById('konsultan_id').value || '';
      _kodeItems = programMastersByKonsultan[konsultanId] || [];
    }
    return _kodeItems;
  }

  function buildKodeSelectEl(idx) {
    const sel = document.createElement('select');
    sel.className = 'form-select kode-select';
    sel.name = `program_items[${idx}][kode_program]`;
    sel.dataset.populated = 'false';
    const defOpt = document.createElement('option');
    defOpt.value = '';
    defOpt.textContent = 'Pilih Kode';
    sel.appendChild(defOpt);
    return sel;
  }

  function populateKodeSelect(sel) {
    if (sel.dataset.populated === 'true') return;
    sel.dataset.populated = 'true';
    const items = getKodeItems();
    const frag = document.createDocumentFragment();
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.kode_program || '';
      opt.dataset.id = item.id || '';
      opt.dataset.nama = item.nama_program || item.nama || '';
      opt.dataset.tujuan = item.tujuan || '';
      opt.dataset.aktivitas = item.aktivitas || '';
      opt.textContent = (item.kode_program || '') + ' - ' + (item.nama_program || item.nama || '');
      frag.appendChild(opt);
    });
    sel.appendChild(frag);
  }

  // (removed nama-select population — Nama Program is a plain input)
  // nama-select removed; provide a no-op refresher to avoid JS errors from leftover calls
  function refreshNamaOptions() {
    // no-op: Nama Program is a plain text input now
    return;
  }

  // Clone baris dari <template> (sudah di-parse sekali saat page load, jauh lebih cepat)
  function cloneRowTemplate(idx) {
    const tmpl = document.getElementById('rowTemplate');
    const tr = tmpl.content.cloneNode(true).querySelector('tr');
    tr.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace(/ROWIDX/g, idx);
    });
    return tr;
  }

  const programItemsTbody = document.getElementById('programItemsTbody');

  document.getElementById('btnTambahBaris').addEventListener('click', function() {
    const tr = cloneRowTemplate(barisIdx);
    // Sisipkan kode-select ke elemen detached, lalu append sekali ke DOM (1x reflow)
    const kodeTd = tr.querySelector('.kode-td');
    kodeTd.insertBefore(buildKodeSelectEl(barisIdx), kodeTd.firstChild);
    programItemsTbody.appendChild(tr);
    // Scroll wrapper ke baris terbaru
    const wrapper = document.getElementById('programTableWrapper');
    wrapper.scrollTop = wrapper.scrollHeight;
    // Refresh nama selects for the new row
    refreshNamaOptions();
    // If the program list wrapper is hidden, disable newly added inputs to avoid browser validation errors
    const wrapperAfterAdd = document.getElementById('daftarProgramAnakWrapper');
    const hiddenAfterAdd = wrapperAfterAdd && wrapperAfterAdd.style.display === 'none';
    if (hiddenAfterAdd) {
      tr.querySelectorAll('input,textarea,select').forEach(el => el.disabled = true);
      const addBtnAfter = document.getElementById('btnTambahBaris');
      if (addBtnAfter) addBtnAfter.disabled = true;
    }
    barisIdx++;
  });

  // delete row
  programItemsTbody.addEventListener('click', function(e) {
    if (e.target.closest('.btn-hapus-baris')) {
      const tr = e.target.closest('tr');
      if (programItemsTbody.children.length > 1) tr.remove();
    }
  });

  // Populate kode-select secara lazy saat user pertama kali membukanya
  programItemsTbody.addEventListener('mousedown', function(e) {
    const sel = e.target.closest('.kode-select');
    if (sel) populateKodeSelect(sel);
  });

  // when a kode-select changes, autofill nama/tujuan/aktivitas if they are empty
  programItemsTbody.addEventListener('change', function(e) {
    if (e.target.classList.contains('kode-select')) {
      const sel = e.target;
      const tr = sel.closest('tr');
      const namaInput = tr.querySelector('.nama-input');
      const tujuanInput = tr.querySelector('.tujuan-input');
      const aktivitasInput = tr.querySelector('.aktivitas-input');
      const pkInput = tr.querySelector('.program-konsultan-id');
      const opt = sel.options[sel.selectedIndex];
      if (!opt) return;
      const nama = opt.getAttribute('data-nama') || '';
      const tujuan = opt.getAttribute('data-tujuan') || '';
      const aktivitas = opt.getAttribute('data-aktivitas') || '';
      const pid = opt.getAttribute('data-id') || '';
      // Always set fields to selected template values (override)
      if (namaInput) namaInput.value = nama;
      if (tujuanInput) tujuanInput.value = tujuan;
      if (aktivitasInput) aktivitasInput.value = aktivitas;
      if (pkInput) pkInput.value = pid;
    }
  });

  // allow ArrowDown from nama-input to focus kode-select; Enter will perform search/select
  programItemsTbody.addEventListener('keydown', function(e) {
    const el = e.target;
    if (!el || !el.classList.contains('nama-input')) return;
    // ArrowDown: focus kode select (populate dulu sebelum fokus)
    if (e.key === 'ArrowDown') {
      const tr = el.closest('tr');
      if (!tr) return;
      const kodeSel = tr.querySelector('.kode-select');
      if (kodeSel) {
        populateKodeSelect(kodeSel);
        kodeSel.focus();
      }
      return;
    }
    // Enter: perform selection based on current input value
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = (el.value || '').trim().toLowerCase();
      const tr = el.closest('tr');
      if (!tr) return;
      const kodeSel = tr.querySelector('.kode-select');
      if (!kodeSel) return;
      // Pastikan opsi sudah dimuat sebelum mencari
      populateKodeSelect(kodeSel);
      if (!q) {
        kodeSel.selectedIndex = 0;
        kodeSel.dispatchEvent(new Event('change', {
          bubbles: true
        }));
        return;
      }
      let matchIndex = -1;
      for (let i = 1; i < kodeSel.options.length; i++) {
        const opt = kodeSel.options[i];
        const name = (opt.getAttribute('data-nama') || opt.text || '').toLowerCase();
        if (name.indexOf(q) === 0) {
          matchIndex = i;
          break;
        }
      }
      if (matchIndex === -1) {
        for (let i = 1; i < kodeSel.options.length; i++) {
          const opt = kodeSel.options[i];
          const name = (opt.getAttribute('data-nama') || opt.text || '').toLowerCase();
          if (name.indexOf(q) !== -1) {
            matchIndex = i;
            break;
          }
        }
      }
      if (matchIndex !== -1) {
        kodeSel.selectedIndex = matchIndex;
        kodeSel.dispatchEvent(new Event('change', {
          bubbles: true
        }));
      } else {
        // show inline not-found message
        try {
          let noEl = tr.querySelector('.nama-no-match');
          if (!noEl) {
            noEl = document.createElement('div');
            noEl.className = 'text-danger small nama-no-match mt-1';
            noEl.textContent = 'Program tidak ditemukan';
            const nameInput = tr.querySelector('.nama-input');
            if (nameInput && nameInput.parentNode) nameInput.parentNode.appendChild(noEl);
          }
        } catch (e) {}
      }
    }
  });

  // clear not-found message when user types again in nama-input
  programItemsTbody.addEventListener('input', function(e) {
    const el = e.target;
    if (!el || !el.classList.contains('nama-input')) return;
    const tr = el.closest('tr');
    if (!tr) return;
    const noEl = tr.querySelector('.nama-no-match');
    if (noEl) noEl.remove();
  });

  // update kode-select options when konsultan changes
  function refreshKodeOptions() {
    const konsultanId = document.getElementById('konsultan_id').value || '';
    const items = (programMastersByKonsultan[konsultanId] || []);
    document.querySelectorAll('.kode-select').forEach(sel => {
      const prev = sel.value;
      // clear
      sel.innerHTML = '<option value="">Pilih Kode</option>';
      items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.kode_program || '';
        opt.setAttribute('data-id', item.id || '');
        opt.setAttribute('data-nama', item.nama_program || item.nama || '');
        opt.setAttribute('data-tujuan', item.tujuan || '');
        opt.setAttribute('data-aktivitas', item.aktivitas || '');
        opt.textContent = (item.kode_program || '') + ' - ' + (item.nama_program || item.nama || '');
        sel.appendChild(opt);
      });
      // restore previous selection if still available
      sel.value = prev;
      // if we restored a previous value, set the hidden program_konsultan_id for that row
      if (prev) {
        const tr = sel.closest('tr');
        const pkInput = tr ? tr.querySelector('.program-konsultan-id') : null;
        if (pkInput) {
          // find matching item by kode_program
          const match = items.find(i => (i.kode_program || '') === prev);
          pkInput.value = match ? (match.id || '') : '';
        }
      }
    });
  }

  // ensure nama selects are refreshed whenever konsultan or anak changes
  function onKonsultanOrAnakChange() {
    // invalidate cache when anak changes (no-op since childProgramsCache removed)
    refreshKodeOptions();
    refreshNamaOptions();
  }

  // Tampilkan/hidden form daftar program anak & field psikologi sesuai konsultan
  function toggleDaftarProgramAnak() {
    const select = document.getElementById('konsultan_id');
    const selected = select.options[select.selectedIndex];
    const spesialisasi = selected ? selected.getAttribute('data-spesialisasi') : '';
    const wrapper = document.getElementById('daftarProgramAnakWrapper');
    const psikologiFields = document.getElementById('psikologiFields');
    // Tampilkan daftar program anak untuk wicara, sensori integrasi, atau pendidikan
    if (spesialisasi === 'wicara' || spesialisasi === 'sensori integrasi' || spesialisasi === 'pendidikan') {
      wrapper.style.display = '';
    } else {
      wrapper.style.display = 'none';
    }
    // Tampilkan field psikologi jika konsultan psikologi
    if (spesialisasi === 'psikologi') {
      psikologiFields.style.display = '';
      const r = document.getElementById('rekomendasi');
      if (r) {
        r.required = true;
        r.disabled = false;
      }
    } else {
      psikologiFields.style.display = 'none';
      const r2 = document.getElementById('rekomendasi');
      if (r2) {
        r2.required = false;
      }
    }
    // refresh kode options for all rows
    refreshKodeOptions();
    // enable/disable inputs inside wrapper depending on visibility to avoid browser constraint validation
    const hidden = wrapper.style.display === 'none';
    wrapper.querySelectorAll('input,textarea,select').forEach(el => {
      el.disabled = hidden;
    });
    const addBtn = document.getElementById('btnTambahBaris');
    if (addBtn) addBtn.disabled = hidden;
  }
  document.getElementById('konsultan_id').addEventListener('change', function() {
    toggleDaftarProgramAnak();
    onKonsultanOrAnakChange();
  });

  // Inisialisasi saat load
  document.addEventListener('DOMContentLoaded', function() {
    toggleDaftarProgramAnak();
    // when anak didik changes and konsultan is psikologi, fetch latest psikologi record
    const anakSelect = document.getElementById('anak_didik_id');
    const konsultanSelect = document.getElementById('konsultan_id');

    function fetchAndFillPsikologi() {
      try {
        const konsultanOpt = konsultanSelect.options[konsultanSelect.selectedIndex];
        const spes = konsultanOpt ? konsultanOpt.getAttribute('data-spesialisasi') : '';
        if (spes !== 'psikologi') {
          // ensure psikologi fields hidden/cleared
          const psikologiFields = document.getElementById('psikologiFields');
          if (psikologiFields) {
            psikologiFields.style.display = 'none';
            // clear and enable
            ['latar_belakang', 'metode_assessment', 'hasil_assessment', 'diagnosa', 'kesimpulan', 'rekomendasi'].forEach(id => {
              const el = document.getElementById(id);
              if (el) {
                el.value = '';
                el.disabled = false;
              }
            });
          }
          return;
        }
        const anakId = anakSelect.value;
        if (!anakId) return;
        fetch(`/program-anak/psikologi-latest/${anakId}`)
          .then(res => res.json())
          .then(json => {
            const data = json && json.data ? json.data : null;
            const ids = ['latar_belakang', 'metode_assessment', 'hasil_assessment', 'diagnosa', 'kesimpulan'];
            ids.forEach(id => {
              const el = document.getElementById(id);
              if (el) {
                el.value = data && data[id] ? data[id] : '';
                el.disabled = true; // make readonly/uneditable
              }
            });
            // allow user to input rekomendasi — populate if available and keep enabled
            const r = document.getElementById('rekomendasi');
            if (r) {
              if (data && data.rekomendasi) r.value = data.rekomendasi;
              r.disabled = false;
            }
            // ensure psikologi fields visible
            const psikologiFields = document.getElementById('psikologiFields');
            if (psikologiFields) psikologiFields.style.display = '';
          }).catch(() => {
            // on error, clear psikologi fields but keep rekomendasi editable
            ['latar_belakang', 'metode_assessment', 'hasil_assessment', 'diagnosa', 'kesimpulan'].forEach(id => {
              const el = document.getElementById(id);
              if (el) {
                el.value = '';
                el.disabled = true;
              }
            });
            const rErr = document.getElementById('rekomendasi');
            if (rErr) {
              rErr.value = '';
              rErr.disabled = false;
            }
          });
      } catch (e) {}
    }
    anakSelect && anakSelect.addEventListener('change', function() {
      fetchAndFillPsikologi();
      onKonsultanOrAnakChange();
    });
    konsultanSelect && konsultanSelect.addEventListener('change', function() {
      fetchAndFillPsikologi();
      onKonsultanOrAnakChange();
    });
  });
</script>
@endpush
@endsection