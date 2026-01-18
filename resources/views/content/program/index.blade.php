@extends('layouts/contentNavbarLayout')

@section('title', 'Observasi/Evaluasi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Observasi/Evaluasi</h4>
            <p class="text-body-secondary mb-0">Kelola observasi/evaluasi anak didik</p>
          </div>
          @if(auth()->check() && auth()->user()->role === 'konsultan')
          <a href="{{ route('program.create') }}"
            class="btn btn-primary d-flex align-items-center justify-content-center p-0 d-inline-flex d-sm-none"
            style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('program.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
            <i class="ri-add-line me-2"></i>Tambah Observasi/Evaluasi
          </a>
          @endif
        </div>
      </div>
    </div>
    <style>
      /* Riwayat modal: list-group item layout to match program-anak view */
      #riwayatObservasiModal .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .5rem;
      }

      #riwayatObservasiModal .list-group-item .btn {
        line-height: 1;
        padding: .35rem .45rem;
      }

      #riwayatObservasiModal .fw-bold.bg-light {
        font-size: .95rem;
      }
    </style>
  </div>
</div>

<!-- Alert Messages -->
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ri-checkbox-circle-line me-2"></i>{{ $message }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Search & Filter -->
<div class="row mb-4">
  <div class="col-12">
    <form method="GET" action="{{ route('program.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari program atau nama anak..."
          value="{{ request('search') }}">
      </div>
      <select name="guru_fokus" class="form-select" style="max-width: 200px;">
        <option value="">Guru Fokus</option>
        @foreach($guruOptions as $id => $name)
        <option value="{{ $id }}" {{ request('guru_fokus') == $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-outline-primary" title="Filter">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('program.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<!-- Table -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="programTable">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Jenis Kelamin</th>
              <th>No. Telp Orang Tua</th>
              <th>Guru Fokus</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programs as $program)
            <tr id="row-{{ $program->sumber }}-{{ $program->id }}">
              <td class="no-col">{{ ($programs->firstItem() ?? 0) + $loop->index }}</td>
              <td>
                <p class="text-heading mb-0 fw-medium">{{ $program->anakDidik->nama ?? '-' }}</p>
              </td>
              <td>
                @php
                $jk = $program->anakDidik->jenis_kelamin ?? null;
                @endphp
                @if($jk)
                <span class="badge bg-label-{{ $jk === 'laki-laki' ? 'info' : 'warning' }}">
                  {{ ucfirst($jk) }}
                </span>
                @else
                -
                @endif
              </td>
              <td>{{ $program->anakDidik->no_telepon_orang_tua ?? '-' }}</td>
              <td>
                @if($program->anakDidik && $program->anakDidik->guruFokus)
                <span class="badge bg-label-primary"
                  style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;vertical-align:middle;"
                  title="{{ $program->anakDidik->guruFokus->nama }}">{{ $program->anakDidik->guruFokus->nama }}</span>
                @else
                -
                @endif
              </td>
              <td>
                <!-- Tombol Riwayat (desktop) - kecil ikon seperti di program-anak -->
                <div class="d-none d-md-flex gap-2 align-items-center">
                  <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-view-riwayat"
                    data-program-id="{{ $program->id }}" data-anak-didik-id="{{ $program->anak_didik_id ?? ($program->anakDidik->id ?? '') }}"
                    title="Riwayat" onclick="loadRiwayatObservasi(this)">
                    <i class="ri-history-line"></i>
                  </button>
                </div>
                <!-- Tombol titik tiga untuk mobile (menu hanya berisi Riwayat) -->
                <div class="dropdown d-md-none">
                  <button class="btn btn-sm p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false" style="box-shadow:none;">
                    <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#" data-program-id="{{ $program->id }}" data-anak-didik-id="{{ $program->anak_didik_id ?? ($program->anakDidik->id ?? '') }}"
                        onclick="loadRiwayatObservasi(this);return false;"><i class="ri-history-line me-1"></i> Riwayat</a>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7">
                <div class="alert alert-warning mb-0" role="alert">
                  <i class="ri-alert-line me-2"></i>
                  Tidak ada data observasi yang dilakukan.
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
        <style>
          @media (max-width: 767.98px) {
            .table .d-md-flex {
              display: none !important;
            }

            .table .d-md-none {
              display: block !important;
            }
          }

          @media (min-width: 768px) {
            .table .d-md-flex {
              display: flex !important;
            }

            .table .d-md-none {
              display: none !important;
            }
          }
        </style>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center pagination-footer-fix">
        <style>
          .pagination-footer-fix {
            flex-wrap: nowrap !important;
            gap: 0.5rem;
          }

          .pagination-footer-fix>div,
          .pagination-footer-fix>nav {
            min-width: 0;
            max-width: 100%;
          }

          .pagination-footer-fix nav {
            flex-shrink: 1;
            flex-grow: 0;
          }

          @media (max-width: 767.98px) {
            .pagination-footer-fix {
              flex-direction: row !important;
              align-items: center !important;
              flex-wrap: nowrap !important;
            }

            .pagination-footer-fix>div,
            .pagination-footer-fix>nav {
              width: auto !important;
              max-width: 100%;
            }

            .pagination-footer-fix nav ul.pagination {
              flex-wrap: nowrap !important;
            }
          }
        </style>
        <div class="text-body-secondary">
          Menampilkan {{ $programs->firstItem() ?? 0 }} hingga {{ $programs->lastItem() ?? 0 }} dari
          {{ $programs->total() }} data
        </div>
        <nav>
          {{ $programs->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>

  <!-- Modal Riwayat Observasi/Evaluasi -->
  <div class="modal fade" id="riwayatObservasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Riwayat Observasi/Evaluasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="riwayatObservasiList">
            <!-- Daftar tanggal observasi akan dimuat di sini -->
            <div class="text-center text-muted">Memuat data...</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Detail -->
  <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Observasi/Evaluasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div class="row mb-3">
            <div class="col-md-4">
              <p class="text-body-secondary text-sm mb-1">Anak Didik</p>
              <p class="fw-medium" id="detailAnakDidik"></p>
            </div>
            <div class="col-md-4">
              <p class="text-body-secondary text-sm mb-1">Guru Fokus</p>
              <span id="detailGuruFokus"
                style="max-width: 180px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom;"
                title=""></span>
            </div>
            <div class="col-md-4">
              <p class="text-body-secondary text-sm mb-1">Konsultan</p>
              <p class="fw-medium" id="detailKonsultan"></p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Penilaian Kemampuan</p>
              <div id="detailKemampuan"></div>
            </div>
          </div>
          <!-- Psikologi specific fields -->
          <div id="psikologiFieldsDetail" style="display:none">
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Latar Belakang</p>
                <p class="fw-medium" id="detailLatarBelakang"></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Metode Assessment</p>
                <p class="fw-medium" id="detailMetodeAssessment"></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Hasil Assessment</p>
                <p class="fw-medium" id="detailHasilAssessment"></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Kesimpulan</p>
                <p class="fw-medium" id="detailKesimpulan"></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Diagnosa</p>
                <p class="fw-medium" id="detailDiagnosaPsiko"></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-12">
                <p class="text-body-secondary text-sm mb-1">Rekomendasi</p>
                <p class="fw-medium" id="detailRekomendasi"></p>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Wawancara</p>
              <p class="fw-medium" id="detailWawancara"></p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Kemampuan Saat Ini</p>
              <p class="fw-medium" id="detailKemampuanSaatIni"></p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Diagnosa</p>
              <p class="fw-medium" id="detailDiagnosa"></p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Saran / Rekomendasi</p>
              <p class="fw-medium" id="detailSaranRekomendasi"></p>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12 text-end">
              <a id="exportPdfBtn" href="#" class="btn btn-danger" target="_blank">
                <i class="ri-file-pdf-line me-2"></i>Export PDF
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Inject current user id from backend (must be outside function for valid JS)
    var currentUserId = @json(Auth::id());
    // Fungsi untuk menampilkan detail observasi secara aman
    window.showDetailObservasi = function(sumber, id) {
      // Pastikan modal detail ada di DOM
      var detailModalEl = document.getElementById('detailModal');
      if (!detailModalEl) return;
      // If sumber provided, call source-aware endpoint
      var url = sumber ? '/program/observasi-program/' + sumber + '/' + id : '/program/observasi-program/' + id;
      fetch(url)
        .then(response => response.json())
        .then(res => {
          if (res.success && res.data) {
            const program = res.data;
            // Gunakan field sumber dari API
            const sumber = program.sumber;
            var el;
            el = document.getElementById('detailAnakDidik');
            if (el && program && document.body.contains(el)) {
              el.textContent = program.anak_didik && program.anak_didik.nama ? program.anak_didik.nama : (program
                .anak_didik_nama || '-');
            }
            el = document.getElementById('detailGuruFokus');
            if (el && program && document.body.contains(el)) {
              let guruFokusHtml = '-';
              if (program.anak_didik && (program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus &&
                  program.anak_didik.guruFokus.nama))) {
                guruFokusHtml = program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus ? program
                  .anak_didik.guruFokus.nama : '-');
              } else if (program.guru_fokus_nama) {
                guruFokusHtml = program.guru_fokus_nama;
              }
              el.textContent = guruFokusHtml;
              el.title = guruFokusHtml;
            }
            el = document.getElementById('detailKonsultan');
            if (el && program && document.body.contains(el)) el.textContent = program.konsultan_nama || '-';
            el = document.getElementById('detailKemampuan');
            if (el && program && document.body.contains(el)) {
              let kemampuanHtml = '';
              if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
                const isSI = (program.sumber === 'si' || (program.konsultan_spesialisasi && typeof program.konsultan_spesialisasi === 'string' && program.konsultan_spesialisasi.toLowerCase() === 'sensori integrasi'));
                if (isSI) {
                  const siSkalaValues = [5, 4, 3, 2, 1, 0];
                  const siSkalaLabels = {
                    5: 'Baik sekali',
                    4: 'Baik',
                    3: 'Cukup',
                    2: 'Kurang',
                    1: 'Kurang sekali',
                    0: 'Tidak ada'
                  };
                  kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">KEMAMPUAN</th>';
                  siSkalaValues.forEach(function(sv) {
                    kemampuanHtml += `<th class="text-center">${sv}<br><small>${siSkalaLabels[sv]}</small></th>`;
                  });
                  kemampuanHtml += '</tr></thead><tbody>';
                  program.kemampuan.forEach((item, idx) => {
                    let skalaInt = (typeof item.skala === 'string' || typeof item.skala === 'number') ? parseInt(item.skala) : null;
                    kemampuanHtml += `<tr><td>${item.judul}</td>`;
                    siSkalaValues.forEach(function(sv) {
                      kemampuanHtml += `<td class="text-center">${skalaInt === sv ? '<i class="ri-check-line text-success"></i>' : ''}</td>`;
                    });
                    kemampuanHtml += '</tr>';
                  });
                  kemampuanHtml += '</tbody></table></div>';
                } else {
                  const skalaValues = [1, 2, 3, 4, 5];
                  kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">KEMAMPUAN</th>';
                  skalaValues.forEach(function(sv) {
                    kemampuanHtml += `<th class="text-center">${sv}</th>`;
                  });
                  kemampuanHtml += '</tr></thead><tbody>';
                  program.kemampuan.forEach((item, idx) => {
                    let skalaInt = (typeof item.skala === 'string' || typeof item.skala === 'number') ? parseInt(item.skala) : null;
                    kemampuanHtml += `<tr><td>${item.judul}</td>`;
                    skalaValues.forEach(function(sv) {
                      kemampuanHtml += `<td class="text-center">${skalaInt === sv ? '<i class="ri-check-line text-success"></i>' : ''}</td>`;
                    });
                    kemampuanHtml += '</tr>';
                  });
                  kemampuanHtml += '</tbody></table></div>';
                }
              } else {
                kemampuanHtml = '<em>Tidak ada data kemampuan</em>';
              }
              el.innerHTML = kemampuanHtml;
            }
            el = document.getElementById('detailWawancara');
            if (el && program && document.body.contains(el)) el.textContent = program.wawancara || '-';
            // Psikologi specific: populate and toggle visibility
            var psikologiBlock = document.getElementById('psikologiFieldsDetail');
            if (sumber === 'psikologi') {
              if (psikologiBlock) psikologiBlock.style.display = '';
              // hide non-relevant blocks
              var detailKemampuanBlock = document.getElementById('detailKemampuan') ? document.getElementById(
                'detailKemampuan').parentElement : null;
              if (detailKemampuanBlock) detailKemampuanBlock.style.display = 'none';
              var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById(
                'detailWawancara').parentElement : null;
              if (wawancaraBlock) wawancaraBlock.style.display = 'none';
              var kemampuanSaatIniBlock = document.getElementById('detailKemampuanSaatIni') ? document
                .getElementById('detailKemampuanSaatIni').parentElement : null;
              if (kemampuanSaatIniBlock) kemampuanSaatIniBlock.style.display = 'none';
              var saranBlock = document.getElementById('detailSaranRekomendasi') ? document.getElementById(
                'detailSaranRekomendasi').parentElement : null;
              if (saranBlock) saranBlock.style.display = 'none';
              // hide general diagnosa block and use psikologi-specific diagnosa placed above Kesimpulan
              var generalDiagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById(
                'detailDiagnosa').parentElement : null;
              if (generalDiagnosaBlock) generalDiagnosaBlock.style.display = 'none';
              // also ensure the general rekomendasi block is hidden (if present)
              var saranBlockGen = document.getElementById('detailSaranRekomendasi') ? document.getElementById(
                'detailSaranRekomendasi').parentElement : null;
              if (saranBlockGen) saranBlockGen.style.display = 'none';
              // populate psikologi fields
              var elLB = document.getElementById('detailLatarBelakang');
              if (elLB) elLB.textContent = program.latar_belakang || '-';
              var elMA = document.getElementById('detailMetodeAssessment');
              if (elMA) elMA.textContent = program.metode_assessment || '-';
              var elHA = document.getElementById('detailHasilAssessment');
              if (elHA) elHA.textContent = program.hasil_assessment || '-';
              var elKS = document.getElementById('detailKesimpulan');
              if (elKS) elKS.textContent = program.kesimpulan || '-';
              // hide psikologi-specific rekomendasi field (not shown for psikologi)
              var elRK = document.getElementById('detailRekomendasi');
              if (elRK && elRK.parentElement) elRK.parentElement.style.display = 'none';
              var elDiagP = document.getElementById('detailDiagnosaPsiko');
              if (elDiagP) elDiagP.textContent = program.diagnosa || '-';
            } else {
              if (psikologiBlock) psikologiBlock.style.display = 'none';
              // restore other blocks
              var detailKemampuanBlock = document.getElementById('detailKemampuan') ? document.getElementById(
                'detailKemampuan').parentElement : null;
              if (detailKemampuanBlock) detailKemampuanBlock.style.display = '';
              var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById(
                'detailWawancara').parentElement : null;
              if (wawancaraBlock) wawancaraBlock.style.display = '';
              var kemampuanSaatIniBlock = document.getElementById('detailKemampuanSaatIni') ? document
                .getElementById('detailKemampuanSaatIni').parentElement : null;
              if (kemampuanSaatIniBlock) kemampuanSaatIniBlock.style.display = '';
              var saranBlock = document.getElementById('detailSaranRekomendasi') ? document.getElementById(
                'detailSaranRekomendasi').parentElement : null;
              if (saranBlock) saranBlock.style.display = '';
              // restore general diagnosa block and clear psikologi-specific diagnosa
              var generalDiagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById(
                'detailDiagnosa').parentElement : null;
              if (generalDiagnosaBlock) generalDiagnosaBlock.style.display = '';
              var elDiagP = document.getElementById('detailDiagnosaPsiko');
              if (elDiagP) elDiagP.textContent = '';
              // restore general rekomendasi block if present
              var saranBlockGen = document.getElementById('detailSaranRekomendasi') ? document.getElementById(
                'detailSaranRekomendasi').parentElement : null;
              if (saranBlockGen) saranBlockGen.style.display = '';
            }
            // --- SENSORY INTEGRASI ONLY: Hide wawancara & diagnosa, show keterangan ---
            if (program.sumber === 'si' || (program.konsultan_spesialisasi && program.konsultan_spesialisasi
                .toLowerCase() === 'sensori integrasi')) {
              // Hide wawancara
              var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById(
                'detailWawancara').parentElement : null;
              if (wawancaraBlock) wawancaraBlock.style.display = 'none';
              // Hide diagnosa
              var diagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById(
                'detailDiagnosa').parentElement : null;
              if (diagnosaBlock) diagnosaBlock.style.display = 'none';
              // Show only keterangan (inject if needed)
              let keteranganBlock = document.getElementById('detailKeterangan');
              if (!keteranganBlock) {
                // Insert after kemampuan table
                var kemampuanBlock = document.getElementById('detailKemampuan') ? document.getElementById(
                  'detailKemampuan').parentElement : null;
                if (kemampuanBlock) {
                  var row = document.createElement('div');
                  row.className = 'row mb-3';
                  row.innerHTML =
                    `<div class=\"col-12\"><p class=\"text-body-secondary text-sm mb-1\">Keterangan</p><p class=\"fw-medium\" id=\"detailKeterangan\"></p></div>`;
                  kemampuanBlock.parentNode.insertBefore(row, kemampuanBlock.nextSibling);
                  keteranganBlock = document.getElementById('detailKeterangan');
                }
              }
              if (keteranganBlock) keteranganBlock.textContent = program.wawancara || '-';
            } else {
              // Restore wawancara & diagnosa, but skip restoring when this is a psikologi detail view
              if (sumber !== 'psikologi') {
                var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById(
                  'detailWawancara').parentElement : null;
                if (wawancaraBlock) wawancaraBlock.style.display = '';
                var diagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById(
                  'detailDiagnosa').parentElement : null;
                if (diagnosaBlock) diagnosaBlock.style.display = '';
                // Remove keterangan block if present
                let keteranganBlock = document.getElementById('detailKeterangan');
                if (keteranganBlock && keteranganBlock.parentElement) keteranganBlock.parentElement.parentElement
                  .removeChild(keteranganBlock.parentElement);
              }
            }
            // Untuk SI dan Psikologi, sembunyikan Kemampuan Saat Ini & Saran Rekomendasi
            el = document.getElementById('detailKemampuanSaatIni');
            if (el && program && document.body.contains(el)) {
              if (sumber === 'si' || sumber === 'psikologi') {
                el.parentElement.style.display = 'none';
              } else {
                el.parentElement.style.display = '';
                el.textContent = program.kemampuan_saat_ini || '-';
              }
            }
            el = document.getElementById('detailSaranRekomendasi');
            if (el && program && document.body.contains(el)) {
              if (sumber === 'si' || sumber === 'psikologi') {
                el.parentElement.style.display = 'none';
              } else {
                el.parentElement.style.display = '';
                el.textContent = program.saran_rekomendasi || '-';
              }
            }
            // Diagnosa (only set general diagnosa when not psikologi)
            el = document.getElementById('detailDiagnosa');
            if (el && program && document.body.contains(el)) {
              if (sumber !== 'psikologi') {
                el.textContent = program.diagnosa || '-';
              } else {
                el.textContent = '';
              }
            }
            // Tampilkan modal detail
            var detailModal = new bootstrap.Modal(detailModalEl);
            // Set export PDF link to open the export in a new tab
            try {
              var exportBtnEl = document.getElementById('exportPdfBtn');
              if (exportBtnEl) {
                var pdfUrl = '/program/' + (program.id || id) + '/export-pdf';
                exportBtnEl.href = pdfUrl;
                exportBtnEl.target = '_blank';
              }
            } catch (e) {}
            detailModal.show();
          }
        });
    }

    // Reset modal detail jika dibuka setelah penghapusan
    function resetDetailModal() {
      const ids = [
        'detailNamaProgram', 'detailKategori', 'detailAnakDidik', 'detailGuruFokus', 'detailKonsultan',
        'detailTanggalMulai', 'detailTanggalSelesai', 'detailDeskripsi', 'detailTargetPembelajaran',
        'detailCatatanKonsultan', 'detailStatus', 'detailKemampuan', 'detailWawancara',
        'detailKemampuanSaatIni', 'detailSaranRekomendasi', 'detailDiagnosaPsiko'
      ];
      ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          if (id === 'detailKemampuan') {
            el.innerHTML = '';
          } else {
            el.textContent = '';
          }
        }
      });
      // Also clear psikologi-specific rekomendasi display/text
      const elRK = document.getElementById('detailRekomendasi');
      if (elRK) {
        if (elRK.parentElement) elRK.parentElement.style.display = '';
        elRK.textContent = '';
      }
      const saranGen = document.getElementById('detailSaranRekomendasi');
      if (saranGen) {
        if (saranGen.parentElement) saranGen.parentElement.style.display = '';
        saranGen.textContent = '';
      }
    }

    window.loadRiwayatObservasi = function(btn) {
      resetDetailModal();
      var programId = btn.getAttribute('data-program-id');
      var listDiv = document.getElementById('riwayatObservasiList');
      listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
      // Ambil id anak didik dari atribut data-anak-didik-id jika ada, fallback ke programId
      var anakDidikId = btn.getAttribute('data-anak-didik-id') || programId;
      // simpan untuk refresh setelah penghapusan
      window.currentRiwayatAnakId = anakDidikId;
      var currentUserId = @json(Auth::id());

      // prepare and show modal (manage stack similar to other views)
      var modalEl = document.getElementById('riwayatObservasiModal');
      var modal = modalEl ? new bootstrap.Modal(modalEl) : null;
      try {
        if (modalEl) pushModalAndShow(modalEl);
      } catch (e) {}

      fetch('/program/riwayat-observasi-program/' + anakDidikId)
        .then(response => response.json())
        .then(res => {
          if (!res.success || !res.riwayat || res.riwayat.length === 0) {
            listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat observasi/evaluasi.</div>';
            if (modal) try {
              modal.show();
            } catch (e) {}
            return;
          }

          // Render groups. Server may provide grouping (groups with .items) or a flat list of items.
          let html = '';
          let groups = [];
          if (Array.isArray(res.riwayat) && res.riwayat.length > 0 && res.riwayat[0].items) {
            groups = res.riwayat;
          } else if (Array.isArray(res.riwayat)) {
            // flat array of items -> group by konsultan_name or user_name client-side
            const grouped = {};
            res.riwayat.forEach(it => {
              const name = it.konsultan_name || it.user_name || (it.konsultan && (it.konsultan.nama || it.konsultan.name)) || '-';
              if (!grouped[name]) grouped[name] = [];
              grouped[name].push(it);
            });
            groups = Object.keys(grouped).map(k => ({
              name: k,
              items: grouped[k]
            }));
          }

          // helper: determine if the current logged-in user may edit/delete this item
          function isItemEditable(item) {
            if (!currentUserId) return false;
            // common fields that might indicate owner
            if (item.user_id && item.user_id == currentUserId) return true;
            if (item.userId && item.userId == currentUserId) return true;
            if (item.konsultan_id && item.konsultan_id == currentUserId) return true;
            if (item.konsultan) {
              if (item.konsultan.id && item.konsultan.id == currentUserId) return true;
              if (item.konsultan.user_id && item.konsultan.user_id == currentUserId) return true;
              if (item.konsultan.userId && item.konsultan.userId == currentUserId) return true;
            }
            return false;
          }

          groups.forEach(group => {
            const groupName = group.name || group.konsultan_name || group.user_name || '-';
            html += `<div class="mb-3">`;
            html += `<div class="fw-bold bg-light p-2 rounded border mb-2"><i class='ri-user-line me-1'></i> ${groupName}</div>`;
            html += `<ul class="list-group">`;
            (group.items || []).forEach(item => {
              html += `<li class="list-group-item d-flex justify-content-between align-items-center" data-sumber="${item.sumber || ''}" data-id="${item.id}">`;
              // compute display day/date with fallbacks and basic formatting
              try {
                var displayDay = item.hari || '';
                var displayDate = item.tanggal || item.date || item.created_at || item.datetime || '';
                if (!displayDate && item.timestamp) displayDate = item.timestamp;
                if (displayDate) {
                  var parsed = new Date(displayDate);
                  if (!isNaN(parsed.getTime())) {
                    // use Indonesian locale for human-friendly output
                    try {
                      if (!displayDay) displayDay = parsed.toLocaleDateString('id-ID', {
                        weekday: 'long'
                      });
                    } catch (e) {}
                    try {
                      displayDate = parsed.toLocaleDateString('id-ID');
                    } catch (e) {}
                  }
                }
              } catch (e) {
                var displayDay = item.hari || '';
                var displayDate = item.tanggal || '';
              }
              html += '<div><b>' + (displayDay || '') + '</b>, ' + (displayDate || '') + '</div>';

              // Desktop actions (visible on sm and up)
              html += `<div class="d-none d-sm-flex">`;
              html += `<button class="btn btn-outline-info me-1" onclick="showDetailObservasi('${item.sumber || ''}', ${item.id})" title="Lihat"><i class='ri-eye-line'></i></button>`;
              // Show Edit & Delete only when the item belongs to current user (UI only)
              if (isItemEditable(item)) {
                html += `<button class="btn btn-outline-warning me-1" onclick="(function(e){ window._lastClickedRiwayatItem = e.currentTarget.closest('li'); editObservasi(${item.id}); })(event)" title="Edit"><i class='ri-edit-line'></i></button>`;
                html += `<button class="btn btn-outline-danger" onclick="hapusObservasi(${item.id})" title="Hapus"><i class='ri-delete-bin-line'></i></button>`;
              }
              html += `</div>`;

              // Mobile actions dropdown
              html += `<div class="d-inline-block d-sm-none dropdown">`;
              html += `<button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;"><i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.25em;"></i></button>`;
              html += `<ul class="dropdown-menu dropdown-menu-end">`;
              html += `<li><a class="dropdown-item" href="#" onclick="showDetailObservasi('${item.sumber || ''}', ${item.id});return false;"><i class='ri-eye-line me-1'></i> Lihat</a></li>`;
              if (isItemEditable(item)) {
                html += `<li><a class="dropdown-item" href="#" onclick="(function(e){ window._lastClickedRiwayatItem = e.currentTarget.closest('li'); editObservasi(${item.id});return false; })(event)"><i class='ri-edit-line me-1'></i> Edit</a></li>`;
                html += `<li><a class="dropdown-item text-danger" href="#" onclick="hapusObservasi(${item.id});return false;"><i class='ri-delete-bin-line me-1'></i> Hapus</a></li>`;
              }
              html += `</ul></div>`;

              html += `</li>`;
            });
            html += `</ul></div>`;
          });

          listDiv.innerHTML = html;
          if (modal) try {
            modal.show();
          } catch (e) {}
        })
        .catch(() => {
          listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        });
    }

    window.editObservasi = function(id) {
      // Navigate to edit page for the observasi; if a sumber was provided in rendering, caller
      // should set window._lastSumberBeforeEdit. Fallback to id-only route.
      try {
        var sumber = null;
        // if caller rendered item with a data-sumber attribute on last clicked element, prefer it
        if (window._lastClickedRiwayatItem && window._lastClickedRiwayatItem.dataset && window._lastClickedRiwayatItem.dataset.sumber) {
          sumber = window._lastClickedRiwayatItem.dataset.sumber;
        }
        if (sumber) {
          window.location.href = '/program/observasi-program/' + sumber + '/' + id + '/edit';
        } else {
          window.location.href = '/program/observasi-program/' + id + '/edit';
        }
      } catch (e) {
        // fallback
        window.location.href = '/program/observasi-program/' + id + '/edit';
      }
    }

    // Fungsi hapus observasi dengan logika aman
    window.hapusObservasi = function(id) {
      if (!confirm('Yakin ingin menghapus observasi ini?')) return;

      // Try to detect sumber (source) for source-aware deletion. Prefer last clicked item, else search DOM.
      var sumber = null;
      try {
        if (window._lastClickedRiwayatItem && window._lastClickedRiwayatItem.dataset && window._lastClickedRiwayatItem.dataset.sumber) {
          sumber = window._lastClickedRiwayatItem.dataset.sumber;
        }
      } catch (e) {}
      if (!sumber) {
        try {
          var li = document.querySelector('li.list-group-item[data-id="' + id + '"]');
          if (li && li.dataset && li.dataset.sumber) sumber = li.dataset.sumber;
        } catch (e) {}
      }

      var url = sumber ? '/program/observasi-program/' + sumber + '/' + id : '/program/observasi-program/' + id;
      fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(res => {
          if (res.success) {
            showToast('Berhasil dihapus', 'success');
            // Tutup modal detail jika sedang terbuka
            var detailModalEl = document.getElementById('detailModal');
            var detailModal = detailModalEl ? bootstrap.Modal.getInstance(detailModalEl) : null;
            if (detailModal) detailModal.hide();
            // Refresh daftar riwayat: prefer stored currentRiwayatAnakId (set when riwayat dibuka)
            var anakId = window.currentRiwayatAnakId || null;
            if (!anakId) {
              // fallback: try to find a button that opened the riwayat
              var lastBtn = document.querySelector('button[data-program-id][data-anak-didik-id]');
              if (lastBtn) anakId = lastBtn.getAttribute('data-anak-didik-id') || lastBtn.getAttribute('data-program-id');
            }
            if (anakId) {
              var dummyBtn = document.createElement('button');
              dummyBtn.setAttribute('data-anak-didik-id', anakId);
              // reload riwayat (this will also ensure modal stays/opened)
              try {
                loadRiwayatObservasi(dummyBtn);
              } catch (e) {
                console.error('Gagal memanggil loadRiwayatObservasi', e);
              }
            }
          } else {
            showToast('Gagal menghapus data', 'danger');
          }
        }).catch(() => {
          showToast('Gagal menghapus data', 'danger');
        });
    }

    // Toast function
    function showToast(message, type = 'success') {
      let toast = document.getElementById('customToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
        toast.style.zIndex = 9999;
        toast.innerHTML =
          '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        document.body.appendChild(toast);
      } else {
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
      }
      toast.querySelector('.toast-body').textContent = message;
      var bsToast = bootstrap.Toast.getOrCreateInstance(toast, {
        delay: 2000
      });
      bsToast.show();
    }

    // Cleanup leftover backdrops when the riwayat modal is closed
    (function() {
      var riwayatEl = document.getElementById('riwayatObservasiModal');
      if (!riwayatEl) return;
      riwayatEl.addEventListener('hidden.bs.modal', function() {
        // small timeout to allow Bootstrap internal handlers to run first
        setTimeout(function() {
          // only remove backdrops if no other modal is visible
          if (!document.querySelector('.modal.show')) {
            document.querySelectorAll('.modal-backdrop').forEach(function(el) {
              if (el && el.parentNode) el.parentNode.removeChild(el);
            });
            document.body.classList.remove('modal-open');
          }
        }, 50);
      });
    })();
  </script>
</div>
@endsection