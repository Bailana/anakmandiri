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
          <a href="{{ route('program.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Observasi/Evaluasi
          </a>
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
        <input type="text" name="search" class="form-control" placeholder="Cari program atau nama anak..." value="{{ request('search') }}">
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
        <table class="table table-hover" id="programTable" style="font-size: 1rem;">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Jenis Kelamin</th>
              <th>No. Telp Orang Tua</th>
              <th>Guru Fokus</th>
              <th>Konsultan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($programs as $index => $program)
            <tr id="row-{{ $program->id }}">
              <td class="no-col">{{ ($programs->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>{{ $program->anakDidik->nama ?? '-' }}</td>
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
                <span class="badge bg-label-primary" style="background-color: #ede9fe; color: #7c3aed; font-weight: 500; font-size: 0.95rem; padding: 0.18em 0.7em; border-radius: 0.4em;">
                  {{ $program->anakDidik->guruFokus->nama }}
                </span>
                @else
                -
                @endif
              </td>
              <td>
                @if($program->konsultan)
                <span class="fw-semibold">{{ $program->konsultan->nama }}</span>
                <br>
                <small class="text-muted">(Input oleh: {{ $program->konsultan->nama }})</small>
                @else
                -
                @endif
              </td>
              <td>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-info"
                  data-bs-toggle="modal"
                  data-bs-target="#riwayatObservasiModal"
                  data-anak-didik-id="{{ $program->anak_didik_id }}"
                  onclick="loadRiwayatObservasi(this)"
                  title="Riwayat Observasi/Evaluasi">
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
          Menampilkan {{ $programs->firstItem() ?? 0 }} hingga {{ $programs->lastItem() ?? 0 }} dari {{ $programs->total() }} data
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

  @push('scripts')
  <script>
    // Pastikan formatKategori tersedia sebelum dipakai
    // function formatKategori(kategori) {
    //   const labels = {
    //     'bina_diri': 'Bina Diri',
    //     'akademik': 'Akademik',
    //     'motorik': 'Motorik',
    //     'perilaku': 'Perilaku',
    //     'vokasi': 'Vokasi'
    //   };
    //   return labels[kategori] || kategori;
    // }

    // Fungsi global agar tombol lihat pada modal dapat diakses
    window.showDetailObservasi = function(id) {
      fetch('/program/observasi-program/' + id)
        .then(response => response.json())
        .then(res => {
          if (window.console) {
            console.log('[showDetailObservasi] FULL RESPONSE:', res);
          }
          if (res.success && res.data) {
            const program = res.data;
            if (window.console) {
              console.log('[showDetailObservasi] DATA KEMAMPUAN:', program.kemampuan);
              if (!Array.isArray(program.kemampuan)) {
                console.log('[showDetailObservasi] DATA FULL:', program);
              }
            }
            var el;
            // Anak Didik
            el = document.getElementById('detailAnakDidik');
            if (el) el.textContent = program.anak_didik?.nama || program.anak_didik_nama || '-';
            // Guru Fokus
            el = document.getElementById('detailGuruFokus');
            if (el) {
              let guruFokusHtml = '-';
              if (program.anak_didik && (program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus && program.anak_didik.guruFokus.nama))) {
                guruFokusHtml = program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus ? program.anak_didik.guruFokus.nama : '-');
              } else if (program.guru_fokus_nama) {
                guruFokusHtml = program.guru_fokus_nama;
              }
              el.textContent = guruFokusHtml;
            }
            // Konsultan
            el = document.getElementById('detailKonsultan');
            if (el) el.textContent = program.konsultan?.nama || program.konsultan_nama || '-';
            // Penilaian Kemampuan
            el = document.getElementById('detailKemampuan');
            if (el) {
              let kemampuanHtml = '';
              if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
                kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">KEMAMPUAN</th><th class="text-center">1</th><th class="text-center">2</th><th class="text-center">3</th><th class="text-center">4</th><th class="text-center">5</th></tr></thead><tbody>';
                program.kemampuan.forEach((item, idx) => {
                  let skalaInt = (typeof item.skala === 'string' || typeof item.skala === 'number') ? parseInt(item.skala) : null;
                  if (window.console) {
                    console.log(`[showDetailObservasi] Baris ${idx+1}: judul=`, item.judul, ', skala=', item.skala, ', typeof skala=', typeof item.skala, ', skalaInt=', skalaInt);
                  }
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
            if (el) el.textContent = program.wawancara || '-';
            el = document.getElementById('detailKemampuanSaatIni');
            if (el) el.textContent = program.kemampuan_saat_ini || '-';
            el = document.getElementById('detailSaranRekomendasi');
            if (el) el.textContent = program.saran_rekomendasi || '-';
            // Tampilkan modal detail
            var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            detailModal.show();
          }
        });
    }

    function loadRiwayatObservasi(btn) {
      var programId = btn.getAttribute('data-program-id');
      var listDiv = document.getElementById('riwayatObservasiList');
      listDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';
      // Ambil id anak didik dari atribut data-anak-didik-id jika ada, fallback ke programId
      var anakDidikId = btn.getAttribute('data-anak-didik-id') || programId;
      fetch('/program/riwayat-observasi-program/' + anakDidikId)
        .then(response => response.json())
        .then(res => {
          if (!res.success || !res.riwayat || res.riwayat.length === 0) {
            listDiv.innerHTML = '<div class="text-center text-muted">Belum ada riwayat observasi/evaluasi.</div>';
            return;
          }
          let html = '<ul class="list-group">';
          res.riwayat.forEach(item => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>${item.hari}</b>, ${item.tanggal}</span>
            <span>
              <button class="btn btn-sm btn-outline-info me-1" onclick="showDetailObservasi(${item.id})" title="Lihat"><i class='ri-eye-line'></i></button>
              <button class="btn btn-sm btn-outline-warning me-1" onclick="editObservasi(${item.id})" title="Edit"><i class='ri-edit-line'></i></button>
              <button class="btn btn-sm btn-outline-danger" onclick="hapusObservasi(${item.id})" title="Hapus"><i class='ri-delete-bin-line'></i></button>
            </span>
          </li>`;

          });
          html += '</ul>';
          listDiv.innerHTML = html;
        })
        .catch(() => {
          listDiv.innerHTML = '<div class="text-danger text-center">Gagal memuat data.</div>';
        });
    }

    // function lihatObservasi(id) {
    //   // Tampilkan detail program/observasi di modal detail
    //   fetch('/program/observasi-program/' + id)
    //     .then(response => response.json())
    //     .then(res => {
    //       if (res.success && res.data) {
    //         // Isi modal detail dengan data observasi
    //         const program = res.data;
    //         document.getElementById('detailNamaProgram').textContent = program.nama_program || '-';
    //         document.getElementById('detailKategori').textContent = formatKategori(program.kategori) || '-';
    //         document.getElementById('detailAnakDidik').textContent = program.anak_didik_id || '-';
    //         document.getElementById('detailGuruFokus').innerHTML = '-'; // Optional: fetch guru fokus jika perlu
    //         document.getElementById('detailKonsultan').textContent = program.konsultan_id || '-';
    //         document.getElementById('detailTanggalMulai').textContent = program.tanggal_mulai || '-';
    //         document.getElementById('detailTanggalSelesai').textContent = program.tanggal_selesai || '-';
    //         document.getElementById('detailDeskripsi').textContent = program.deskripsi || '-';
    //         document.getElementById('detailTargetPembelajaran').textContent = program.target_pembelajaran || '-';
    //         document.getElementById('detailCatatanKonsultan').textContent = program.catatan_konsultan || '-';
    //         document.getElementById('detailStatus').innerHTML = program.is_approved ?
    //           '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Disetujui</span>' :
    //           '<span class="badge bg-warning"><i class="ri-time-line me-1"></i>Menunggu Approval</span>';
    //         // Kemampuan
    //         let kemampuanHtml = '';
    //         if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
    //           kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">Kemampuan</th><th class="text-center">1</th><th class="text-center">2</th><th class="text-center">3</th><th class="text-center">4</th><th class="text-center">5</th></tr></thead><tbody>';
    //           program.kemampuan.forEach(item => {
    //             kemampuanHtml += `<tr><td>${item.judul}</td>`;
    //             for (let skala = 1; skala <= 5; skala++) {
    //               kemampuanHtml += `<td class=\"text-center\">${parseInt(item.skala) === skala ? '<i class=\\"ri-check-line text-success\\"></i>' : ''}</td>`;
    //             }
    //             kemampuanHtml += '</tr>';
    //           });
    //           kemampuanHtml += '</tbody></table></div>';
    //         } else {
    //           kemampuanHtml = '<em>Tidak ada data kemampuan</em>';
    //         }
    //         document.getElementById('detailKemampuan').innerHTML = kemampuanHtml;
    //         document.getElementById('detailWawancara').textContent = program.wawancara || '-';
    //         document.getElementById('detailKemampuanSaatIni').textContent = program.kemampuan_saat_ini || '-';
    //         document.getElementById('detailSaranRekomendasi').textContent = program.saran_rekomendasi || '-';
    //         // Tampilkan modal detail
    //         var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    //         detailModal.show();
    //       }
    //     });
    // }

    function editObservasi(id) {
      // TODO: Implementasi edit observasi
      alert('Edit observasi ID: ' + id);
    }

    function hapusObservasi(id) {
      // Fungsi global agar tombol lihat pada modal dapat diakses
      window.showDetailObservasi = function(id) {
        fetch('/program/observasi-program/' + id)
          .then(response => response.json())
          .then(res => {
            if (res.success && res.data) {
              const program = res.data;
              document.getElementById('detailNamaProgram').textContent = program.nama_program || '-';
              document.getElementById('detailKategori').textContent = formatKategori(program.kategori) || '-';
              document.getElementById('detailAnakDidik').textContent = program.anak_didik_id || '-';
              document.getElementById('detailGuruFokus').innerHTML = '-';
              document.getElementById('detailKonsultan').textContent = program.konsultan_id || '-';
              document.getElementById('detailTanggalMulai').textContent = program.tanggal_mulai || '-';
              document.getElementById('detailTanggalSelesai').textContent = program.tanggal_selesai || '-';
              document.getElementById('detailDeskripsi').textContent = program.deskripsi || '-';
              document.getElementById('detailTargetPembelajaran').textContent = program.target_pembelajaran || '-';
              document.getElementById('detailCatatanKonsultan').textContent = program.catatan_konsultan || '-';
              document.getElementById('detailStatus').innerHTML = program.is_approved ?
                '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Disetujui</span>' :
                '<span class="badge bg-warning"><i class="ri-time-line me-1"></i>Menunggu Approval</span>';
              // Kemampuan
              let kemampuanHtml = '';
              if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
                kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">Kemampuan</th><th class="text-center">1</th><th class="text-center">2</th><th class="text-center">3</th><th class="text-center">4</th><th class="text-center">5</th></tr></thead><tbody>';
                program.kemampuan.forEach(item => {
                  kemampuanHtml += `<tr><td>${item.judul}</td>`;
                  for (let skala = 1; skala <= 5; skala++) {
                    kemampuanHtml += `<td class=\"text-center\">${parseInt(item.skala) === skala ? '<i class=\\"ri-check-line text-success\\"></i>' : ''}</td>`;
                  }
                  kemampuanHtml += '</tr>';
                });
                kemampuanHtml += '</tbody></table></div>';
              } else {
                kemampuanHtml = '<em>Tidak ada data kemampuan</em>';
              }
              document.getElementById('detailKemampuan').innerHTML = kemampuanHtml;
              document.getElementById('detailWawancara').textContent = program.wawancara || '-';
              document.getElementById('detailKemampuanSaatIni').textContent = program.kemampuan_saat_ini || '-';
              document.getElementById('detailSaranRekomendasi').textContent = program.saran_rekomendasi || '-';
              // Tampilkan modal detail
              var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
              detailModal.show();
            }
          });
      }
      if (confirm('Yakin ingin menghapus observasi ini?')) {
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
              // Refresh daftar riwayat tanpa menutup modal
              var btn = document.querySelector('[data-bs-target="#riwayatObservasiModal"]');
              if (btn) {
                // Ambil id anak didik dari tombol
                var anakDidikId = btn.getAttribute('data-anak-didik-id');
                // Cek ulang riwayat, jika kosong hapus baris tabel utama
                fetch('/program/riwayat-observasi-program/' + anakDidikId)
                  .then(response => response.json())
                  .then(data => {
                    if (!data.riwayat || data.riwayat.length === 0) {
                      // Hapus baris tabel utama berdasarkan data-anak-didik-id
                      var rows = document.querySelectorAll('tr[id^="row-"]');
                      rows.forEach(function(row) {
                        if (row.querySelector('[data-anak-didik-id]') && row.querySelector('[data-anak-didik-id]').getAttribute('data-anak-didik-id') == anakDidikId) {
                          row.remove();
                        }
                      });
                      // Perbarui penomoran kolom NO
                      // Reset penomoran NO hanya pada tabel utama program
                      var programTable = document.getElementById('programTable');
                      if (programTable) {
                        var no = 1;
                        var rows = programTable.querySelectorAll('tbody tr');
                        rows.forEach(function(row) {
                          var noCell = row.querySelector('td.no-col');
                          if (noCell) {
                            noCell.textContent = no++;
                          }
                        });
                      }
                      // Tutup modal
                      var modal = bootstrap.Modal.getInstance(document.getElementById('riwayatObservasiModal'));
                      if (modal) modal.hide();
                    } else {
                      loadRiwayatObservasi(btn);
                    }
                  });
              }
            } else {
              showToast('Gagal menghapus data', 'danger');
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
          });
      }
    }
  </script>
  @endpush

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

  <!-- <script>
    function showDetail(btn) {
      const programId = btn.getAttribute('data-program-id');
      window._lastProgramId = programId;
      fetch('/program/observasi-program/' + programId)
        .then(response => response.json())
        .then(res => {
          if (window.console) {
            console.log('FULL RESPONSE:', res);
          }
          if (res.success && res.data) {
            const data = res.data;
            if (window.console) {
              console.log('DATA KEMAMPUAN MODAL:', data.kemampuan);
              if (!Array.isArray(data.kemampuan)) {
                console.log('DATA FULL:', data);
              }
            }
            var el;
            el = document.getElementById('detailAnakDidik');
            if (el) el.textContent = data.anak_didik_nama || '-';
            el = document.getElementById('detailGuruFokus');
            if (el) {
              let guruFokusHtml = '-';
              if (data.guru_fokus_nama && data.guru_fokus_nama !== '-') {
                guruFokusHtml = `<span class="badge bg-label-primary" style="background-color: #ede9fe; color: #7c3aed; font-weight: 500; font-size: 0.95rem; padding: 0.18em 0.7em; border-radius: 0.4em;">${data.guru_fokus_nama}</span>`;
              }
              el.innerHTML = guruFokusHtml;
            }
            el = document.getElementById('detailKonsultan');
            if (el) el.textContent = data.konsultan_nama || '-';
            el = document.getElementById('detailKemampuan');
            if (el) {
              let kemampuanHtml = '';
              if (Array.isArray(data.kemampuan) && data.kemampuan.length > 0) {
                kemampuanHtml += '<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th style="width:40%">Kemampuan</th><th class="text-center">1</th><th class="text-center">2</th><th class="text-center">3</th><th class="text-center">4</th><th class="text-center">5</th></tr></thead><tbody>';
                data.kemampuan.forEach((item, idx) => {
                  let skalaInt = (typeof item.skala === 'string' || typeof item.skala === 'number') ? parseInt(item.skala) : null;
                  if (window.console) {
                    console.log(`Baris ${idx+1}: judul=${item.judul}, skala=`, item.skala, ', typeof skala=', typeof item.skala, ', skalaInt=', skalaInt);
                  }
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
            if (el) el.textContent = data.wawancara || '-';
            el = document.getElementById('detailKemampuanSaatIni');
            if (el) el.textContent = data.kemampuan_saat_ini || '-';
            el = document.getElementById('detailSaranRekomendasi');
            if (el) el.textContent = data.saran_rekomendasi || '-';
            // Tampilkan modal detail
            var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            detailModal.show();
          }
        });
    }
    // Set export PDF button link in modal
    document.getElementById('exportPdfBtn').href = `/program/${window._lastProgramWicaraId}/export-pdf`;



    function deleteData(btn) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        const programId = btn.getAttribute('data-program-id');
        const row = document.getElementById(`row-${programId}`);

        fetch(`/program/${programId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              row.remove();
              alert(data.message);
            }
          });
      }
    }

    function formatDate(dateStr) {
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      return new Date(dateStr).toLocaleDateString('id-ID', options);
    }

    function formatKategori(kategori) {
      const labels = {
        'bina_diri': 'Bina Diri',
        'akademik': 'Akademik',
        'motorik': 'Motorik',
        'perilaku': 'Perilaku',
        'vokasi': 'Vokasi'
      };
      return labels[kategori] || kategori;
    }
  </script> -->
  @endsection