@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah PPI')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah PPI</h5>
        <a href="{{ route('ppi.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('ppi.store') }}">
          @csrf
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="anak_didik_id" class="form-label">Anak Didik</label>
              <select name="anak_didik_id" id="anak_didik_id" class="form-select" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }} ({{ $anak->nis ?? '-' }})</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="periode_mulai" class="form-label">Periode Mulai</label>
                  <input type="date" name="periode_mulai" id="periode_mulai" class="form-control" required value="{{ old('periode_mulai') }}">
                </div>
                <div class="col-md-6">
                  <label for="periode_selesai" class="form-label">Periode Selesai</label>
                  <input type="date" name="periode_selesai" id="periode_selesai" class="form-control" required value="{{ old('periode_selesai') }}">
                </div>
              </div>
              <label class="form-label">Program & Kategori</label>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width:67%">Nama Program</th>
                      <th style="width:25%">Kategori</th>
                      <th style="width:8%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="ppiProgramTbody">
                    <tr data-index="0">
                      <td>
                        <select name="program_items[0][nama_program]" class="form-select nama-program-select" required>
                          <option value="">Pilih Program</option>
                        </select>
                        <input type="hidden" name="program_items[0][program_konsultan_id]" class="program-konsultan-id">
                      </td>
                      <td>
                        <select name="program_items[0][kategori]" class="form-select kategori-select">
                          <option value="">Pilih Kategori</option>
                          <option value="Akademik">Akademik</option>
                          <option value="Bina Diri">Bina Diri</option>
                          <option value="Motorik">Motorik</option>
                          <option value="Perilaku">Perilaku</option>
                          <option value="Vokasi">Vokasi</option>
                        </select>
                      </td>
                      <td class="text-center" style="width:8%;white-space:nowrap;">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-ppi-baris p-1" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <button type="button" id="btnTambahPPIBaris" class="btn btn-outline-primary btn-sm mt-2"><i class="ri-add-line"></i> Tambah Baris</button>
            </div>
            <div class="row mb-3">
              <div class="col-md-12">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea name="keterangan" id="keterangan" class="form-control" rows="4"></textarea>
              </div>
            </div>

            <div class="d-flex justify-content-start gap-2">
              <button type="submit" class="btn btn-primary"><i class="ri-save-line me-2"></i>Simpan</button>
              <a href="{{ route('ppi.index') }}" class="btn btn-outline-danger"><i
                  class="ri-close-line me-2"></i>Batal</a>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const konsultanSpecs = @json((($konsultans?? collect())->mapWithKeys(function($k) {
      return [$k->id => strtolower($k->spesialisasi?? '')];
    })->toArray()));
    const pendidikanCache = {};

    function addOptionToSelect(sel, value, label, dataAttrs = {}) {
      const opt = document.createElement('option');
      opt.value = value || '';
      opt.textContent = label || value || '';
      Object.keys(dataAttrs).forEach(k => {
        if (dataAttrs[k] !== undefined && dataAttrs[k] !== null) opt.setAttribute('data-' + k, dataAttrs[k]);
      });
      sel.appendChild(opt);
    }

    function populateSelectsFromArray(optionsArray) {
      const selects = Array.from(document.querySelectorAll('.nama-program-select'));
      selects.forEach(s => {
        // preserve current value and hidden id for this row
        const tr = s.closest('tr');
        const hidden = tr ? tr.querySelector('.program-konsultan-id') : null;
        const prevValue = s.value || '';
        const prevHidden = hidden ? hidden.value : '';

        s.innerHTML = '<option value="">Pilih Program</option>';
        optionsArray.forEach(opt => addOptionToSelect(s, opt.value, opt.label, opt.data));

        // try restore by value first
        if (prevValue) {
          s.value = prevValue;
        }

        // if value not restored but hidden id exists, try select by data-id
        if ((!s.value || s.value === '') && prevHidden) {
          for (let i = 0; i < s.options.length; i++) {
            const o = s.options[i];
            if (o.getAttribute('data-id') === prevHidden) {
              s.selectedIndex = i;
              break;
            }
          }
        }

        // ensure hidden id matches selected option
        if (hidden) {
          const opt = s.options[s.selectedIndex];
          hidden.value = opt ? (opt.getAttribute('data-id') || '') : '';
        }
      });
    }

    async function refreshPPIProgramOptions() {
      const selects = Array.from(document.querySelectorAll('.nama-program-select'));
      if (!selects.length) return;
      const anakId = document.getElementById('anak_didik_id').value || '';
      if (!anakId) {
        selects.forEach(s => s.innerHTML = '<option value="">Pilih Program</option>');
        return;
      }

      const optionsMap = new Map();
      try {
        const r = await fetch('/program-anak/' + encodeURIComponent(anakId) + '/all-json');
        const j = await r.json();
        const programs = j && j.programs ? j.programs : [];
        const allowed = ['wicara', 'sensori integrasi', 'psikologi'];
        programs.forEach(p => {
          const konsId = (p.konsultan && p.konsultan.id) || p.konsultan_id || p.program_konsultan_id || null;
          const spec = konsId ? (konsultanSpecs[konsId] || '') : '';
          if (spec && allowed.includes(spec)) {
            const label = (p.kode_program ? (p.kode_program + ' - ') : '') + (p.nama_program || p.nama || '');
            if (!optionsMap.has(label)) {
              // Only assign a program_konsultan id when it's explicitly provided by the API
              const pkId = p.program_konsultan_id ? p.program_konsultan_id : '';
              optionsMap.set(label, {
                value: p.nama_program || p.nama || '',
                label: label,
                data: {
                  id: pkId,
                  kode: p.kode_program || '',
                  tujuan: p.tujuan || '',
                  aktivitas: p.aktivitas || ''
                }
              });
            }
          }
        });
      } catch (err) {
        console.error('Failed fetching child programs', err);
      }

      // For 'pendidikan' konsultan, only include programs that were actually assigned to this anak
      const pendidikanIds = Object.keys(konsultanSpecs).filter(id => konsultanSpecs[id] === 'pendidikan');
      for (const id of pendidikanIds) {
        try {
          // cache key should include anakId since results vary by anak
          const cacheKey = `${anakId}::${id}`;
          let items = pendidikanCache[cacheKey];
          if (!items) {
            // fetch programs for this anak filtered by konsultan
            const r2 = await fetch('/program-anak/riwayat-program/' + encodeURIComponent(anakId) + '/konsultan/' + encodeURIComponent(id));
            const j2 = await r2.json();
            items = j2 && j2.programs ? j2.programs : [];
            pendidikanCache[cacheKey] = items;
          }
          items.forEach(it => {
            const label = (it.kode_program ? (it.kode_program + ' - ') : '') + (it.nama_program || it.nama || '');
            if (!optionsMap.has(label)) {
              optionsMap.set(label, {
                value: it.nama_program || it.nama || '',
                label: label,
                data: {
                  id: it.id || '',
                  kode: it.kode_program || '',
                  tujuan: it.tujuan || '',
                  aktivitas: it.aktivitas || ''
                }
              });
            }
          });
        } catch (err) {
          console.error('Failed fetching pendidikan programs for konsultan ' + id, err);
        }
      }

      const optionEntries = Array.from(optionsMap.values());
      populateSelectsFromArray(optionEntries);
    }

    let ppiRowIdx = document.querySelectorAll('#ppiProgramTbody tr').length || 1;

    function addPpiRow() {
      const tbody = document.getElementById('ppiProgramTbody');
      const tr = document.createElement('tr');
      tr.setAttribute('data-index', String(ppiRowIdx));
      tr.innerHTML = `
      <td>
        <select name="program_items[${ppiRowIdx}][nama_program]" class="form-select nama-program-select" required>
          <option value="">Pilih Program</option>
        </select>
        <input type="hidden" name="program_items[${ppiRowIdx}][program_konsultan_id]" class="program-konsultan-id">
      </td>
      <td>
        <select name="program_items[${ppiRowIdx}][kategori]" class="form-select kategori-select">
          <option value="">Pilih Kategori</option>
          <option value="Akademik">Akademik</option>
          <option value="Bina Diri">Bina Diri</option>
          <option value="Motorik">Motorik</option>
          <option value="Perilaku">Perilaku</option>
          <option value="Vokasi">Vokasi</option>
        </select>
      </td>
      <td class="text-center" style="width:8%;white-space:nowrap;">
        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-ppi-baris p-1" title="Hapus"><i class="ri-delete-bin-line"></i></button>
      </td>
    `;
      tbody.appendChild(tr);
      ppiRowIdx++;
      refreshPPIProgramOptions();
    }

    document.addEventListener('click', function(e) {
      const btn = e.target && e.target.closest ? e.target.closest('.btn-hapus-ppi-baris') : null;
      if (btn) {
        const tr = btn.closest('tr');
        if (tr) tr.remove();
      }
    });

    document.getElementById('ppiProgramTbody').addEventListener('change', function(e) {
      let sel = e.target;
      if (!sel.classList || !sel.classList.contains('nama-program-select')) {
        sel = e.target.closest ? e.target.closest('.nama-program-select') : null;
      }
      if (sel) {
        const tr = sel.closest('tr');
        const hidden = tr ? tr.querySelector('.program-konsultan-id') : null;
        const opt = sel.options[sel.selectedIndex];
        const id = opt ? (opt.getAttribute('data-id') || '') : '';
        if (hidden) hidden.value = id;
      }
    });

    const addBtn = document.getElementById('btnTambahPPIBaris');
    if (addBtn) addBtn.addEventListener('click', addPpiRow);
    const anak = document.getElementById('anak_didik_id');
    if (anak) anak.addEventListener('change', refreshPPIProgramOptions);
    refreshPPIProgramOptions();
  });
</script>
@endpush


@endsection