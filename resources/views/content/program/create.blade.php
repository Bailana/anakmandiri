@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Observasi/Evaluasi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Observasi/Evaluasi</h5>
        <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('program.store') }}" method="POST">
          <div class="row mb-3">
            <!-- Kolom khusus untuk konsultan psikologi, tampil bersama field lain jika dipilih -->
            <!-- Kolom khusus untuk konsultan psikologi, letakkan setelah dropdown anak didik -->
            <div class="col-md-12">
              <label class="form-label">Konsultan <span class="text-danger">*</span></label>
              <select name="konsultan_id" id="konsultan_id" class="form-select" required onchange="window.handleKonsultanChange()">
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" data-spesialisasi="{{ strtolower($konsultan->spesialisasi) }}">{{ $konsultan->nama }} ({{ $konsultan->spesialisasi }})</option>
                @endforeach
              </select>
            </div>
          </div>
          @csrf

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select name="anak_didik_id" class="form-select @error('anak_didik_id') is-invalid @enderror" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}" {{ old('anak_didik_id') == $anak->id ? 'selected' : '' }}>
                  {{ $anak->nama }} ({{ $anak->nis }})
                </option>
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

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
              <label for="diagnosa_psikologi" class="form-label">Diagnosa</label>
              <textarea name="diagnosa_psikologi" id="diagnosa_psikologi" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="kesimpulan" class="form-label">Kesimpulan</label>
              <textarea name="kesimpulan" id="kesimpulan" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12">
              <label for="rekomendasi" class="form-label">Rekomendasi</label>
              <textarea name="rekomendasi" id="rekomendasi" class="form-control" rows="3"></textarea>
            </div>
          </div>

          <div class="row mb-3" id="row-diagnosa" style="display:none;">
            <div class="col-md-12" id="wrapper-diagnosa">
              <label class="form-label">Diagnosa</label>
              <input type="text" name="diagnosa" id="input-diagnosa" class="form-control" placeholder="Masukkan diagnosa...">
            </div>

          </div>
          <div class="row mb-3">
            <div class="col-md-12" id="wrapper-penilaian-kemampuan">
              <label class="form-label">Penilaian Kemampuan Anak</label>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40%">Kemampuan</th>
                      <th colspan="5" class="text-center">Skala Penilaian</th>
                    </tr>
                    <tr>
                      <th></th>
                      <th class="text-center">1<br><small>Tidak Mampu</small></th>
                      <th class="text-center">2<br><small>Kurang Mampu</small></th>
                      <th class="text-center">3<br><small>Cukup Mampu</small></th>
                      <th class="text-center">4<br><small>Mampu</small></th>
                      <th class="text-center">5<br><small>Sangat Mampu</small></th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Satu baris kemampuan kosong default -->
                    <tr id="row-kemampuan-0">
                      <td>
                        <div class="input-group">
                          <input type="text" name="kemampuan[0][judul]" class="form-control" required placeholder="Jenis kemampuan">
                          <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                        </div>
                      </td>
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="1" required></td>
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="2" required></td>
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="3" required></td>
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="4" required></td>
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="5" required></td>
                    </tr>
                    <!-- Baris kemampuan tambahan dinamis -->
                    <tr id="row-tambah-kemampuan"></tr>
                    <tr>
                      <td colspan="6">
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-tambah-kemampuan">
                          <i class="ri-add-line"></i> Tambah Kemampuan Lainnya
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row mb-3" id="row-wawancara">
              <div class="col-md-12" id="wrapper-wawancara">
                <label class="form-label" id="label-wawancara">Wawancara</label>
                <textarea name="wawancara" id="input-wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Hasil wawancara dengan orang tua/anak/guru">{{ old('wawancara') }}</textarea>
                @error('wawancara')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="row mb-3" id="row-kemampuan-saat-ini">
              <div class="col-md-12" id="wrapper-kemampuan-saat-ini">
                <label class="form-label">Kemampuan Saat Ini</label>
                <textarea name="kemampuan_saat_ini" class="form-control @error('kemampuan_saat_ini') is-invalid @enderror" rows="3" placeholder="Deskripsikan kemampuan anak saat ini">{{ old('kemampuan_saat_ini') }}</textarea>
                @error('kemampuan_saat_ini')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="row mb-3" id="row-saran-rekomendasi">
              <div class="col-md-12" id="wrapper-saran-rekomendasi">
                <label class="form-label">Saran / Rekomendasi</label>
                <textarea name="saran_rekomendasi" class="form-control @error('saran_rekomendasi') is-invalid @enderror" rows="3" placeholder="Saran atau rekomendasi untuk program berikutnya">{{ old('saran_rekomendasi') }}</textarea>
                @error('saran_rekomendasi')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="ri-save-line me-2"></i>Simpan
                </button>
                <a href="{{ route('program.index') }}" class="btn btn-outline-danger">
                  <i class="ri-close-line me-2"></i>Batal
                </a>
              </div>
            </div>


        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // --- Penilaian Kemampuan Anak ---
    // Toggle tampilan field sesuai konsultan
    var psikologiFields = document.getElementById('psikologiFields');
    var wrapperPenilaian = document.getElementById('wrapper-penilaian-kemampuan');
    var wrapperWawancara = document.getElementById('wrapper-wawancara');
    var wrapperKemampuanSaatIni = document.getElementById('wrapper-kemampuan-saat-ini');
    var wrapperSaranRekomendasi = document.getElementById('wrapper-saran-rekomendasi');
    var wrapperDiagnosa = document.getElementById('wrapper-diagnosa');

    function toggleFieldsByKonsultan() {
      var select = document.getElementById('konsultan_id');
      var selected = select.options[select.selectedIndex];
      var spesialisasi = selected.getAttribute('data-spesialisasi');
      if (spesialisasi === 'psikologi') {
        psikologiFields.style.display = '';
        if (wrapperPenilaian) toggleContainerDisabled(wrapperPenilaian, true);
        if (wrapperWawancara) toggleContainerDisabled(wrapperWawancara, true);
        if (wrapperKemampuanSaatIni) toggleContainerDisabled(wrapperKemampuanSaatIni, true);
        if (wrapperSaranRekomendasi) toggleContainerDisabled(wrapperSaranRekomendasi, true);
        if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, true);
      } else {
        psikologiFields.style.display = 'none';
        if (wrapperPenilaian) toggleContainerDisabled(wrapperPenilaian, false);
        if (wrapperWawancara) toggleContainerDisabled(wrapperWawancara, false);
        if (wrapperKemampuanSaatIni) toggleContainerDisabled(wrapperKemampuanSaatIni, false);
        if (wrapperSaranRekomendasi) toggleContainerDisabled(wrapperSaranRekomendasi, false);
        if (spesialisasi === 'wicara') {
          if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, false);
        } else {
          if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, true);
        }
      }
    }

    function toggleContainerDisabled(container, disabled) {
      if (!container) return;
      // If hiding, set display:none; if showing, clear inline display
      container.style.display = disabled ? 'none' : '';
      // Find form controls inside container and toggle disabled attribute
      const controls = container.querySelectorAll('input,textarea,select,button');
      controls.forEach(c => {
        // don't disable buttons used to add/remove rows globally
        if (c.classList && c.classList.contains('btn-hapus-kemampuan')) return;
        if (disabled) {
          c.setAttribute('data-was-enabled', c.disabled ? '0' : '1');
          c.disabled = true;
        } else {
          // restore previous state if we recorded it
          if (c.getAttribute('data-was-enabled') === '1') {
            c.disabled = false;
          }
          c.removeAttribute('data-was-enabled');
        }
      });
    }
    document.getElementById('konsultan_id').addEventListener('change', toggleFieldsByKonsultan);
    toggleFieldsByKonsultan();
    // Set kemampuanIndex ke index terbesar yang ada + 1, hanya jalankan sekali
    let kemampuanIndex = 1;
    // Cari index terbesar dari baris kemampuan yang sudah ada
    const rowsAwal = Array.from(document.querySelectorAll('tr[id^="row-kemampuan-"]'));
    if (rowsAwal.length > 0) {
      const lastIdx = rowsAwal.map(row => parseInt(row.id.replace('row-kemampuan-', ''))).sort((a, b) => b - a)[0];
      kemampuanIndex = lastIdx + 1;
    }
    const tbody = document.querySelector('table tbody');

    // Proteksi agar event handler hanya dipasang satu kali
    if (!window._handlerKemampuanSudahDipasang) {
      tbody.addEventListener('click', function(e) {
        // Hapus baris kemampuan
        if (e.target.closest('.btn-hapus-kemampuan')) {
          const btn = e.target.closest('.btn-hapus-kemampuan');
          const tr = btn.closest('tr');
          if (tr && tr.id.startsWith('row-kemampuan-')) {
            tr.remove();
          }
        }
        // Tambah baris kemampuan
        if (e.target.closest('#btn-tambah-kemampuan')) {
          const tr = document.createElement('tr');
          tr.id = `row-kemampuan-${kemampuanIndex}`;
          let html = `<td><div class=\"input-group\"><input type=\"text\" name=\"kemampuan[${kemampuanIndex}][judul]\" class=\"form-control\" required><button type=\"button\" class=\"btn btn-outline-danger btn-sm btn-hapus-kemampuan\"><i class=\"ri-delete-bin-line\"></i></button></div></td>`;
          for (let skala = 1; skala <= 5; skala++) {
            html += `<td class=\"text-center\"><input type=\"radio\" name=\"kemampuan[${kemampuanIndex}][skala]\" value=\"${skala}\" required></td>`;
          }
          tr.innerHTML = html;
          tbody.insertBefore(tr, document.getElementById('row-tambah-kemampuan'));
          kemampuanIndex++;
        }
      });
      window._handlerKemampuanSudahDipasang = true;
    }

    // --- Konsultan Change Handler ---
    window.handleKonsultanChange = function() {
      var select = document.getElementById('konsultan_id');
      var selected = select.options[select.selectedIndex];
      var spesialisasi = selected.getAttribute('data-spesialisasi');
      var rowDiagnosa = document.getElementById('row-diagnosa');
      if (spesialisasi === 'wicara') {
        if (rowDiagnosa) rowDiagnosa.style.display = '';
      } else {
        if (rowDiagnosa) rowDiagnosa.style.display = 'none';
      }
    }
    window.handleKonsultanChange();
  });
</script>
@endpush