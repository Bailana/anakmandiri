@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Program Vokasi')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Program Vokasi</h5>
        <a href="{{ route('vokasi.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form id="vokasiCreateForm" method="POST" action="{{ route('vokasi.store') }}">
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
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Jenis Vokasi (centang yang sesuai)</label>
              <div class="d-flex flex-wrap gap-2">
                @php
                $jenisList = ['Painting','Cooking','Craft','Computer','Gardening','Beauty','Auto Wash','House Keeping'];
                @endphp
                @foreach($jenisList as $j)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="jenis_vokasi[]" value="{{ $j }}" id="jenis_{{ Str::slug($j) }}">
                  <label class="form-check-label" for="jenis_{{ Str::slug($j) }}">{{ $j }}</label>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          <div class="col-md-12" id="daftarProgramAnakWrapper">
            <label class="form-label">Daftar Program Anak</label>
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="programItemsTable">
                <thead class="table-light">
                  <tr>
                    <th style="width:15%">Kode Program</th>
                    <th style="width:20%">Nama Program</th>
                    <th style="width:35%">Tujuan</th>
                    <th style="width:35%">Aktivitas</th>
                    <th style="width:10%">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <select name="program_items[0][kode_program]" class="form-select kode-select"></select>
                      <input type="hidden" name="program_items[0][program_konsultan_id]" class="program-konsultan-id">
                    </td>
                    <td><input type="text" name="program_items[0][nama_program]" class="form-control nama-input" required placeholder="Ketik nama untuk mencari..."></td>
                    <td><textarea name="program_items[0][tujuan]" class="form-control tujuan-input" rows="1" readonly required></textarea></td>
                    <td><textarea name="program_items[0][aktivitas]" class="form-control aktivitas-input" rows="1" readonly required></textarea></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
                  </tr>
                </tbody>
                <!-- Tombol tambah baris di dalam tabel -->
                <tr>
                  <td colspan="4">
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
              <input type="date" name="periode_mulai" id="periode_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="periode_selesai" class="form-label">Periode Selesai</label>
              <input type="date" name="periode_selesai" id="periode_selesai" class="form-control" required>
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
            <a href="{{ route('vokasi.index') }}" class="btn btn-outline-danger"><i class="ri-close-line me-2"></i>Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@push('page-script')
