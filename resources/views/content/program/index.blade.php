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
              <td>{{ ($programs->currentPage() - 1) * 15 + $index + 1 }}</td>
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
              <td>{{ $program->konsultan->nama ?? '-' }}</td>
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
              <td colspan="7" class="text-center">Tidak ada data observasi/evaluasi</td>
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
            <span><b>${item.hari}</b>, ${item.tanggal} <span class="badge bg-label-primary ms-2">${item.nama_program || '-'}</span></span>
            <span>
              <button class="btn btn-sm btn-info me-1" onclick="lihatObservasi(${item.id})">Lihat</button>
              <button class="btn btn-sm btn-warning me-1" onclick="editObservasi(${item.id})">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="hapusObservasi(${item.id})">Hapus</button>
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

    function lihatObservasi(id) {
      // Tampilkan detail program/observasi di modal detail
      fetch('/program/observasi-program/' + id)
        .then(response => response.json())
        .then(res => {
          if (res.success && res.data) {
            // Isi modal detail dengan data observasi
            const program = res.data;
            document.getElementById('detailNamaProgram').textContent = program.nama_program || '-';
            document.getElementById('detailKategori').textContent = formatKategori(program.kategori) || '-';
            document.getElementById('detailAnakDidik').textContent = program.anak_didik_id || '-';
            document.getElementById('detailGuruFokus').innerHTML = '-'; // Optional: fetch guru fokus jika perlu
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

    function editObservasi(id) {
      // TODO: Implementasi edit observasi
      alert('Edit observasi ID: ' + id);
    }

    function hapusObservasi(id) {
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
              alert('Berhasil dihapus');
              // Refresh daftar riwayat
              document.querySelector('[data-bs-target="#riwayatObservasiModal"]').click();
            } else {
              alert('Gagal menghapus data');
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
          <h5 class="modal-title">Detail Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div class="row mb-3">
            <div class="col-md-6">
              <p class="text-body-secondary text-sm mb-1">Nama Program</p>
              <p class="fw-medium" id="detailNamaProgram"></p>
            </div>
            <div class="col-md-6">
              <p class="text-body-secondary text-sm mb-1">Kategori</p>
              <p class="fw-medium" id="detailKategori"></p>
            </div>
          </div>

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
            <div class="col-md-6">
              <p class="text-body-secondary text-sm mb-1">Tanggal Mulai</p>
              <p class="fw-medium" id="detailTanggalMulai"></p>
            </div>
            <div class="col-md-6">
              <p class="text-body-secondary text-sm mb-1">Tanggal Selesai</p>
              <p class="fw-medium" id="detailTanggalSelesai"></p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Deskripsi</p>
              <p class="fw-medium" id="detailDeskripsi"></p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Target Pembelajaran</p>
              <p class="fw-medium" id="detailTargetPembelajaran"></p>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Catatan Konsultan</p>
              <p class="fw-medium" id="detailCatatanKonsultan"></p>
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
          <div class="row">
            <div class="col-12">
              <p class="text-body-secondary text-sm mb-1">Status Persetujuan</p>
              <p class="fw-medium" id="detailStatus"></p>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12 text-end">
              <button type="button" class="btn btn-outline-info" id="btnCetakProgram" onclick="cetakProgram()">
                <i class="ri-printer-line me-2"></i>Cetak
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showDetail(btn) {
      const programId = btn.getAttribute('data-program-id');
      window._lastProgramId = programId;
      fetch(`/program/${programId}`)
        .then(response => response.json())
        .then(data => {
          const program = data.data;
          document.getElementById('detailNamaProgram').textContent = program.nama_program || '-';
          document.getElementById('detailKategori').textContent = formatKategori(program.kategori) || '-';
          document.getElementById('detailAnakDidik').textContent = program.anak_didik?.nama || '-';
          // Guru Fokus badge
          let guruFokusHtml = '-';
          if (program.anak_didik && (program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus && program.anak_didik.guruFokus.nama))) {
            const namaGuru = program.anak_didik.guru_fokus_nama || (program.anak_didik.guruFokus ? program.anak_didik.guruFokus.nama : '');
            guruFokusHtml = `<span class="badge bg-label-primary" style="background-color: #ede9fe; color: #7c3aed; font-weight: 500; font-size: 0.95rem; padding: 0.18em 0.7em; border-radius: 0.4em;">${namaGuru}</span>`;
          }
          document.getElementById('detailGuruFokus').innerHTML = guruFokusHtml;
          document.getElementById('detailKonsultan').textContent = program.konsultan?.nama || '-';
          document.getElementById('detailTanggalMulai').textContent = program.tanggal_mulai ? formatDate(program.tanggal_mulai) : '-';
          document.getElementById('detailTanggalSelesai').textContent = program.tanggal_selesai ? formatDate(program.tanggal_selesai) : '-';
          document.getElementById('detailDeskripsi').textContent = program.deskripsi || '-';
          document.getElementById('detailTargetPembelajaran').textContent = program.target_pembelajaran || '-';
          document.getElementById('detailCatatanKonsultan').textContent = program.catatan_konsultan || '-';
          document.getElementById('detailStatus').innerHTML = program.is_approved ?
            '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Disetujui</span>' :
            '<span class="badge bg-warning"><i class="ri-time-line me-1"></i>Menunggu Approval</span>';

          // Tampilkan kemampuan (tabel)
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
        });
    }
    // Fungsi cetak (sederhana, bisa dikembangkan ke PDF/print view)
    function cetakProgram(id) {
      // Jika dipanggil dari modal, gunakan id terakhir
      if (!id && window._lastProgramId) id = window._lastProgramId;
      fetch(`/program/${id}`)
        .then(response => response.json())
        .then(data => {
          const program = data.data;
          let win = window.open('', '_blank');
          let html = `<html><head><title>Cetak Program</title><style>body{font-family:sans-serif}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:4px;text-align:center}th{background:#eee}</style></head><body>`;
          html += `<h2>Program Pembelajaran Anak</h2>`;
          html += `<b>Nama Program:</b> ${program.nama_program}<br>`;
          html += `<b>Kategori:</b> ${formatKategori(program.kategori)}<br>`;
          html += `<b>Anak Didik:</b> ${program.anak_didik?.nama || '-'}<br>`;
          html += `<b>Konsultan:</b> ${program.konsultan?.nama || '-'}<br>`;
          html += `<b>Tanggal Mulai:</b> ${program.tanggal_mulai ? formatDate(program.tanggal_mulai) : '-'}<br>`;
          html += `<b>Tanggal Selesai:</b> ${program.tanggal_selesai ? formatDate(program.tanggal_selesai) : '-'}<br><br>`;
          html += `<b>Penilaian Kemampuan:</b><br>`;
          if (Array.isArray(program.kemampuan) && program.kemampuan.length > 0) {
            html += '<table><thead><tr><th>Kemampuan</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th></tr></thead><tbody>';
            program.kemampuan.forEach(item => {
              html += `<tr><td>${item.judul}</td>`;
              for (let skala = 1; skala <= 5; skala++) {
                html += `<td>${parseInt(item.skala) === skala ? '✔️' : ''}</td>`;
              }
              html += '</tr>';
            });
            html += '</tbody></table>';
          } else {
            html += '<em>Tidak ada data kemampuan</em>';
          }
          html += `<br><b>Wawancara:</b><br>${program.wawancara || '-'}<br><br>`;
          html += `<b>Kemampuan Saat Ini:</b><br>${program.kemampuan_saat_ini || '-'}<br><br>`;
          html += `<b>Saran / Rekomendasi:</b><br>${program.saran_rekomendasi || '-'}<br><br>`;
          html += `<b>Status Persetujuan:</b> ${program.is_approved ? 'Disetujui' : 'Menunggu Approval'}<br>`;
          html += '</body></html>';
          win.document.write(html);
          win.document.close();
          win.print();
        });
    }

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
  </script>
  @endsection