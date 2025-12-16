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

          <div class="row mb-3">
            <div class="col-md-12">
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
          </div>

          @push('scripts')
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              // --- Penilaian Kemampuan Anak ---
              let kemampuanIndex = {
                {
                  count($kemampuanList ?? ['Kontak mata', 'Atensi', 'Simbolik play'])
                }
              };
              const btnTambah = document.getElementById('btn-tambah-kemampuan');
              const tbody = document.querySelector('table tbody');

              // Event delegation untuk hapus baris kemampuan
              tbody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-hapus-kemampuan')) {
                  const btn = e.target.closest('.btn-hapus-kemampuan');
                  const tr = btn.closest('tr');
                  if (tr && tr.id.startsWith('row-kemampuan-')) {
                    tr.remove();
                  }
                }
              });

              if (btnTambah) {
                btnTambah.addEventListener('click', function() {
                  // Cari index terbesar yang masih ada
                  const rows = Array.from(document.querySelectorAll('tr[id^="row-kemampuan-"]'));
                  if (rows.length > 0) {
                    const lastIdx = rows.map(row => parseInt(row.id.replace('row-kemampuan-', ''))).sort((a, b) => b - a)[0];
                    kemampuanIndex = lastIdx + 1;
                  }
                  const tr = document.createElement('tr');
                  tr.id = `row-kemampuan-${kemampuanIndex}`;
                  let html = `<td><div class=\"input-group\"><input type=\"text\" name=\"kemampuan[${kemampuanIndex}][judul]\" class=\"form-control\" required><button type=\"button\" class=\"btn btn-outline-danger btn-sm btn-hapus-kemampuan\"><i class=\"ri-delete-bin-line\"></i></button></div></td>`;
                  for (let skala = 1; skala <= 5; skala++) {
                    html += `<td class=\"text-center\"><input type=\"radio\" name=\"kemampuan[${kemampuanIndex}][skala]\" value=\"${skala}\" required></td>`;
                  }
                  tr.innerHTML = html;
                  tbody.insertBefore(tr, document.getElementById('row-tambah-kemampuan'));
                  kemampuanIndex++;
                });
              }

              // --- Konsultan Change Handler ---
              window.handleKonsultanChange = function() {
                var select = document.getElementById('konsultan_id');
                var selected = select.options[select.selectedIndex];
                var spesialisasi = selected.getAttribute('data-spesialisasi');
                var wawancaraRow = document.getElementById('row-wawancara');
                var wawancaraLabel = document.getElementById('label-wawancara');
                var wawancaraInput = document.getElementById('input-wawancara');
                var rowKemampuanSaatIni = document.getElementById('row-kemampuan-saat-ini');
                var rowSaranRekomendasi = document.getElementById('row-saran-rekomendasi');
                if (spesialisasi === 'sensori integrasi') {
                  wawancaraLabel.textContent = 'Keterangan';
                  wawancaraInput.placeholder = 'Keterangan';
                  if (rowKemampuanSaatIni) rowKemampuanSaatIni.style.display = 'none';
                  if (rowSaranRekomendasi) rowSaranRekomendasi.style.display = 'none';
                } else {
                  wawancaraLabel.textContent = 'Wawancara';
                  wawancaraInput.placeholder = 'Hasil wawancara dengan orang tua/anak/guru';
                  if (rowKemampuanSaatIni) rowKemampuanSaatIni.style.display = '';
                  if (rowSaranRekomendasi) rowSaranRekomendasi.style.display = '';
                }
              }
              window.handleKonsultanChange();
            });
          </script>
          @endpush


          <div class="row mb-3" id="row-wawancara">
            <div class="col-md-12">
              <label class="form-label" id="label-wawancara">Wawancara</label>
              <textarea name="wawancara" id="input-wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Hasil wawancara dengan orang tua/anak/guru">{{ old('wawancara') }}</textarea>
              @error('wawancara')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row mb-3" id="row-kemampuan-saat-ini">
            <div class="col-md-12">
              <label class="form-label">Kemampuan Saat Ini</label>
              <textarea name="kemampuan_saat_ini" class="form-control @error('kemampuan_saat_ini') is-invalid @enderror" rows="3" placeholder="Deskripsikan kemampuan anak saat ini">{{ old('kemampuan_saat_ini') }}</textarea>
              @error('kemampuan_saat_ini')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row mb-3" id="row-saran-rekomendasi">
            <div class="col-md-12">
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