<script>
  // program master templates grouped by konsultan_id
  const programMastersByKonsultan = @json($programMasters ?? []);
  // flat list fallback of all vokasi program masters
  const programMastersFlat = @json($programMastersFlat ?? []);

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  let barisIdx = 1;

  function buildKodeSelectHtml(idx) {
    const konsultanId = document.getElementById('konsultan_id').value || '';
    let items = (programMastersByKonsultan[konsultanId] || []);
    if (!items || items.length === 0) items = programMastersFlat || [];
    let html = `<select name="program_items[${idx}][kode_program]" class="form-select kode-select"><option value="">Pilih Kode</option>`;
    items.forEach(item => {
      const nama = escapeHtml(item.nama_program || item.nama || '');
      const tujuan = escapeHtml(item.tujuan || '');
      const aktivitas = escapeHtml(item.aktivitas || '');
      const kode = escapeHtml(item.kode_program || '');
      const id = item.id || '';
      html += `<option value="${kode}" data-id="${id}" data-nama="${nama}" data-tujuan="${tujuan}" data-aktivitas="${aktivitas}">${kode} - ${nama}</option>`;
    });
    html += '</select>';
    return html;
  }

  function refreshNamaOptions() {
    return;
  }

  document.getElementById('btnTambahBaris').addEventListener('click', function() {
    const tbody = document.querySelector('#programItemsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
    <td>${buildKodeSelectHtml(barisIdx)}<input type="hidden" name="program_items[${barisIdx}][program_konsultan_id]" class="program-konsultan-id"></td>
    <td><input type="text" name="program_items[${barisIdx}][nama_program]" class="form-control nama-input" required placeholder="Ketik nama untuk mencari..."></td>
    <td><textarea name="program_items[${barisIdx}][tujuan]" class="form-control tujuan-input" rows="1" readonly required></textarea></td>
    <td><textarea name="program_items[${barisIdx}][aktivitas]" class="form-control aktivitas-input" rows="1" readonly required></textarea></td>
    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
  `;
    tbody.appendChild(tr);
    refreshNamaOptions();
    const wrapperAfterAdd = document.getElementById('daftarProgramAnakWrapper');
    const hiddenAfterAdd = wrapperAfterAdd && wrapperAfterAdd.style.display === 'none';
    if (hiddenAfterAdd) {
      tr.querySelectorAll('input,textarea,select').forEach(el => el.disabled = true);
      const addBtnAfter = document.getElementById('btnTambahBaris');
      if (addBtnAfter) addBtnAfter.disabled = true;
    }
    barisIdx++;
  });

  document.querySelector('#programItemsTable').addEventListener('click', function(e) {
    if (e.target.closest('.btn-hapus-baris')) {
      const tr = e.target.closest('tr');
      if (tr.parentNode.children.length > 1) tr.remove();
    }
  });

  document.querySelector('#programItemsTable').addEventListener('change', function(e) {
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
      if (namaInput) namaInput.value = nama;
      if (tujuanInput) tujuanInput.value = tujuan;
      if (aktivitasInput) aktivitasInput.value = aktivitas;
      if (pkInput) pkInput.value = pid;
    }
  });

  document.querySelector('#programItemsTable').addEventListener('keydown', function(e) {
    const el = e.target;
    if (!el || !el.classList.contains('nama-input')) return;
    if (e.key === 'ArrowDown') {
      const tr = el.closest('tr');
      if (!tr) return;
      const kodeSel = tr.querySelector('.kode-select');
      if (kodeSel) kodeSel.focus();
      return;
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = (el.value || '').trim().toLowerCase();
      const tr = el.closest('tr');
      if (!tr) return;
      const kodeSel = tr.querySelector('.kode-select');
      if (!kodeSel) return;
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

  document.querySelector('#programItemsTable').addEventListener('input', function(e) {
    const el = e.target;
    if (!el || !el.classList.contains('nama-input')) return;
    const tr = el.closest('tr');
    if (!tr) return;
    const noEl = tr.querySelector('.nama-no-match');
    if (noEl) noEl.remove();
  });

  function refreshKodeOptions() {
    const konsultanId = document.getElementById('konsultan_id').value || '';
    let items = (programMastersByKonsultan[konsultanId] || []);
    if (!items || items.length === 0) items = programMastersFlat || [];
    document.querySelectorAll('.kode-select').forEach(sel => {
      const prev = sel.value;
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
      sel.value = prev;
      if (prev) {
        const tr = sel.closest('tr');
        const pkInput = tr ? tr.querySelector('.program-konsultan-id') : null;
        if (pkInput) {
          const match = items.find(i => (i.kode_program || '') === prev);
          pkInput.value = match ? (match.id || '') : '';
        }
      }
    });
  }

  function onKonsultanOrAnakChange() {
    refreshKodeOptions();
    refreshNamaOptions();
  }

  function toggleDaftarProgramAnak() {
    const select = document.getElementById('konsultan_id');
    const selected = select.options[select.selectedIndex];
    const spesialisasi = selected ? selected.getAttribute('data-spesialisasi') : '';
    const wrapper = document.getElementById('daftarProgramAnakWrapper');
    const psikologiFields = document.getElementById('psikologiFields');
    if (spesialisasi === 'wicara' || spesialisasi === 'sensori integrasi' || spesialisasi === 'pendidikan') {
      wrapper.style.display = '';
    } else {
      wrapper.style.display = 'none';
    }
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
    refreshKodeOptions();
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

  document.addEventListener('DOMContentLoaded', function() {
    toggleDaftarProgramAnak();
    const anakSelect = document.getElementById('anak_didik_id');
    const konsultanSelect = document.getElementById('konsultan_id');

    function fetchAndFillPsikologi() {
      try {
        const konsultanOpt = konsultanSelect.options[konsultanSelect.selectedIndex];
        const spes = konsultanOpt ? konsultanOpt.getAttribute('data-spesialisasi') : '';
        if (spes !== 'psikologi') {
          const psikologiFields = document.getElementById('psikologiFields');
          if (psikologiFields) {
            psikologiFields.style.display = 'none';
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
        fetch(`/vokasi/psikologi-latest/${anakId}`)
          .then(res => res.json())
          .then(json => {
            const data = json && json.data ? json.data : null;
            const ids = ['latar_belakang', 'metode_assessment', 'hasil_assessment', 'diagnosa', 'kesimpulan'];
            ids.forEach(id => {
              const el = document.getElementById(id);
              if (el) {
                el.value = data && data[id] ? data[id] : '';
                el.disabled = true;
              }
            });
            const r = document.getElementById('rekomendasi');
            if (r) {
              if (data && data.rekomendasi) r.value = data.rekomendasi;
              r.disabled = false;
            }
            const psikologiFields = document.getElementById('psikologiFields');
            if (psikologiFields) psikologiFields.style.display = '';
          }).catch(() => {
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
      applyAnakVokasi(this.value);
    });
    konsultanSelect && konsultanSelect.addEventListener('change', function() {
      fetchAndFillPsikologi();
      onKonsultanOrAnakChange();
    });

    @php
    $anakVMap = [];
    foreach($anakDidiks ?? [] as $a) {
      $anakVMap[$a->id] = $a->vokasi_diikuti ?? [];
    }
    @endphp
    // Map of anak_didik id -> vokasi_diikuti array
    const anakVokasiMap = @json($anakVMap);

    function applyAnakVokasi(anakId) {
      try {
        const vals = anakVokasiMap && anakVokasiMap[anakId] ? anakVokasiMap[anakId] : [];
        const checks = document.querySelectorAll('input[name="jenis_vokasi[]"]');
        checks.forEach(cb => {
          try {
            cb.checked = false;
          } catch (e) {}
        });
        if (!Array.isArray(vals)) return;
        vals.forEach(v => {
          const sel = document.querySelector('input[name="jenis_vokasi[]"][value="' + String(v).replace(/"/g, '&quot;') + '"]');
          if (sel) sel.checked = true;
        });
      } catch (e) {}
    }

    // Apply initially if a anak is pre-selected
    if (anakSelect && anakSelect.value) {
      applyAnakVokasi(anakSelect.value);
    }

    // On submit, copy the displayed (disabled) checked vokasi into hidden inputs named vokasi_diikuti[]
    const vkForm = document.getElementById('vokasiCreateForm');
    if (vkForm) {
      vkForm.addEventListener('submit', function(e) {
        try {
          // remove previous hidden inputs
          document.querySelectorAll('input[name="vokasi_diikuti[]"]').forEach(n => n.remove());
          const checks = document.querySelectorAll('input[name="jenis_vokasi[]"]');
          checks.forEach(cb => {
            if (cb.checked) {
              const h = document.createElement('input');
              h.type = 'hidden';
              h.name = 'vokasi_diikuti[]';
              h.value = cb.value;
              vkForm.appendChild(h);
            }
          });
        } catch (e) {}
      });
    }
  });
</script>
@endpush
@endsection