<!-- Modal Riwayat Observasi/Evaluasi -->
<div class="modal fade" id="riwayatObservasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat Observasi/Evaluasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatObservasiTableWrapper">
          <div class="text-center py-4 text-body-secondary">Memuat data...</div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('page-script')
<script>
  function showRiwayatObservasi(anakDidikId) {
    const modal = new bootstrap.Modal(document.getElementById('riwayatObservasiModal'));
    const wrapper = document.getElementById('riwayatObservasiTableWrapper');
    wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
    modal.show();
    fetch(`/assessment/riwayat/${anakDidikId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.riwayat) || data.riwayat.length === 0) {
          wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Belum ada riwayat observasi/evaluasi.</div>';
          return;
        }
        let html = `<div class='table-responsive'><table class='table table-bordered'><thead><tr><th>Hari, Tanggal</th><th>Guru Fokus</th><th>Aksi</th></tr></thead><tbody>`;
        data.riwayat.forEach(item => {
          html += `<tr><td>
          ${item.hari}, ${item.tanggal}<br><small>${item.created_at}</small>
        </td><td>${item.guru_fokus ?? '-'}</td><td>
          <a href='/assessment/${item.id}/edit' class='btn btn-sm btn-warning me-1'><i class='ri-edit-line'></i> Edit</a>
          <button class='btn btn-sm btn-danger' onclick='konfirmasiHapusObservasi(${item.id})'><i class='ri-delete-bin-line'></i> Hapus</button>
        </td></tr>`;
        });
        html += '</tbody></table></div>';
        wrapper.innerHTML = html;
      });
  }

  function konfirmasiHapusObservasi(id) {
    if (confirm('Yakin ingin menghapus observasi/evaluasi ini? Tindakan ini tidak dapat dibatalkan.')) {
      fetch(`/assessment/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showRiwayatObservasi(data.anak_didik_id);
          } else {
            alert('Gagal menghapus observasi.');
          }
        });
    }
  }
</script>
@endpush
@extends('layouts/contentNavbarLayout')

