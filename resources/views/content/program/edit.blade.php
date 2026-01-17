@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Observasi/Evaluasi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Observasi/Evaluasi</h5>
        <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        @if(!empty($sumber))
        <form action="{{ route('program.observasi-program.update.withsumber', [$sumber, $program->id]) }}" method="POST">
          @else
          <form action="{{ route('program.observasi-program.update', $program->id) }}" method="POST">
            @endif
            @csrf
            @method('PUT')

            <div class="row mb-3">
              <div class="col-12">
                <label class="form-label">Anak Didik <span class="text-danger">*</span></label>
                <select class="form-select" disabled>
                  @foreach($anakDidiks as $anak)
                  <option value="{{ $anak->id }}" {{ (old('anak_didik_id', $program->anak_didik_id) == $anak->id) ? 'selected' : '' }}>
                    {{ $anak->nama }}
                  </option>
                  @endforeach
                </select>
                <input type="hidden" name="anak_didik_id" value="{{ old('anak_didik_id', $program->anak_didik_id) }}">
              </div>
              {{-- Keep konsultan_id submitted but hide the field in edit view to avoid edits here --}}
              <input type="hidden" name="konsultan_id" value="{{ $program->konsultan_id ?? optional(\App\Models\Konsultan::where('user_id', auth()->id())->first())->id }}">
              @php
              // Determine selected konsultan specialization for initial toggle (if available)
              $selectedKonsultanId = old('konsultan_id', $program->konsultan_id ?? null);
              $selectedSpesialisasi = null;
              if ($selectedKonsultanId) {
              $k = \App\Models\Konsultan::find($selectedKonsultanId);
              if ($k && $k->spesialisasi) $selectedSpesialisasi = strtolower($k->spesialisasi);
              }
              // Also detect if this program record is a psikologi record
              $isPsikologiProgram = (isset($sumber) && $sumber === 'psikologi') || ($program instanceof \App\Models\ProgramPsikologi) || ($selectedSpesialisasi === 'psikologi');
              // Detect if the currently logged-in user is a konsultan psikologi
              $currentKons = optional(\App\Models\Konsultan::where('user_id', auth()->id())->first());
              $isCurrentUserPsikologi = $currentKons && isset($currentKons->spesialisasi) && strtolower($currentKons->spesialisasi) === 'psikologi';
              // Determine if this is SI or Wicara for rendering skala and default kemampuan
              $programSpesialisasi = strtolower(optional($program->konsultan)->spesialisasi ?? '');
              $isWicara = ($selectedSpesialisasi === 'wicara') || $programSpesialisasi === 'wicara' || (!empty($sumber) && $sumber === 'wicara') || ($program instanceof \App\Models\ProgramWicara);
              $isSI = ($selectedSpesialisasi === 'sensori integrasi') || $programSpesialisasi === 'sensori integrasi' || (!empty($sumber) && $sumber === 'si') || ($program instanceof \App\Models\ProgramSI);
              // Fallback: ensure we consider the konsultan referenced by the program record
              if (!$isWicara && !empty($program->konsultan_id)) {
              $progK = \App\Models\Konsultan::find($program->konsultan_id);
              if ($progK && isset($progK->spesialisasi) && strtolower($progK->spesialisasi) === 'wicara') {
              $isWicara = true;
              }
              }
              if (!$isSI && !empty($program->konsultan_id)) {
              $progK2 = \App\Models\Konsultan::find($program->konsultan_id);
              if ($progK2 && isset($progK2->spesialisasi) && strtolower($progK2->spesialisasi) === 'sensori integrasi') {
              $isSI = true;
              }
              }
              $skalaCount = $isSI ? 6 : 5;
              $skalaLabels = $isSI
              ? [1 => 'Tidak ada', 2 => 'Kurang sekali', 3 => 'Kurang', 4 => 'Cukup', 5 => 'Baik', 6 => 'Baik sekali']
              : [1 => 'Tidak Mampu', 2 => 'Kurang Mampu', 3 => 'Cukup Mampu', 4 => 'Mampu', 5 => 'Sangat Mampu'];
              @endphp
            </div>

            {{-- Removed program metadata fields (nama, kategori, deskripsi, target) for riwayat-edit view --}}

            {{-- Reuse create-style fields: psikologi blocks, diagnosa row, and penilaian kemampuan table ---}}
            <div class="row mb-3" id="psikologiFields" style="display:{{ !empty($isPsikologiProgram) ? '' : 'none' }};">
              <div class="col-md-12 mb-2">
                <label for="latar_belakang" class="form-label">Latar Belakang</label>
                <textarea name="latar_belakang" id="latar_belakang" class="form-control" rows="3">{{ old('latar_belakang', $program->latar_belakang) }}</textarea>
              </div>
              @push('page-script')
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  var preSpesialisasi = '{{ $selectedSpesialisasi ?? '
                  ' }}';
                  var isPsikologiProgram = {
                    {
                      !empty($isPsikologiProgram) ? 'true' : 'false'
                    }
                  };
                  var psikologiFields = document.getElementById('psikologiFields');
                  var wrapperPenilaian = document.getElementById('wrapper-penilaian-kemampuan');
                  var wrapperWawancara = document.getElementById('wrapper-wawancara');
                  var wrapperKeterangan = document.getElementById('wrapper-keterangan');
                  var wrapperKemampuanSaatIni = document.getElementById('wrapper-kemampuan-saat-ini');
                  var wrapperSaranRekomendasi = document.getElementById('wrapper-saran-rekomendasi');
                  var wrapperDiagnosa = document.getElementById('wrapper-diagnosa');

                  function toggleContainerDisabled(container, disabled) {
                    if (!container) return;
                    container.style.display = disabled ? 'none' : '';
                    var controls = container.querySelectorAll('input,textarea,select,button');
                    controls.forEach(function(c) {
                      if (c.classList && (c.classList.contains('btn-hapus-kemampuan') || c.id === 'btn-tambah-kemampuan' || c.classList.contains('btn-tambah-kemampuan'))) return;
                      c.disabled = !!disabled;
                    });
                  }

                  function applySpesialisasi(spes) {
                    if (!spes) return;
                    if (spes === 'psikologi') {
                      if (psikologiFields) psikologiFields.style.display = '';
                      toggleContainerDisabled(wrapperPenilaian, true);
                      toggleContainerDisabled(wrapperWawancara, true);
                      toggleContainerDisabled(wrapperKemampuanSaatIni, true);
                      toggleContainerDisabled(wrapperSaranRekomendasi, true);
                      if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, true);
                      if (wrapperKeterangan) toggleContainerDisabled(wrapperKeterangan, true);
                    } else {
                      if (psikologiFields) psikologiFields.style.display = 'none';
                      toggleContainerDisabled(wrapperPenilaian, false);
                      toggleContainerDisabled(wrapperWawancara, false);
                      toggleContainerDisabled(wrapperKemampuanSaatIni, false);
                      toggleContainerDisabled(wrapperSaranRekomendasi, false);
                      if (spes === 'wicara') {
                        if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, false);
                        if (wrapperKeterangan) toggleContainerDisabled(wrapperKeterangan, true);
                      } else {
                        if (wrapperDiagnosa) toggleContainerDisabled(wrapperDiagnosa, true);
                        if (wrapperKeterangan) toggleContainerDisabled(wrapperKeterangan, false);
                      }
                    }
                  }

                  if (preSpesialisasi) {
                    applySpesialisasi(preSpesialisasi);
                  } else if (isPsikologiProgram) {
                    applySpesialisasi('psikologi');
                  } else {
                    // No konsultan info — leave defaults (create-style JS may run later if present)
                  }
                });
              </script>
              @endpush
              <div class="col-md-12 mb-2">
                <label for="metode_assessment" class="form-label">Metode Assessment</label>
                <textarea name="metode_assessment" id="metode_assessment" class="form-control" rows="3">{{ old('metode_assessment', $program->metode_assessment) }}</textarea>
              </div>
              <div class="col-md-12 mb-2">
                <label for="hasil_assessment" class="form-label">Hasil Assessment</label>
                <textarea name="hasil_assessment" id="hasil_assessment" class="form-control" rows="3">{{ old('hasil_assessment', $program->hasil_assessment) }}</textarea>
              </div>
              <div class="col-md-12 mb-2">
                <label for="diagnosa_psikologi" class="form-label">Diagnosa</label>
                <textarea name="diagnosa_psikologi" id="diagnosa_psikologi" class="form-control" rows="3">{{ old('diagnosa_psikologi', $program->diagnosa_psikologi) }}</textarea>
              </div>
              <div class="col-md-12 mb-2">
                <label for="kesimpulan" class="form-label">Kesimpulan</label>
                <textarea name="kesimpulan" id="kesimpulan" class="form-control" rows="3">{{ old('kesimpulan', $program->kesimpulan) }}</textarea>
              </div>
            </div>

            <div class="row mb-3" id="row-diagnosa" style="display:{{ ($isWicara && empty($isPsikologiProgram)) ? '' : 'none' }};">
              <div class="col-md-12" id="wrapper-diagnosa">
                <label class="form-label">Diagnosa</label>
                <input type="text" name="diagnosa" id="input-diagnosa" class="form-control" placeholder="Masukkan diagnosa..." value="{{ old('diagnosa', $program->diagnosa) }}">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-md-12" id="wrapper-penilaian-kemampuan" style="display:{{ !empty($isPsikologiProgram) ? 'none' : '' }};">
                <label class="form-label">Penilaian Kemampuan Anak</label>
                <div class="table-responsive">
                  <style>
                    table.table thead th:first-child,
                    table.table tbody td:first-child {
                      min-width: 320px !important;
                    }

                    @media (max-width: 576px) {
                      table.table {
                        table-layout: auto !important;
                        width: 100% !important;
                        min-width: 0 !important;
                      }

                      table.table thead th:first-child,
                      table.table tbody td:first-child {
                        width: auto !important;
                        min-width: 320px !important;
                        white-space: normal !important;
                        vertical-align: middle;
                      }

                      table.table thead th:not(:first-child),
                      table.table tbody td:not(:first-child) {
                        width: auto !important;
                        white-space: nowrap !important;
                        padding: .35rem .5rem !important;
                        text-align: center;
                      }

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
                      <tr>
                        <th style="width:40%">Kemampuan</th>
                        <th colspan="{{ $skalaCount }}" class="text-center">Skala Penilaian</th>
                      </tr>
                      <tr>
                        <th></th>
                        @for($s = 1; $s <= $skalaCount; $s++)
                          <th class="text-center">{{ $s }}<br><small>{{ $skalaLabels[$s] }}</small></th>
                          @endfor
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
                      // prepare existing kemampuan from program
                      $existingKemampuan = is_array($program->kemampuan) ? $program->kemampuan : (is_string($program->kemampuan) ? json_decode($program->kemampuan, true) : null);
                      $kemampuanIndex = 0;
                      @endphp
                      @if($existingKemampuan && is_array($existingKemampuan) && count($existingKemampuan) > 0 && !$isSI)
                      @foreach($existingKemampuan as $i => $k)
                      <tr id="row-kemampuan-{{ $i }}">
                        <td>
                          <div class="input-group">
                            <input type="text" name="kemampuan[{{ $i }}][judul]" class="form-control" {{ empty($isPsikologiProgram) ? 'required' : '' }} value="{{ $k['judul'] ?? ($k['name'] ?? '') }}">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                          </div>
                        </td>
                        @for($skala=1;$skala<=$skalaCount;$skala++)
                          <td class="text-center"><input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" {{ (isset($k['skala']) && intval($k['skala'])== $skala) ? 'checked' : '' }} @if(!empty($isPsikologiProgram)) disabled @else required @endif></td>
                          @endfor
                      </tr>
                      @php $kemampuanIndex = $i + 1; @endphp
                      @endforeach
                      @elseif($isWicara)
                      @foreach($kemampuanWicara as $i => $judul)
                      <tr id="row-kemampuan-{{ $i }}">
                        <td>
                          <div class="input-group">
                            <input type="text" name="kemampuan[{{ $i }}][judul]" class="form-control" required value="{{ $judul }}">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                          </div>
                        </td>
                        @for($skala=1;$skala<=$skalaCount;$skala++)
                          <td class="text-center"><input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" required></td>
                          @endfor
                      </tr>
                      @endforeach
                      @php $kemampuanIndex = count($kemampuanWicara); @endphp
                      @elseif($isSI)
                      @php $printedSI = false; $lastSIIndex = -1; @endphp
                      @if($existingKemampuan && is_array($existingKemampuan) && count($existingKemampuan) > 0)
                      @foreach($existingKemampuan as $i => $k)
                      @if(isset($k['skala']) && (string)$k['skala'] !== '')
                      <tr id="row-kemampuan-{{ $i }}">
                        <td>
                          <div class="input-group">
                            <input type="text" name="kemampuan[{{ $i }}][judul]" class="form-control" required value="{{ $k['judul'] ?? ($k['name'] ?? '') }}">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                          </div>
                        </td>
                        @for($skala=1;$skala<=$skalaCount;$skala++)
                          <td class="text-center"><input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" {{ (isset($k['skala']) && intval($k['skala'])== $skala) ? 'checked' : '' }} required></td>
                          @endfor
                      </tr>
                      @php $printedSI = true; $lastSIIndex = $i; @endphp
                      @endif
                      @endforeach
                      @endif

                      @if(!$printedSI)
                      <tr id="row-kemampuan-0">
                        <td>
                          <div class="input-group">
                            <input type="text" name="kemampuan[0][judul]" class="form-control" {{ empty($isPsikologiProgram) ? 'required' : '' }} placeholder="Jenis kemampuan">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                          </div>
                        </td>
                        @for($skala=1;$skala<=$skalaCount;$skala++)
                          <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="{{ $skala }}" @if(!empty($isPsikologiProgram)) disabled @else required @endif></td>
                          @endfor
                      </tr>
                      @php $kemampuanIndex = 1; @endphp
                      @else
                      @php $kemampuanIndex = ($lastSIIndex >= 0 ? $lastSIIndex + 1 : 1); @endphp
                      @endif
                      @else
                      <tr id="row-kemampuan-0">
                        <td>
                          <div class="input-group">
                            <input type="text" name="kemampuan[0][judul]" class="form-control" {{ empty($isPsikologiProgram) ? 'required' : '' }} placeholder="Jenis kemampuan">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button>
                          </div>
                        </td>
                        @for($skala=1;$skala<=$skalaCount;$skala++)
                          <td class="text-center"><input type="radio" name="kemampuan[0][skala]" value="{{ $skala }}" @if(!empty($isPsikologiProgram)) disabled @else required @endif></td>
                          @endfor
                      </tr>
                      @php $kemampuanIndex = 1; @endphp
                      @endif
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

              @if($isSI)
              <div class="row mb-3" id="row-keterangan">
                <div class="col-md-12" id="wrapper-keterangan" style="display:{{ (!empty($isPsikologiProgram) || $isWicara) ? 'none' : '' }};">
                  <label class="form-label">Keterangan</label>
                  <textarea name="wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Keterangan tambahan...">{{ old('wawancara', $program->keterangan ?? $program->wawancara) }}</textarea>
                  @error('wawancara')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              @else
              <div class="row mb-3" id="row-keterangan">
                <div class="col-md-12" id="wrapper-keterangan" style="display:{{ (!empty($isPsikologiProgram) || $isWicara) ? 'none' : '' }};">
                  <label class="form-label">Keterangan</label>
                  <textarea name="wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Keterangan tambahan...">{{ old('wawancara', $program->keterangan ?? $program->wawancara) }}</textarea>
                  @error('wawancara')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3" id="row-wawancara">
                <div class="col-md-12" id="wrapper-wawancara" style="display:{{ !empty($isPsikologiProgram) ? 'none' : '' }};">
                  <label class="form-label" id="label-wawancara">Wawancara</label>
                  <textarea name="wawancara" id="input-wawancara" class="form-control @error('wawancara') is-invalid @enderror" rows="3" placeholder="Hasil wawancara dengan orang tua/anak/guru">{{ old('wawancara', $program->wawancara) }}</textarea>
                  @error('wawancara')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3" id="row-kemampuan-saat-ini">
                <div class="col-md-12" id="wrapper-kemampuan-saat-ini" style="display:{{ !empty($isPsikologiProgram) ? 'none' : '' }};">
                  <label class="form-label">Kemampuan Saat Ini</label>
                  <textarea name="kemampuan_saat_ini" class="form-control @error('kemampuan_saat_ini') is-invalid @enderror" rows="3" placeholder="Deskripsikan kemampuan anak saat ini">{{ old('kemampuan_saat_ini', $program->kemampuan_saat_ini) }}</textarea>
                  @error('kemampuan_saat_ini')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row mb-3" id="row-saran-rekomendasi">
                <div class="col-md-12" id="wrapper-saran-rekomendasi" style="display:{{ !empty($isPsikologiProgram) ? 'none' : '' }};">
                  <label class="form-label">Saran / Rekomendasi</label>
                  <textarea name="saran_rekomendasi" class="form-control @error('saran_rekomendasi') is-invalid @enderror" rows="3" placeholder="Saran atau rekomendasi untuk program berikutnya">{{ old('saran_rekomendasi', $program->saran_rekomendasi) }}</textarea>
                  @error('saran_rekomendasi')
                  <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              @endif

              {{-- Removed schedule and admin metadata from riwayat-edit view --}}

              <div class="row">
                <div class="col-12">
                  <button type="submit" class="btn btn-primary me-2">
                    <i class="ri-save-line me-2"></i>Perbarui
                  </button>
                  <a href="{{ route('program.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-close-line me-2"></i>Batal
                  </a>
                </div>
              </div>
          </form>
      </div>
    </div>
  </div>
</div>

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var maxSkala = {{ $isSI ? 6 : 5 }};
    // initialize kemampuanIndex from server-side computed value
    var kemampuanIndex = {{ $kemampuanIndex ?? 1 }};
    const tbody = document.querySelector('table tbody');

    if (tbody && !window._handlerKemampuanEditSudahDipasang) {
      tbody.addEventListener('click', function(e) {
        // Hapus baris kemampuan
        if (e.target.closest('.btn-hapus-kemampuan')) {
          const btn = e.target.closest('.btn-hapus-kemampuan');
          const tr = btn.closest('tr');
          if (tr && tr.id && tr.id.startsWith('row-kemampuan-')) {
            tr.remove();
          }
        }
        // Tambah baris kemampuan
        if (e.target.closest('#btn-tambah-kemampuan')) {
          const tr = document.createElement('tr');
          tr.id = `row-kemampuan-${kemampuanIndex}`;
          let html = `<td><div class="input-group"><input type="text" name="kemampuan[${kemampuanIndex}][judul]" class="form-control" required><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan"><i class="ri-delete-bin-line"></i></button></div></td>`;
          for (let skala = 1; skala <= maxSkala; skala++) {
            html += `<td class="text-center"><input type="radio" name="kemampuan[${kemampuanIndex}][skala]" value="${skala}" required></td>`;
          }
          tr.innerHTML = html;
          const placeholder = document.getElementById('row-tambah-kemampuan');
          if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(tr, placeholder);
          }
          kemampuanIndex++;
        }
      });
      window._handlerKemampuanEditSudahDipasang = true;
    }
  });
</script>
@endpush

@endsection