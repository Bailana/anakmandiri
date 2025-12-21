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
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }}</option>
                @endforeach
              </select>
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
                    <td><input type="text" name="program_items[0][nama_program]" class="form-control nama-input" required></td>
                    <td><textarea name="program_items[0][tujuan]" class="form-control tujuan-input" rows="1" required></textarea></td>
                    <td><textarea name="program_items[0][aktivitas]" class="form-control aktivitas-input" rows="1" required></textarea></td>
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
          <div class="row mb-3" id="psikologiFields" style="display:none;">
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
@push('page-script')
<script>
  // program master templates grouped by konsultan_id
  const programMastersByKonsultan = @json($programMasters ?? []);

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  let barisIdx = 1;

  function buildKodeSelectHtml(idx) {
    const konsultanId = document.getElementById('konsultan_id').value || '';
    const items = (programMastersByKonsultan[konsultanId] || []);
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

  document.getElementById('btnTambahBaris').addEventListener('click', function() {
    const tbody = document.querySelector('#programItemsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
    <td>${buildKodeSelectHtml(barisIdx)}<input type="hidden" name="program_items[${barisIdx}][program_konsultan_id]" class="program-konsultan-id"></td>
    <td><input type="text" name="program_items[${barisIdx}][nama_program]" class="form-control nama-input" required></td>
    <td><textarea name="program_items[${barisIdx}][tujuan]" class="form-control tujuan-input" rows="1" required></textarea></td>
    <td><textarea name="program_items[${barisIdx}][aktivitas]" class="form-control aktivitas-input" rows="1" required></textarea></td>
    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
  `;
    tbody.appendChild(tr);
    barisIdx++;
  });

  // delete row
  document.querySelector('#programItemsTable').addEventListener('click', function(e) {
    if (e.target.closest('.btn-hapus-baris')) {
      const tr = e.target.closest('tr');
      if (tr.parentNode.children.length > 1) tr.remove();
    }
  });

  // when a kode-select changes, autofill nama/tujuan/aktivitas if they are empty
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
      // Always set fields to selected template values (override)
      if (namaInput) namaInput.value = nama;
      if (tujuanInput) tujuanInput.value = tujuan;
      if (aktivitasInput) aktivitasInput.value = aktivitas;
      if (pkInput) pkInput.value = pid;
    }
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
    } else {
      psikologiFields.style.display = 'none';
    }
    // refresh kode options for all rows
    refreshKodeOptions();
  }
  document.getElementById('konsultan_id').addEventListener('change', toggleDaftarProgramAnak);

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
    anakSelect && anakSelect.addEventListener('change', fetchAndFillPsikologi);
    konsultanSelect && konsultanSelect.addEventListener('change', fetchAndFillPsikologi);
  });
</script>
@endpush
@endsection