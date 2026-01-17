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
        <!-- Mobile: icon-only circular back button (transparent, matches anak-didik detail); Desktop: text back button -->
        <a href="{{ route('program.index') }}" class="btn btn-secondary d-none d-sm-inline-flex btn-sm align-items-center">
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
              @php
              $userKonsultanId = $currentKonsultanId ?? (auth()->check() && auth()->user()->role === 'konsultan' ? optional(\App\Models\Konsultan::where('user_id', auth()->id())->first())->id : null);
              @endphp
              <select name="konsultan_id" id="konsultan_id" class="form-select" required onchange="window.handleKonsultanChange()" @if(!empty($userKonsultanId)) disabled @endif>
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" data-spesialisasi="{{ strtolower($konsultan->spesialisasi) }}"
                  @if(old('konsultan_id')==$konsultan->id) selected
                  @elseif(!empty($currentKonsultanId) && $currentKonsultanId == $konsultan->id) selected
                  @elseif(empty($currentKonsultanId) && !empty($userKonsultanId) && $userKonsultanId == $konsultan->id) selected
                  @endif
                  >{{ $konsultan->nama }} ({{ $konsultan->spesialisasi }})</option>
                @endforeach
              </select>
              @if(!empty($userKonsultanId))
              <input type="hidden" name="konsultan_id" value="{{ $userKonsultanId }}">
              @endif
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
                  {{ $anak->nama }}
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
                <style>
                  /* Ensure Kemampuan column has a reasonable minimum width on all screens */
                  table.table thead th:first-child,
                  table.table tbody td:first-child {
                    min-width: 320px !important;
                  }

                  /* Mobile: let scale columns size to their content while keeping table scrollable */
                  @media (max-width: 576px) {

                    /* use auto table layout so columns fit content; allow horizontal scrolling via .table-responsive */
                    table.table {
                      table-layout: auto !important;
                      width: 100% !important;
                      min-width: 0 !important;
                    }

                    /* first column (Kemampuan) should be flexible and wrap text
                       — keep a reasonable minimum so it stays wide without shrinking scale cols */
                    table.table thead th:first-child,
                    table.table tbody td:first-child {
                      width: auto !important;
                      min-width: 320px !important;
                      white-space: normal !important;
                      vertical-align: middle;
                    }

                    /* allow scale columns to size to their content (text) and remain readable */
                    table.table thead th:not(:first-child),
                    table.table tbody td:not(:first-child) {
                      width: auto !important;
                      white-space: nowrap !important;
                      padding: .35rem .5rem !important;
                      text-align: center;
                    }

                    /* ensure input shrinks instead of forcing overflow */
                    table.table tbody td:first-child .input-group {
                      display: flex !important;
                    }

                    table.table tbody td:first-child .form-control {
                      min-width: 0 !important;
                      flex: 1 1 auto !important;
                    }

                    table.table tbody td:first-child .input-group .btn {
                      flex: 0 0 36px !important;
                    }
                  }
                </style>
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    @php
                    // Tentukan spesialisasi konsultan terlebih dahulu agar $isSI tersedia
                    $isWicara = false;
                    $isSI = false;
                    if (old('konsultan_id')) {
                    $konsultan = $konsultans->firstWhere('id', old('konsultan_id'));
                    $isWicara = $konsultan && strtolower($konsultan->spesialisasi) === 'wicara';
                    $isSI = $konsultan && strtolower($konsultan->spesialisasi) === 'sensori integrasi';
                    } elseif (!empty($userKonsultanId)) {
                    $konsultan = $konsultans->firstWhere('id', $userKonsultanId);
                    $isWicara = $konsultan && strtolower($konsultan->spesialisasi) === 'wicara';
                    $isSI = $konsultan && strtolower($konsultan->spesialisasi) === 'sensori integrasi';
                    }

                    // Untuk SI kita ingin urutan skala terbalik: mulai dari Baik sekali (5) -> ... -> Tidak ada (0)
                    if ($isSI) {
                    $skalaValues = [5,4,3,2,1,0];
                    $skalaLabels = [
                    5 => 'Baik sekali',
                    4 => 'Baik',
                    3 => 'Cukup',
                    2 => 'Kurang',
                    1 => 'Kurang sekali',
                    0 => 'Tidak ada'
                    ];
                    } else {
                    $skalaValues = [1,2,3,4,5];
                    $skalaLabels = [
                    1 => 'Tidak Mampu',
                    2 => 'Kurang Mampu',
                    3 => 'Cukup Mampu',
                    4 => 'Mampu',
                    5 => 'Sangat Mampu'
                    ];
                    }

                    $skalaCount = count($skalaValues);
                    @endphp
                    <tr>
                      <th style="width:40%">Kemampuan</th>
                      <th colspan="{{ $skalaCount }}" class="text-center">Skala Penilaian</th>
                    </tr>
                    <tr>
                      <th></th>
                      @foreach($skalaValues as $s)
                      <th class="text-center">{{ $s }}<br><small>{{ $skalaLabels[$s] }}</small></th>
                      @endforeach
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $kemampuanWicara = [
                    'Kontak mata','Atensi','Simbolik play','Pralinguistik 1','Pralingustik 2','Paham instruksi','Kata Benda','Kata kerja','Kata Sifat','Konsep waktu','Paham frasa','Paham kalimat','Paham kata tanya','Menamai tingkat kata','Menamai tingkat frasa','Menamai tingkat kalimat','Bercerita','Menjawab pertanyaan sederhana','Menyebutkan','Auditory','Visual','Taktil','Motorik kasar','Motorik halus','Motorik oral','Menggigit, mengunyah dan menelan','Komunikasi sosial','Pernafasan','Suara','Artikulasi','Kelancaran'
                    ];
                    $kemampuanSI = [
                    'Activity Level','Social Interaction','Frustration Tolerance','Attention','Postural Control','Muscle Tone & Joint Stability','Gravitational Security','Bilateral Motor Coordination','Oculomotor Control','Sensori Modulasi & Registrasi (Umum)','Sensori Modulasi & Registrasi Visual','Sensori Modulasi & Registrasi Auditory','Sensori Modulasi & Registrasi Tactile','Sensori Modulasi & Registrasi Proprioseptif','Sensori Modulasi & Registrasi Vestibular','Sensori Modulasi & Registrasi Body Awareness','Praxis (Umum)','Praxis Space Visualization','Praxis Design Copying','Praxis Postural Praxis','Praxis Sequencing Praxis','Praxis Oral Praxis','Auditory Praxis','Praxis Finger Identification','Praxis Localization of Tactile Stimuli'
                    ];
                    @endphp
                    @if($isWicara)
                    @foreach($kemampuanWicara as $i => $judul)
                    <tr id="row-kemampuan-{{ $i }}">
                      <td>
                        <div class="input-group">
                          <input type="text" name="kemampuan[{{ $i }}][judul]" class="form-control" required value="{{ $judul }}">
                          <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                        </div>
                      </td>
                      @foreach($skalaValues as $skala)
                      <td class="text-center"><input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" required></td>
                      @endforeach
                    </tr>
                    @endforeach
                    @php $kemampuanIndex = count($kemampuanWicara); @endphp
                    @elseif($isSI)
                    @foreach($kemampuanSI as $i => $judul)
                    <tr id="row-kemampuan-{{ $i }}">
                      <td>
                        <div class="input-group">
                          <input type="text" name="kemampuan[{{ $i }}][judul]" class="form-control" required value="{{ $judul }}">
                          <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                        </div>
                      </td>
                      @foreach($skalaValues as $skala)
                      <td class="text-center"><input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" required></td>
                      @endforeach
                    </tr>
                    @endforeach
                    @php $kemampuanIndex = count($kemampuanSI); @endphp
                    @else
                    <tr id="row-kemampuan-0">
                      <td>
                        <div class="input-group">
                          <input type="text" name="kemampuan[0][judul]" class="form-control" required placeholder="Jenis kemampuan">
                          <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                        </div>
                      </td>
                      @foreach($skalaValues as $skala)
                      <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="{{ $skala }}" required></td>
                      @endforeach
                    </tr>
                    @php $kemampuanIndex = 1; @endphp
                    @endif
                    <!-- Baris kemampuan tambahan dinamis -->
                    <tr id="row-tambah-kemampuan"></tr>
                    <tr>
                      <td colspan="{{ $skalaCount + 1 }}">
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-tambah-kemampuan">
                          <i class="ri-add-line"></i> Tambah Kemampuan Lainnya
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="row mt-3" id="row-keterangan">
              @if($isSI)
              <div class="col-md-12" id="wrapper-keterangan">
                <label class="form-label">Keterangan</label>
                <textarea name="wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Keterangan tambahan...">{{ old('wawancara') }}</textarea>
                @error('wawancara')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              @endif
            </div>
            <div class="row mb-3" id="row-wawancara">
              @if(!$isSI)
              <div class="col-md-12" id="wrapper-wawancara">
                <label class="form-label" id="label-wawancara">Wawancara</label>
                <textarea name="wawancara" id="input-wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Hasil wawancara dengan orang tua/anak/guru">{{ old('wawancara') }}</textarea>
                @error('wawancara')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              @endif
            </div>
            <div class="row mb-3" id="row-kemampuan-saat-ini">
              @if(!$isSI)
              <div class="col-md-12" id="wrapper-kemampuan-saat-ini">
                <label class="form-label">Kemampuan Saat Ini</label>
                <textarea name="kemampuan_saat_ini" class="form-control @error('kemampuan_saat_ini') is-invalid @enderror" rows="3" placeholder="Deskripsikan kemampuan anak saat ini">{{ old('kemampuan_saat_ini') }}</textarea>
                @error('kemampuan_saat_ini')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              @endif
            </div>
            <div class="row mb-3" id="row-saran-rekomendasi">
              @if(!$isSI)
              <div class="col-md-12" id="wrapper-saran-rekomendasi">
                <label class="form-label">Saran / Rekomendasi</label>
                <textarea name="saran_rekomendasi" class="form-control @error('saran_rekomendasi') is-invalid @enderror" rows="3" placeholder="Saran atau rekomendasi untuk program berikutnya">{{ old('saran_rekomendasi') }}</textarea>
                @error('saran_rekomendasi')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              @endif
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
    // Jika server mengirim currentKonsultanId atau view menentukan userKonsultanId, pastikan option terpilih
    var currentKonsultanId = '{{ $currentKonsultanId ?? ($userKonsultanId ?? "") }}';
    if (currentKonsultanId) {
      var sel = document.getElementById('konsultan_id');
      if (sel) {
        // set value (works even jika select disabled)
        sel.value = currentKonsultanId;
        // trigger handler to show/hide related fields
        if (typeof window.handleKonsultanChange === 'function') window.handleKonsultanChange();
      }
    }
    // skalaValues berisi daftar nilai skala dalam urutan yang ingin ditampilkan (mis. SI => [5,4,3,2,1,0])
    var skalaValues = {!! json_encode($skalaValues) !!};
    var maxSkala = skalaValues.length;
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
      // Render ulang skala agar urutan kolom sesuai spesialisasi yang dipilih
      renderSkalaFor(spesialisasi);
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
        if (c.classList && (c.classList.contains('btn-hapus-kemampuan') || c.id === 'btn-tambah-kemampuan' || c.classList.contains('btn-tambah-kemampuan'))) return;
        c.disabled = !!disabled;
      });
    }
    // Fungsi untuk merender ulang header dan kolom skala sesuai spesialisasi terpilih
    function renderSkalaFor(spesialisasi) {
      var isSiNow = (spesialisasi === 'sensori integrasi' || spesialisasi === 'si' || spesialisasi === 'sensori_integrasi');
      var targetSkalaValues = isSiNow ? [5, 4, 3, 2, 1, 0] : [1, 2, 3, 4, 5];
      // Update global skalaValues agar JS lain (tambah baris) mengikuti
      skalaValues = targetSkalaValues;
      var thead = document.querySelector('table.table thead');
      if (!thead) return;
      var firstRow = thead.querySelector('tr');
      var colspanTh = firstRow ? firstRow.querySelector('th[colspan]') : null;
      if (colspanTh) colspanTh.setAttribute('colspan', String(targetSkalaValues.length));
      var rows = thead.querySelectorAll('tr');
      if (rows.length >= 2) {
        var labelRow = rows[1];
        var newHtml = '';
        newHtml += '<th></th>';
        targetSkalaValues.forEach(function(s) {
          var label = isSiNow ? ({
            5: 'Baik sekali',
            4: 'Baik',
            3: 'Cukup',
            2: 'Kurang',
            1: 'Kurang sekali',
            0: 'Tidak ada'
          })[s] : ({
            1: 'Tidak Mampu',
            2: 'Kurang Mampu',
            3: 'Cukup Mampu',
            4: 'Mampu',
            5: 'Sangat Mampu'
          })[s];
          newHtml += `<th class="text-center">${s}<br><small>${label}</small></th>`;
        });
        labelRow.innerHTML = newHtml;
      }
      // Rebuild setiap baris kemampuan: pertahankan kolom pertama (judul), rebuild kolom skala
      var tbodyTable = document.querySelector('table.table tbody');
      if (!tbodyTable) return;
      var rowsBody = Array.from(tbodyTable.querySelectorAll('tr')).filter(r => r.id && r.id.startsWith('row-kemampuan-'));
      rowsBody.forEach(function(r) {
        var firstTd = r.querySelector('td');
        var newRow = document.createElement('tr');
        newRow.id = r.id;
        var html = '';
        if (firstTd) html += `<td>${firstTd.innerHTML}</td>`;
        // Build inputs with names preserving index part before [skala]
        var inputNameBase = null;
        var inp = r.querySelector('input[name$="[skala]"]');
        if (inp) {
          inputNameBase = inp.name.replace(/\[skala\]$/, '');
        } else {
          // fallback: try to derive from first input
          var anyInp = r.querySelector('input[name^="kemampuan"]');
          if (anyInp) inputNameBase = anyInp.name.replace(/\[judul\]$/, '');
        }
        targetSkalaValues.forEach(function(s) {
          var name = inputNameBase ? (inputNameBase + '[skala]') : 'kemampuan[][skala]';
          html += `<td class="text-center"><input type="radio" name="${name}" value="${s}" required></td>`;
        });
        r.parentNode.replaceChild(newRow, r);
        newRow.innerHTML = html;
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

    // Override tombol tambah kemampuan agar menggunakan `skalaValues` (menangani urutan SI terbalik)
    (function() {
      var btnTambah = document.getElementById('btn-tambah-kemampuan');
      if (btnTambah) {
        btnTambah.addEventListener('click', function(e) {
          // cegah bubbling ke tbody agar handler lama tidak membuat baris duplikat
          e.stopPropagation();
          const tr = document.createElement('tr');
          tr.id = `row-kemampuan-${kemampuanIndex}`;
          let html = `<td><div class="input-group"><input type="text" name="kemampuan[${kemampuanIndex}][judul]" class="form-control" required><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button></div></td>`;
          skalaValues.forEach(function(skala) {
            html += `<td class="text-center"><input type="radio" name="kemampuan[${kemampuanIndex}][skala]" value="${skala}" required></td>`;
          });
          tr.innerHTML = html;
          const placeholder = document.getElementById('row-tambah-kemampuan');
          if (placeholder && placeholder.parentNode) placeholder.parentNode.insertBefore(tr, placeholder);
          else if (tbody) tbody.appendChild(tr);
          kemampuanIndex++;
        });
      }
    })();

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