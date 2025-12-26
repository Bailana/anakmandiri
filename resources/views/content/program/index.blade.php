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
          <a href="{{ route('program.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Observasi/Evaluasi
          </a>
          @endif
        </div>
      </div>
    </div>
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
            @forelse($programs as $index => $program)
            <tr id="row-{{ $program->sumber }}-{{ $program->id }}">
              <td class="no-col">{{ ($programs->currentPage() - 1) * $programs->perPage() + $index + 1 }}</td>
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
                <span class="badge bg-label-primary">{{ $program->anakDidik->guruFokus->nama }}</span>
                @else
                -
                @endif
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                  data-bs-target="#riwayatObservasiModal" data-anak-didik-id="{{ $program->anak_didik_id }}"
                  onclick="loadRiwayatObservasi(this)" title="Riwayat Observasi/Evaluasi">
                  <i class="ri-history-line"></i>
                </button>
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
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
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
              <span id="detailGuruFokus"></span>
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
              el.textContent = program.anak_didik && program.anak_didik.nama ? program.anak_didik.nama : (program.anak_didik_nama || '-');
            }
            el = document.getElementById('detailGuruFokus');
            if (el && program && document.body.contains(el)) {
              let guruFokusHtml = '-';
              if (program.anak_didik && (program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus && program.anak_didik.guruFokus.nama))) {
                guruFokusHtml = program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus ? program.anak_didik.guruFokus.nama : '-');
              } else if (program.guru_fokus_nama) {
                guruFokusHtml = program.guru_fokus_nama;
              }
              el.textContent = guruFokusHtml;
            }
            el = document.getElementById('detailKonsultan');
            if (el && program && document.body.contains(el)) el.textContent = program.konsultan_nama || '-';
            el = document.getElementById('detailKemampuan');
            if (el && program && document.body.contains(el)) {
              let kemampuanHtml = '';
              if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
                kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">KEMAMPUAN</th><th class="text-center">1</th><th class="text-center">2</th><th class="text-center">3</th><th class="text-center">4</th><th class="text-center">5</th></tr></thead><tbody>';
                program.kemampuan.forEach((item, idx) => {
                  let skalaInt = (typeof item.skala === 'string' || typeof item.skala === 'number') ? parseInt(item.skala) : null;
                  kemampuanHtml += `<tr><td>${item.judul}</td>`;
                  for (let skala = 1; skala <= 5; skala++) {
                    kemampuanHtml += `<td class="text-center">${skalaInt === skala ? '<i class=\"ri-check-line text-success\"></i>' : ''}</td>`;
                  }
                  kemampuanHtml += '</tr>';
                });
                kemampuanHtml += '</tbody></table></div>';
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
              var detailKemampuanBlock = document.getElementById('detailKemampuan') ? document.getElementById('detailKemampuan').parentElement : null;
              if (detailKemampuanBlock) detailKemampuanBlock.style.display = 'none';
              var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById('detailWawancara').parentElement : null;
              if (wawancaraBlock) wawancaraBlock.style.display = 'none';
              var kemampuanSaatIniBlock = document.getElementById('detailKemampuanSaatIni') ? document.getElementById('detailKemampuanSaatIni').parentElement : null;
              if (kemampuanSaatIniBlock) kemampuanSaatIniBlock.style.display = 'none';
              var saranBlock = document.getElementById('detailSaranRekomendasi') ? document.getElementById('detailSaranRekomendasi').parentElement : null;
              if (saranBlock) saranBlock.style.display = 'none';
              // hide general diagnosa block and use psikologi-specific diagnosa placed above Kesimpulan
              var generalDiagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById('detailDiagnosa').parentElement : null;
              if (generalDiagnosaBlock) generalDiagnosaBlock.style.display = 'none';
              // also ensure the general rekomendasi block is hidden (if present)
              var saranBlockGen = document.getElementById('detailSaranRekomendasi') ? document.getElementById('detailSaranRekomendasi').parentElement : null;
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
              var detailKemampuanBlock = document.getElementById('detailKemampuan') ? document.getElementById('detailKemampuan').parentElement : null;
              if (detailKemampuanBlock) detailKemampuanBlock.style.display = '';
              var wawancaraBlock = document.getElementById('detailWawancara') ? document.getElementById('detailWawancara').parentElement : null;
              if (wawancaraBlock) wawancaraBlock.style.display = '';
              var kemampuanSaatIniBlock = document.getElementById('detailKemampuanSaatIni') ? document.getElementById('detailKemampuanSaatIni').parentElement : null;
              if (kemampuanSaatIniBlock) kemampuanSaatIniBlock.style.display = '';
              var saranBlock = document.getElementById('detailSaranRekomendasi') ? document.getElementById('detailSaranRekomendasi').parentElement : null;
              if (saranBlock) saranBlock.style.display = '';
              // restore general diagnosa block and clear psikologi-specific diagnosa
              var generalDiagnosaBlock = document.getElementById('detailDiagnosa') ? document.getElementById('detailDiagnosa').parentElement : null;
              if (generalDiagnosaBlock) generalDiagnosaBlock.style.display = '';
              var elDiagP = document.getElementById('detailDiagnosaPsiko');
              if (elDiagP) elDiagP.textContent = '';
              // restore general rekomendasi block if present
              var saranBlockGen = document.getElementById('detailSaranRekomendasi') ? document.getElementById('detailSaranRekomendasi').parentElement : null;
              if (saranBlockGen) saranBlockGen.style.display = '';
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
            // Diagnosa
            el = document.getElementById('detailDiagnosa');
            if (el && program && document.body.contains(el)) {
              el.textContent = program.diagnosa || '-';
            }
            // Tampilkan modal detail
            var detailModal = new bootstrap.Modal(detailModalEl);
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
      // Inject current user id from backend
      var currentUserId = @json(Auth::id());
      fetch('/program/riwayat-observasi-program/' + anakDidikId)
        .then(response => response.json())
        .then(res => {
          if (!res.success || !res.riwayat || res.riwayat.length === 0) {
            listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat observasi/evaluasi.</div>';
            return;
          }
          // Group by konsultan (if present) else by user
          const groups = {};
          res.riwayat.forEach(item => {
            const key = item.konsultan_id ? `konsultan_${item.konsultan_id}` : `user_${item.user_id}`;
            const name = item.konsultan_name || item.user_name || '-';
            if (!groups[key]) groups[key] = {
              name: name,
              items: []
            };
            groups[key].items.push(item);
          });
          let html = '';
          Object.keys(groups).forEach((userId, idx, arr) => {
            const group = groups[userId];
            html += `<div class="mb-3">
              <div class="fw-bold bg-light p-2 rounded border mb-2"><i class='ri-user-line me-1'></i> ${group.name}</div>
              <ul class="list-group">`;
            group.items.forEach(item => {
              html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <span><b>${item.hari}</b>, ${item.tanggal}</span>
                <span>
                  <button class="btn btn-sm btn-outline-info me-1" onclick="showDetailObservasi('${item.sumber}', ${item.id})" title="Lihat"><i class='ri-eye-line'></i></button>
                  ${item.user_id == currentUserId ? `
                    <button class="btn btn-sm btn-outline-warning me-1" onclick="editObservasi(${item.id})" title="Edit"><i class='ri-edit-line'></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="hapusObservasi(${item.id})" title="Hapus"><i class='ri-delete-bin-line'></i></button>
                  ` : ''}
                </span>
              </li>`;
            });
            html += '</ul></div>';
          });
          listDiv.innerHTML = html;
        })
        .catch(() => {
          listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        });
    }

    function editObservasi(id) {
      // TODO: Implementasi edit observasi
      alert('Edit observasi ID: ' + id);
    }

    // Fungsi hapus observasi dengan logika aman
    function hapusObservasi(id) {
      if (!confirm('Yakin ingin menghapus observasi ini?')) return;
      fetch('/program/observasi-program/' + id, {
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
            // Refresh daftar riwayat dengan id anak didik terakhir yang aktif di modal
            var modal = document.getElementById('riwayatObservasiModal');
            if (modal) {
              var anakDidikId = null;
              // Cari tombol yang terakhir membuka modal
              var lastBtn = document.querySelector('button[data-bs-target="#riwayatObservasiModal"].active, button[data-bs-target="#riwayatObservasiModal"]:focus');
              if (!lastBtn) lastBtn = document.querySelector('button[data-bs-target="#riwayatObservasiModal"]');
              if (lastBtn) anakDidikId = lastBtn.getAttribute('data-anak-didik-id');
              if (anakDidikId) {
                // Buat dummy btn agar loadRiwayatObservasi tetap dapat parameter btn
                var dummyBtn = document.createElement('button');
                dummyBtn.setAttribute('data-anak-didik-id', anakDidikId);
                loadRiwayatObservasi(dummyBtn);
              }
            }
          } else {
            showToast('Gagal menghapus data', 'danger');
          }
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
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
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
  </script>
  @endsection