<!-- Modal Riwayat Penilaian -->
<div class="modal fade" id="riwayatObservasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat Penilaian</h5>
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

    // Fetch program history (program-anak) and display programs from first to last
    fetch(`/program-anak/riwayat-program/${anakDidikId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.riwayat) || data.riwayat.length === 0) {
          wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Belum ada riwayat penilaian.</div>';
          return;
        }

        // Flatten groups into items with group name
        let items = [];
        data.riwayat.forEach(group => {
          const groupName = group.name || '-';
          (group.items || []).forEach(it => {
            items.push({
              nama_program: it.nama_program || it.nama_program,
              created_at: it.created_at || null,
              group_name: groupName,
              id: it.id || null,
            });
          });
        });

        // Sort ascending by created_at (first -> last)
        items.sort((a, b) => {
          if (!a.created_at) return 1;
          if (!b.created_at) return -1;
          return new Date(a.created_at) - new Date(b.created_at);
        });

        let html = '<div class="list-group">';
        items.forEach(it => {
          const dateText = it.created_at ? it.created_at : '-';
          html += `
            <div class="list-group-item d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold">${it.nama_program}</div>
                <div class="text-muted small">${dateText}</div>
              </div>
              <div class="text-muted small">${it.group_name}</div>
            </div>`;
        });
        html += '</div>';

        wrapper.innerHTML = html;
      }).catch(err => {
        console.error('Failed to load riwayat penilaian', err);
        wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat data.</div>';
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
<div class="row">
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
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @php
            // Deduplicate by anak didik on the current page, keeping first occurrence
            $unique = [];
            foreach ($assessments as $a) {
            $id = optional($a->anakDidik)->id ?? 0;
            if (!isset($unique[$id])) $unique[$id] = $a;
            }
            $unique = array_values($unique);
            @endphp

            @forelse($unique as $index => $assessment)
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
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-info"
                    onclick="showRiwayatObservasi({{ $assessment->anakDidik->id ?? 0 }})"
                    title="Riwayat Observasi/Evaluasi"
                    aria-label="Riwayat Observasi/Evaluasi">
                    <i class="ri-history-line" style="font-size:1.1rem"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-5">
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