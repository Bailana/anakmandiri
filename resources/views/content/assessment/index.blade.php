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
  // Helper: select a program, render its chart, and close the riwayat modal
  window.selectProgramAndClose = function(anakId, programId) {
    try {
      if (typeof renderSelectedProgram === 'function') renderSelectedProgram(anakId, programId);
    } catch (e) {
      console.error('selectProgramAndClose render error', e);
    }
    try {
      const modalEl = document.getElementById('riwayatObservasiModal');
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
    } catch (e) {
      /* ignore */
    }
  };

  window.showRiwayatObservasi = async function(anakDidikId) {
    console.debug('showRiwayatObservasi called for', anakDidikId);
    let modal;
    try {
      modal = new bootstrap.Modal(document.getElementById('riwayatObservasiModal'));
    } catch (err) {
      console.warn('Bootstrap Modal constructor failed, trying jQuery fallback', err);
      try {
        if (window.jQuery) {
          $('#riwayatObservasiModal').modal('show');
        }
      } catch (e) {
        console.error('Failed to show modal via fallback', e);
      }
    }
    const wrapper = document.getElementById('riwayatObservasiTableWrapper');
    wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
    try {
      if (modal && typeof modal.show === 'function') modal.show();
    } catch (e) {
      console.error('modal.show failed', e);
    }

    // Fetch program history (program-anak) and display programs from first to last
    try {
      const res = await fetch(`/program-anak/riwayat-program/${anakDidikId}`);
      const data = await (res.ok ? res.json().catch(() => null) : null);
      if (!data || !data.success || !Array.isArray(data.riwayat) || data.riwayat.length === 0) {
        wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Belum ada riwayat penilaian.</div>';
        return;
      }

      // helpers for kategori: normalize key, display label, and badge class
      const normalizeKey = (k) => String(k || '').toLowerCase().replace(/[\s\-]+/g, '_').replace(/[^a-z0-9_]/g, '');
      const displayLabelFor = (k) => {
        const key = normalizeKey(k);
        switch (key) {
          case 'bina_diri':
            return 'Bina Diri';
          case 'akademik':
            return 'Akademik';
          case 'motorik':
            return 'Motorik';
          case 'perilaku':
            return 'Perilaku';
          case 'vokasi':
            return 'Vokasi';
          default:
            return String(k || 'Lainnya').replace(/[_\-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }
      };
      const badgeFor = (k) => {
        const key = normalizeKey(k);
        switch (key) {
          case 'bina_diri':
            return 'bg-success';
          case 'akademik':
            return 'bg-primary';
          case 'motorik':
            return 'bg-info text-dark';
          case 'perilaku':
            return 'bg-warning text-dark';
          case 'vokasi':
            return 'bg-secondary';
          default:
            return 'bg-dark';
        }
      };

      // Build history index: map program id or name -> array of dates
      const historyIndex = {};
      data.riwayat.forEach(group => {
        (group.items || []).forEach(it => {
          const key = it.id ? `id:${it.id}` : `name:${String(it.nama_program || '').trim().toLowerCase()}`;
          historyIndex[key] = historyIndex[key] || [];
          historyIndex[key].push(it.created_at || null);
        });
      });

      // Fetch per-program assessment history to extract last score per program name
      const scoreIndex = {}; // nameLower -> last score
      try {
        const phRes = await fetch(`/assessment/${anakDidikId}/program-history`, {
          credentials: 'same-origin'
        });
        if (phRes && phRes.ok) {
          const phJson = await phRes.json().catch(() => null);
          if (phJson && phJson.success && Array.isArray(phJson.programs)) {
            phJson.programs.forEach(p => {
              const name = String(p.nama_program || '').trim().toLowerCase();
              if (!name) return;
              const dps = Array.isArray(p.datapoints) ? p.datapoints : [];
              if (dps.length === 0) return;
              const last = dps.slice().sort((a, b) => {
                if (!a.tanggal) return 1;
                if (!b.tanggal) return -1;
                return new Date(b.tanggal) - new Date(a.tanggal);
              })[0];
              if (last && typeof last.score !== 'undefined' && last.score !== null) {
                scoreIndex[name] = last.score;
              }
            });
          }
        }
      } catch (e) {
        console.debug('program-history fetch failed', e);
      }

      // Fetch PPI programs per known kategori (same categories used in create page)
      const kategoriKeys = ['bina_diri', 'akademik', 'motorik', 'perilaku', 'vokasi'];
      const groupMap = {}; // key -> { label, raw, items }
      const ensureGroup = (rawKat) => {
        const key = normalizeKey(rawKat || 'Lainnya');
        if (!groupMap[key]) groupMap[key] = {
          label: displayLabelFor(rawKat),
          raw: rawKat || 'Lainnya',
          items: []
        };
        return groupMap[key];
      };

      try {
        const results = await Promise.all(kategoriKeys.map(k => fetch(`/assessment/ppi-programs?anak_didik_id=${encodeURIComponent(anakDidikId)}&kategori=${encodeURIComponent(k)}`, {
          credentials: 'same-origin'
        }).then(r => r.json().catch(() => null)).catch(() => null)));

        results.forEach((res, i) => {
          const kat = kategoriKeys[i];
          if (!res || !res.success || !Array.isArray(res.programs)) return;
          const g = ensureGroup(kat);
          res.programs.forEach(p => {
            const exists = g.items.find(x => (x.id && p.id && x.id == p.id) || (x.nama_program && p.nama_program && x.nama_program === p.nama_program));
            if (!exists) g.items.push(p);
          });
        });

        // if no ppi programs found at all, fallback: infer kategori from each history item (prefer its.kategori)
        const anyPpi = Object.keys(groupMap).some(k => groupMap[k].items && groupMap[k].items.length);
        if (!anyPpi) {
          // create groups from history items using each item's kategori (not group name)
          data.riwayat.forEach(group => {
            (group.items || []).forEach(it => {
              const rawKat = it.kategori || 'Lainnya';
              const g = ensureGroup(rawKat);
              const exists = g.items.find(x => (x.id && it.id && x.id == it.id) || (x.nama_program && it.nama_program && x.nama_program === it.nama_program));
              if (!exists) g.items.push({
                id: it.id || null,
                nama_program: it.nama_program || '-',
                kategori: rawKat
              });
            });
          });
        } else {
          // build name/id -> kategori lookup from fetched PPI programs
          const nameToKat = {};
          Object.values(groupMap).forEach(g => {
            g.items.forEach(p => {
              if (p.id) nameToKat[`id:${p.id}`] = g.raw;
              if (p.nama_program) nameToKat[String(p.nama_program).trim().toLowerCase()] = g.raw;
            });
          });

          // merge history items into matched groups by id or nama_program; prefer it.kategori, then lookup, else 'Lainnya'
          data.riwayat.forEach(group => {
            (group.items || []).forEach(it => {
              const idKey = it.id ? `id:${it.id}` : null;
              const nameKey = String(it.nama_program || '').trim().toLowerCase();
              const mappedRawKat = it.kategori || (idKey && nameToKat[idKey]) || nameToKat[nameKey] || 'Lainnya';
              const g = ensureGroup(mappedRawKat);
              const exists = g.items.find(x => (x.id && it.id && x.id == it.id) || (x.nama_program && it.nama_program && x.nama_program === it.nama_program));
              if (!exists) g.items.push({
                id: it.id || null,
                nama_program: it.nama_program || '-',
                kategori: mappedRawKat
              });
            });
          });
        }

        // render groups (skip 'Lainnya')
        let html = '';
        Object.keys(groupMap).sort().forEach(k => {
          const g = groupMap[k];
          // skip groups mapped to 'Lainnya' to avoid showing consultant names
          if (normalizeKey(g.raw) === 'lainnya') return;
          html += `<div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="d-flex align-items-center gap-2">
                <span class="badge ${badgeFor(g.raw)} text-uppercase">${g.label}</span>
                <strong class="ms-2">${g.items.length} item</strong>
              </div>
            </div>`;

          if (!g.items.length) {
            html += '<div class="text-body-secondary small">(Tidak ada program pada kategori ini)</div>';
          } else {
            html += '<div class="list-group">';
            g.items.forEach(p => {
              const name = p.nama_program || '-';
              const keyId = p.id ? `id:${p.id}` : `name:${String((p.nama_program||'').trim().toLowerCase())}`;
              const dates = historyIndex[keyId] || [];
              const dateText = (function() {
                const nameKey = String(name || '').trim().toLowerCase();
                if (scoreIndex && typeof scoreIndex[nameKey] !== 'undefined') {
                  // show as integer if whole number else one decimal
                  const s = scoreIndex[nameKey];
                  return 'Skor: ' + (Number.isInteger(s) ? s : (Math.round(s * 10) / 10));
                }
                return dates.length ? dates[0] : '-';
              })();
              const pid = String(p.id || p.nama_program || '').replace(/'/g, "\\'");
              html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-semibold">${name}</div>
                    <div class="text-muted small">${dateText}</div>
                  </div>
                  <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectProgramAndClose(${anakDidikId}, '${pid}')" title="Lihat"><i class="ri-bar-chart-line"></i></button>
                  </div>
                </div>`;
            });
            html += '</div>';
          }

          html += '</div>';
        });

        wrapper.innerHTML = html;
      } catch (err) {
        console.error('Failed to load ppi programs', err);
        wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat daftar program.</div>';
      }
    } catch (err) {
      console.error('Failed to load riwayat penilaian', err);
      wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat data.</div>';
    }
  }

  // Delegate click on riwayat buttons (safe if functions defined later)
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-riwayat');
    if (!btn) return;
    const anakId = btn.dataset.anakId;
    const anakName = btn.dataset.anakName || '';
    console.debug('btn-riwayat clicked', anakId, anakName);
    if (window.showRiwayatObservasi) {
      try {
        window.showRiwayatObservasi(anakId);
      } catch (err) {
        console.error('error calling showRiwayatObservasi', err);
      }
    } else {
      console.warn('showRiwayatObservasi not defined yet');
    }
    if (window.renderChartsForAnak) {
      try {
        window.renderChartsForAnak(anakId, anakName);
      } catch (err) {
        console.error(err);
      }
    }
  });

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
                    class="btn btn-sm btn-outline-info btn-riwayat"
                    data-anak-id="{{ $assessment->anakDidik->id ?? 0 }}"
                    data-anak-name="{{ addslashes($assessment->anakDidik->nama ?? '-') }}"
                    title="Riwayat Penilaian"
                    aria-label="Riwayat Penilaian">
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

<!-- Charts Area -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">Grafik Program Anak</h5>
          <small class="text-body-secondary">Grafik per program (klik ikon riwayat pada baris anak)</small>
        </div>
        <div id="assessment-charts-meta" class="text-end text-body-secondary"></div>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <div class="row g-2">
            <div class="col-12 col-md-4">
              <label class="form-label small">Pilih Anak</label>
              <select id="select-anak" class="form-select">
                <option value="">-- Pilih Anak --</option>
                @php $seen = []; @endphp
                @foreach($assessments as $a)
                @php $kid = optional($a->anakDidik)->id ?? 0; @endphp
                @if($kid && !isset($seen[$kid]))
                @php $seen[$kid] = true; @endphp
                <option value="{{ $kid }}">{{ $a->anakDidik->nama ?? '-' }} ({{ $a->anakDidik->nis ?? '' }})</option>
                @endif
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label small">Kategori</label>
              <select id="select-kategori" class="form-select">
                <option value="">Semua Kategori</option>
                <option value="bina_diri">Bina Diri</option>
                <option value="akademik">Akademik</option>
                <option value="motorik">Motorik</option>
                <option value="perilaku">Perilaku</option>
                <option value="vokasi">Vokasi</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label small">Program</label>
              <select id="select-program" class="form-select">
                <option value="">-- Pilih Program --</option>
              </select>
              <div id="select-program-help" class="text-body-secondary small mt-1">&nbsp;</div>
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end">
              <button id="btn-tampilkan" class="btn btn-primary w-100">Tampilkan</button>
            </div>
          </div>
        </div>

        <div id="assessment-charts" class="row g-3">Pilih anak untuk menampilkan grafik.</div>
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
<script>
  (function() {
      function loadScript(src) {
        return new Promise((resolve, reject) => {
          if (document.querySelector(`script[src="${src}"]`)) return resolve();
          const s = document.createElement('script');
          s.src = src;
          s.onload = resolve;
          s.onerror = reject;
          document.head.appendChild(s);
        });
      }

      async function renderChartsForAnak(anakId, anakName = null) {
        const container = document.getElementById('assessment-charts');
        const meta = document.getElementById('assessment-charts-meta');
        if (!container) return;
        container.innerHTML = '<div class="text-body-secondary">Memuat grafik...</div>';
        if (meta) meta.textContent = anakName ? anakName : '';
        try {
          await loadScript('https://cdn.jsdelivr.net/npm/chart.js');
          await loadScript('https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2');
          const res = await fetch(`/assessment/${anakId}/program-history`, {
            credentials: 'same-origin'
          });
          const json = await res.json();
          if (!json.success || !Array.isArray(json.programs) || json.programs.length === 0) {
            container.innerHTML = '<div class="text-body-secondary">Belum ada data penilaian untuk program.</div>';
            return;
          }

          // register datalabels if available
          if (window.Chart && window.ChartDataLabels) {
            try {
              Chart.register(ChartDataLabels);
            } catch (e) {}
          }

          container.innerHTML = '';
          // try to match table card height for consistent sizing
          const tableResponsive = document.querySelector('.card .table-responsive');
          const targetHeight = tableResponsive ? Math.max(240, tableResponsive.clientHeight) : 240;
          json.programs.forEach((prog, idx) => {
            const col = document.createElement('div');
            col.className = 'col-12';

            const card = document.createElement('div');
            // match table card styling and spacing
            card.className = 'card mb-4';

            const body = document.createElement('div');
            body.className = 'card-body';
            body.style.minHeight = targetHeight + 'px';

            const title = document.createElement('h6');
            title.className = 'card-title mb-2';
            title.textContent = prog.nama_program || '-';

            const canvasWrap = document.createElement('div');
            const canvasHeight = Math.max(180, targetHeight - 80);
            canvasWrap.style.height = canvasHeight + 'px';
            canvasWrap.style.position = 'relative';

            const canvas = document.createElement('canvas');
            canvas.id = `assessment-chart-${anakId}-${idx}`;
            canvas.style.width = '100%';
            canvas.style.height = canvasHeight + 'px';

            canvasWrap.appendChild(canvas);
            body.appendChild(title);
            body.appendChild(canvasWrap);

            const footer = document.createElement('div');
            footer.className = 'card-footer bg-transparent';
            footer.style.borderTop = 'none';
            const latest = (prog.datapoints && prog.datapoints.length) ? prog.datapoints[prog.datapoints.length - 1].score : null;
            footer.innerHTML = `<small class="text-muted">Terakhir: ${latest !== null ? (Math.round(latest*100)/100) : '-'}</small>`;

            card.appendChild(body);
            card.appendChild(footer);
            col.appendChild(card);
            container.appendChild(col);

            const labels = prog.datapoints.map(d => d.tanggal || '').filter(Boolean);
            const data = prog.datapoints.map(d => d.score !== null ? Number(d.score) : NaN);

            try {
              new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                  labels: labels.map(l => {
                    try {
                      return new Date(l).toLocaleDateString('id-ID', {
                        month: 'short',
                        year: 'numeric'
                      });
                    } catch (e) {
                      return l;
                    }
                  }),
                  datasets: [{
                    label: prog.nama_program,
                    data: data,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.06)',
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981'
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                    x: {
                      display: true,
                      grid: {
                        color: '#f8fafc'
                      },
                      ticks: {
                        maxRotation: 0,
                        autoSkip: true
                      }
                    },
                    y: {
                      display: true,
                      grid: {
                        color: '#f1f5f9'
                      },
                      suggestedMin: 0,
                      suggestedMax: 5
                    }
                  },
                  plugins: {
                    legend: {
                      display: false
                    },
                    tooltip: {
                      enabled: true,
                      mode: 'index',
                      intersect: false
                    },
                    datalabels: {
                      display: true,
                      align: 'top',
                      formatter: function(v) {
                        return isNaN(v) ? '' : Math.round(v * 100) / 100;
                      },
                      backgroundColor: 'rgba(255,255,255,0.95)',
                      borderRadius: 4,
                      color: '#111',
                      font: {
                        size: 11
                      }
                    }
                  }
                }
              });
            } catch (e) {
              console.error('Chart render failed', e);
            }
          });
        } catch (err) {
          console.error('Failed to render charts', err);
          container.innerHTML = '<div class="text-body-secondary">Gagal memuat grafik.</div>';
        }
      }

      // simple in-memory cache for fetched programs per anak
      const _programsCache = {};

      async function fetchProgramsForAnak(anakId) {
        if (!anakId) return [];
        if (_programsCache[anakId]) return _programsCache[anakId];
        try {
          const res = await fetch(`/assessment/${anakId}/program-history`);
          const json = await res.json();
          if (!json.success) return [];
          _programsCache[anakId] = json.programs || [];
          return _programsCache[anakId];
        } catch (e) {
          console.error('Fetch programs failed', e);
          return [];
        }
      }

      // populate program select based on selected anak and category
      async function populateProgramSelect(anakId, kategori) {
        const sel = document.getElementById('select-program');
        const help = document.getElementById('select-program-help');
        sel.innerHTML = '<option value="">Memuat...</option>';
        if (help) help.textContent = '\u00A0';
        if (!anakId) return;

        // Primary: ask server for PPI-derived programs (same as create page)
        try {
          const q = new URLSearchParams({
            anak_didik_id: anakId,
            kategori: kategori || ''
          });
          const res = await fetch(`/assessment/ppi-programs?${q.toString()}`, {
            credentials: 'same-origin'
          });
          if (!res.ok) {
            console.warn('ppi-programs responded with', res.status);
            if (help) help.textContent = res.status === 403 ? 'Akses ditolak (403) — Anda tidak memiliki izin melihat program PPI.' : `Gagal memuat program (status ${res.status}).`;
          } else {
            const j = await res.json().catch(e => null);
            if (j && j.success && Array.isArray(j.programs) && j.programs.length) {
              // populate from PPI programs (these may create Program rows server-side)
              sel.innerHTML = '<option value="">-- Pilih Program --</option>';
              j.programs.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id || p.nama_program;
                opt.textContent = p.nama_program + (p.kategori ? (' — ' + p.kategori) : '');
                opt.dataset.kategori = p.kategori || '';
                sel.appendChild(opt);
              });
              if (help) help.textContent = `PPI: ${j.programs.length} item`;
              // auto-select first and render
              if (sel.options.length > 1) {
                sel.selectedIndex = 1;
                setTimeout(() => renderSelectedProgram(anakId, sel.value), 120);
                return;
              }
            } else {
              if (help) help.textContent = 'PPI: 0 item — memeriksa riwayat penilaian...';
            }
          }
        } catch (e) {
          console.error('ppi-programs request failed', e);
          if (help) help.textContent = 'Gagal memuat program dari PPI.';
        }

        // Fallback: use program-history (programs that have assessments)
        try {
          const historyPrograms = await fetchProgramsForAnak(anakId);
          const normSelKat = kategori ? String(kategori).toLowerCase().replace(/\s+/g, '_') : '';
          const filtered = historyPrograms.filter(p => {
            const pk = p.kategori || '';
            const normPk = String(pk).toLowerCase().replace(/\s+/g, '_');
            if (normSelKat && normPk && normSelKat !== normPk) return false;
            return true;
          });
          if (filtered.length) {
            sel.innerHTML = '<option value="">-- Pilih Program --</option>';
            filtered.forEach(p => {
              const opt = document.createElement('option');
              opt.value = p.id || p.nama_program;
              opt.textContent = p.nama_program + (p.kategori ? (' — ' + p.kategori) : '');
              opt.dataset.kategori = p.kategori || '';
              sel.appendChild(opt);
            });
            if (help) help.textContent = `Riwayat: ${filtered.length} item`;
            sel.selectedIndex = 1;
            setTimeout(() => renderSelectedProgram(anakId, sel.value), 120);
            return;
          } else {
            if (help) help.textContent = 'Tidak ada program ditemukan untuk anak/ kategori ini.';
            sel.innerHTML = '<option value="">(Tidak ada program)</option>';
          }
        } catch (e) {
          console.error('fetchProgramsForAnak failed', e);
          if (help) help.textContent = 'Gagal mengambil riwayat program.';
          sel.innerHTML = '<option value="">(Tidak ada program)</option>';
        }
      }

      // render only selected program for anak
      async function renderSelectedProgram(anakId, programIdOrName) {
        const programs = await fetchProgramsForAnak(anakId);
        const prog = programs.find(p => String(p.id) === String(programIdOrName) || p.nama_program === programIdOrName);
        const container = document.getElementById('assessment-charts');
        const meta = document.getElementById('assessment-charts-meta');
        if (!container) return;
        container.innerHTML = '';
        if (!prog) {
          container.innerHTML = '<div class="text-body-secondary">Program tidak ditemukan.</div>';
          return;
        }
        if (meta) meta.textContent = prog.nama_program || '';

        const col = document.createElement('div');
        col.className = 'col-12';
        const card = document.createElement('div');
        card.className = 'card mb-4';
        const body = document.createElement('div');
        body.className = 'card-body';
        const title = document.createElement('h6');
        title.className = 'card-title mb-2';
        title.textContent = prog.nama_program;
        const canvasWrap = document.createElement('div');
        canvasWrap.style.height = '320px';
        canvasWrap.style.position = 'relative';
        const canvas = document.createElement('canvas');
        canvas.id = `assessment-chart-single-${anakId}`;
        canvas.style.width = '100%';
        canvas.style.height = '320px';
        canvasWrap.appendChild(canvas);
        body.appendChild(title);
        body.appendChild(canvasWrap);
        const footer = document.createElement('div');
        footer.className = 'card-footer bg-transparent';
        footer.style.borderTop = 'none';
        const latest = (prog.datapoints && prog.datapoints.length) ? prog.datapoints[prog.datapoints.length - 1].score : null;
        footer.innerHTML = `<small class="text-muted">Terakhir: ${latest !== null ? (Math.round(latest*100)/100) : '-'}</small>`;
        card.appendChild(body);
        card.appendChild(footer);
        col.appendChild(card);
        container.appendChild(col);

        // draw chart
        try {
          const labels = prog.datapoints.map(d => d.tanggal || '').filter(Boolean).map(l => {
            try {
              return new Date(l).toLocaleDateString('id-ID', {
                month: 'short',
                year: 'numeric'
              });
            } catch (e) {
              return l;
            }
          });
          const data = prog.datapoints.map(d => d.score !== null ? Number(d.score) : NaN);
          if (window.Chart && window.ChartDataLabels) {
            try {
              Chart.register(ChartDataLabels);
            } catch (e) {}
          }
          new Chart(canvas.getContext('2d'), {
              type: 'line',
              data: {
                labels: labels,
                datasets: [{
                  label: prog.nama_program,
                  data: data,
                  borderColor: '#10b981',
                  backgroundColor: 'rgba(16,185,129,0.06)',
                  tension: 0.3,
                  pointRadius: 6,
                  pointBackgroundColor: '#fff',
                  pointBorderColor: '#10b981'
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  x: {
                    grid: {
                      color: '#f8fafc'
                    }
                  },
                  y: {
                    grid: {
                      color: '#f1f5f9'
                    },
                    suggestedMin: 0,
                    suggestedMax: 5
                  }
                },
                plugins: {
                  legend: {
                    display: false
                  },
                  tooltip: {
                    enabled: true
                  },
                  datalabels: {
                    display: true,
                    align: 'top',
                    formatter: function(v) {
                      return isNaN(v) ? '' : Math.round(v * 100) / 100;
                    },
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    borderRadius: 4,
                    color: '#111',
                    font: {
                      size: 11
                    }
                  }
                }
              });
          }
          catch (e) {
            console.error('Chart failed', e);
          }
        }

        // wire up selects
        document.addEventListener('DOMContentLoaded', function() {
          const selAnak = document.getElementById('select-anak');
          const selKategori = document.getElementById('select-kategori');
          const selProgram = document.getElementById('select-program');
          const btn = document.getElementById('btn-tampilkan');

          if (selAnak) {
            selAnak.addEventListener('change', function() {
              const anakId = this.value;
              populateProgramSelect(anakId, selKategori ? selKategori.value : '');
            });
          }
          if (selKategori) {
            selKategori.addEventListener('change', function() {
              const anakId = document.getElementById('select-anak').value;
              populateProgramSelect(anakId, this.value);
            });
          }
          if (btn) {
            btn.addEventListener('click', function(e) {
              e.preventDefault();
              const anakId = document.getElementById('select-anak').value;
              const programVal = document.getElementById('select-program').value;
              if (!anakId || !programVal) {
                alert('Pilih anak dan program terlebih dahulu');
                return;
              }
              renderSelectedProgram(anakId, programVal);
            });
          }

          // expose helper globally
          window.populateProgramSelect = populateProgramSelect;
          window.renderSelectedProgram = renderSelectedProgram;
          // auto-populate if both anak and kategori are preselected
          if (selAnak && selKategori && selAnak.value && selKategori.value) {
            populateProgramSelect(selAnak.value, selKategori.value);
          }
        });

        // Expose renderChartsForAnak for backward compatibility (renders all programs)
        window.renderChartsForAnak = renderChartsForAnak;
      })();
</script>
@endsection