@section('title', 'Penilaian Anak')

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
            <h4 class="mb-0">Penilaian Anak</h4>
            <p class="text-body-secondary mb-0">Kelola penilaian perkembangan anak didik</p>
          </div>
          <a href="{{ route('assessment.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-2"></i>Tambah Penilaian
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
    <form method="GET" action="{{ route('assessment.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ request('search') }}">
      </div>

      <!-- Filter Kategori -->
      <select name="kategori" class="form-select" style="max-width: 150px;">
        <option value="">Semua Kategori</option>
        <option value="bina_diri" {{ request('kategori') === 'bina_diri' ? 'selected' : '' }}>Bina Diri</option>
        <option value="akademik" {{ request('kategori') === 'akademik' ? 'selected' : '' }}>Akademik</option>
        <option value="motorik" {{ request('kategori') === 'motorik' ? 'selected' : '' }}>Motorik</option>
        <option value="perilaku" {{ request('kategori') === 'perilaku' ? 'selected' : '' }}>Perilaku</option>
        <option value="vokasi" {{ request('kategori') === 'vokasi' ? 'selected' : '' }}>Vokasi</option>
      </select>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('assessment.index') }}" class="btn btn-outline-secondary" title="Reset">
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
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Guru Fokus</th>
              <th>Program</th>
              <th>Kategori</th>
              <th>Tanggal Observasi/Evaluasi</th>
              <th>Penilaian Perkembangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assessments as $index => $assessment)
            <tr id="row-{{ $assessment->id }}">
              <td>{{ ($assessments->currentPage() - 1) * 15 + $index + 1 }}</td>
              <td>
                <strong>{{ $assessment->anakDidik->nama ?? '-' }}</strong><br>
                <small class="text-body-secondary">{{ $assessment->anakDidik->nis ?? '-' }}</small>
              </td>
              <td>
                @php
                $guruFokus = $assessment->anakDidik && $assessment->anakDidik->guruFokus ? $assessment->anakDidik->guruFokus->nama : '-';
                @endphp
                {{ $guruFokus }}
              </td>
              <td>
                @if($assessment->program_id && $assessment->program)
                {{ $assessment->program->nama_program }}
                @else
                <span class="text-body-secondary">-</span>
                @endif
              </td>
              <td>
                @php
                $kategoriColors = [
                'bina_diri' => 'primary',
                'akademik' => 'info',
                'motorik' => 'success',
                'perilaku' => 'warning',
                'vokasi' => 'danger',
                ];
                $kategoriLabels = [
                'bina_diri' => 'Bina Diri',
                'akademik' => 'Akademik',
                'motorik' => 'Motorik',
                'perilaku' => 'Perilaku',
                'vokasi' => 'Vokasi',
                ];
                @endphp
                <span class="badge bg-label-{{ $kategoriColors[$assessment->kategori] ?? 'secondary' }}">
                  {{ $kategoriLabels[$assessment->kategori] ?? $assessment->kategori }}
                </span>
              </td>
              <td>{{ $assessment->tanggal_assessment ? $assessment->tanggal_assessment->format('d M Y') : '-' }}</td>
              <td>
                @php
                $perkembanganLabels = [
                1 => '1 - Ada perkembangan 25%',
                2 => '2 - Ada perkembangan 50%',
                3 => '3 - Ada perkembangan 75%',
                4 => '4 - Ada perkembangan 100%'
                ];
                @endphp
                @if(isset($assessment->perkembangan) && $assessment->perkembangan)
                <span class="badge bg-label-info">{{ $perkembanganLabels[$assessment->perkembangan] ?? $assessment->perkembangan }}</span>
                @else
                <span class="text-body-secondary">-</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-info"
                    onclick="showRiwayatObservasi({{ $assessment->anakDidik->id ?? 0 }})"
                    title="Riwayat Observasi/Evaluasi">
                    <i class="ri-history-line"></i> Riwayat Observasi/Evaluasi
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="mb-3">
                  <i class="ri-search-line" style="font-size: 3rem; color: #ccc;"></i>
                </div>
                <p class="text-body-secondary mb-0">Tidak ada penilaian ditemukan</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $assessments->firstItem() ?? 0 }} hingga {{ $assessments->lastItem() ?? 0 }} dari {{ $assessments->total() }} data
        </div>
        <nav>
          {{ $assessments->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Penilaian</h5>
        <div class="d-flex gap-2 align-items-center">
          <a id="exportPdfBtn" href="#" class="btn btn-danger btn-sm" target="_blank">
            <i class="ri-file-pdf-line me-1"></i> Export PDF
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Anak Didik</p>
            <p class="fw-medium" id="detailAnakDidik"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Konsultan</p>
            <p class="fw-medium" id="detailKonsultan"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Kategori</p>
            <p class="fw-medium" id="detailKategori"></p>
          </div>
          <div class="col-md-6">
            <p class="text-body-secondary text-sm mb-1">Tanggal Penilaian</p>
            <p class="fw-medium" id="detailTanggal"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Hasil Penilaian</p>
            <p class="fw-medium" id="detailHasil" style="white-space: pre-wrap;"></p>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Rekomendasi</p>
            <p class="fw-medium" id="detailRekomendasi" style="white-space: pre-wrap;"></p>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <p class="text-body-secondary text-sm mb-1">Saran</p>
            <p class="fw-medium" id="detailSaran" style="white-space: pre-wrap;"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function showDetail(btn) {
    const assessmentId = btn.getAttribute('data-assessment-id');
    fetch(`/assessment/${assessmentId}`)
      .then(response => response.json())
      .then(data => {
        const assessment = data.data;
        document.getElementById('detailAnakDidik').textContent = assessment.anak_didik?.nama || '-';
        document.getElementById('detailKonsultan').textContent = assessment.konsultan?.nama || '-';
        document.getElementById('detailKategori').textContent = formatKategori(assessment.kategori);
        document.getElementById('detailTanggal').textContent = assessment.tanggal_assessment ? formatDate(assessment.tanggal_assessment) : '-';
        document.getElementById('detailHasil').textContent = assessment.hasil_penilaian || '-';
        document.getElementById('detailRekomendasi').textContent = assessment.rekomendasi || '-';
        document.getElementById('detailSaran').textContent = assessment.saran || '-';
        // Set export PDF button link
        document.getElementById('exportPdfBtn').href = `/assessment/${assessmentId}/export-pdf`;
      });
  }

  function deleteData(btn) {
    if (confirm('Apakah Anda yakin ingin menghapus penilaian ini?')) {
      const assessmentId = btn.getAttribute('data-assessment-id');
      const row = document.getElementById(`row-${assessmentId}`);

      fetch(`/assessment/${assessmentId}`, {
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