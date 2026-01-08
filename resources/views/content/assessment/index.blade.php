<!-- Modal Riwayat Penilaian -->
<div class="modal fade" id="riwayatObservasiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Riwayat Penilaian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
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
            return 'Basic Learning';
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
          <!-- Tombol tambah penilaian hanya untuk non-admin -->
          @if(auth()->check() && auth()->user()->role !== 'admin')
          <a href="{{ route('assessment.create') }}" class="btn btn-primary d-inline-flex d-sm-none align-items-center justify-content-center p-0" style="width:44px;height:44px;border-radius:12px;min-width:44px;min-height:44px;">
            <i class="ri-add-line" style="font-size:1.7em;"></i>
          </a>
          <a href="{{ route('assessment.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center">
            <i class="ri-add-line me-2"></i>Tambah Penilaian
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
<div class="row">
  <div class="col-12">
    <form method="GET" action="{{ route('assessment.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..." value="{{ request('search') }}">
      </div>

      <!-- Kategori filter removed per request -->

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
              <td>{{ ($assessments->currentPage() - 1) * $assessments->perPage() + $index + 1 }}</td>
              <td>
                <p class="text-heading mb-0 fw-medium">{{ $assessment->anakDidik->nama ?? '-' }}</p>
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
          Menampilkan {{ $assessments->firstItem() ?? 0 }} hingga {{ $assessments->lastItem() ?? 0 }} dari {{ $assessments->total() }} data
        </div>
        <nav>
          {{ $assessments->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Charts Area removed to simplify page (per request) -->

<!-- Modal Grafik Program (ditampilkan saat tombol Lihat ditekan) -->
<div class="modal fade" id="programChartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="programChartTitle">Grafik Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="programChartContainer" style="min-height:240px;">
          <div id="programChartLegend" class="mb-2 d-flex gap-3 align-items-center justify-content-center w-100">
            <div class="d-flex align-items-center gap-2">
              <span style="display:inline-block;width:14px;height:14px;background:#10b981;border-radius:3px;border:1px solid rgba(0,0,0,0.05);"></span>
              <span class="small text-body-secondary">Ada penilaian</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span style="display:inline-block;width:14px;height:14px;background:#ef4444;border-radius:3px;border:1px solid rgba(0,0,0,0.05);"></span>
              <span class="small text-body-secondary">Tidak ada penilaian</span>
            </div>
          </div>
          <canvas id="programChartCanvas" style="width:100%;height:320px" height="320"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

@push('page-script')
<script>
  // dynamic script loader
  function _loadScript(src) {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[src="${src}"]`)) return resolve();
      const s = document.createElement('script');
      s.src = src;
      s.onload = resolve;
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }

  async function renderProgramChart(anakId, programIdOrName) {
    const canvas = document.getElementById('programChartCanvas');
    const titleEl = document.getElementById('programChartTitle');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    try {
      await _loadScript('https://cdn.jsdelivr.net/npm/chart.js');
      await _loadScript('https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2');
    } catch (e) {
      console.error('Failed loading chart scripts', e);
      if (titleEl) titleEl.textContent = 'Gagal memuat Chart.js';
      return;
    }

    // register datalabels if available
    try {
      if (window.Chart && window.ChartDataLabels) Chart.register(ChartDataLabels);
    } catch (e) {}

    // fetch program history
    try {
      const res = await fetch(`/assessment/${anakId}/program-history`, {
        credentials: 'same-origin'
      });
      if (!res.ok) {
        if (titleEl) titleEl.textContent = 'Gagal memuat data program.';
        return;
      }
      const json = await res.json().catch(() => null);
      if (!json || !json.success || !Array.isArray(json.programs)) {
        if (titleEl) titleEl.textContent = 'Tidak ada data program.';
        return;
      }

      const prog = json.programs.find(p => String(p.id) === String(programIdOrName) || p.nama_program === programIdOrName);
      if (!prog) {
        if (titleEl) titleEl.textContent = 'Program tidak ditemukan.';
        return;
      }
      if (titleEl) titleEl.textContent = prog.nama_program || 'Grafik Program';

      // prepare daily buckets between min and max date from datapoints
      const dps = Array.isArray(prog.datapoints) ? prog.datapoints : [];
      if (!dps.length) {
        if (titleEl) titleEl.textContent = prog.nama_program + ' — (Belum ada penilaian)';
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        return;
      }

      // find min and max date among datapoints
      let minDate = null,
        maxDate = null;
      dps.forEach(d => {
        if (!d || !d.tanggal) return;
        const dt = new Date(d.tanggal);
        if (isNaN(dt)) return;
        if (!minDate || dt < minDate) minDate = dt;
        if (!maxDate || dt > maxDate) maxDate = dt;
      });
      if (!minDate || !maxDate) {
        if (titleEl) titleEl.textContent = prog.nama_program + ' — (Belum ada penilaian)';
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        return;
      }

      // build map date(yyyy-mm-dd) -> aggregated avg score
      const dayMap = {};
      dps.forEach(d => {
        if (!d || !d.tanggal) return;
        const dt = new Date(d.tanggal);
        if (isNaN(dt)) return;
        const key = dt.toISOString().slice(0, 10);
        dayMap[key] = dayMap[key] || {
          sum: 0,
          count: 0
        };
        const s = (typeof d.score === 'number') ? d.score : Number(d.score);
        if (!isNaN(s)) {
          dayMap[key].sum += s;
          dayMap[key].count += 1;
        }
      });

      // generate labels & data for each day in range
      const labels = [];
      const data = [];
      const pointBg = [];
      const pointBorder = [];
      const dateList = [];
      for (let dt = new Date(minDate); dt <= maxDate; dt.setDate(dt.getDate() + 1)) {
        const key = dt.toISOString().slice(0, 10);
        dateList.push(new Date(dt));
        labels.push(new Date(key).toLocaleDateString('id-ID', {
          day: '2-digit',
          month: 'short'
        }));
        if (dayMap[key] && dayMap[key].count) {
          const avg = Math.round((dayMap[key].sum / dayMap[key].count) * 100) / 100;
          data.push(avg);
          pointBg.push('#10b981');
          pointBorder.push('#10b981');
        } else {
          // mark missing days as 0 and color red
          data.push(0);
          pointBg.push('#ef4444');
          pointBorder.push('#ef4444');
        }
      }

      if (window._programChartInstance && typeof window._programChartInstance.destroy === 'function') {
        try {
          window._programChartInstance.destroy();
        } catch (e) {}
        window._programChartInstance = null;
      }

      window._programChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: prog.nama_program,
            data: data,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.06)',
            tension: 0.2,
            pointRadius: 6,
            pointBackgroundColor: pointBg,
            pointBorderColor: pointBorder,
            pointStyle: 'circle'
          }]
        },
        options: {
          // render at fixed canvas size to avoid Chart.js triggering continuous resizes
          responsive: false,
          maintainAspectRatio: false,
          animation: false,
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
              suggestedMax: 4,
              ticks: {
                stepSize: 1
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            datalabels: {
              display: true,
              align: 'top',
              anchor: 'end',
              backgroundColor: '#fff',
              borderRadius: 4,
              color: '#111',
              font: {
                size: 11
              },
              formatter: function(v, ctxLabel) {
                // show value for scored days, show '-' for missing (value 0 treated as missing)
                return (v && v !== 0) ? v : (v === 0 ? '' : v);
              }
            }
          }
        }
      });
      // ensure chart uses canvas pixel size correctly
      try {
        window._programChartInstance.resize();
      } catch (e) {}

    } catch (err) {
      console.error('renderProgramChart failed', err);
      if (titleEl) titleEl.textContent = 'Gagal memuat grafik.';
    }
  }

  // override selectProgramAndClose to show chart modal
  window.selectProgramAndClose = function(anakId, programId) {
    try {
      const modalEl = document.getElementById('riwayatObservasiModal');
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
    } catch (e) {}
    try {
      const chartModalEl = document.getElementById('programChartModal');
      const chartModal = new bootstrap.Modal(chartModalEl);
      // render chart after modal is fully shown to ensure correct sizing
      const onShown = function() {
        try {
          renderProgramChart(anakId, programId);
        } catch (err) {
          console.error(err);
        }
        chartModalEl.removeEventListener('shown.bs.modal', onShown);
      };
      chartModalEl.addEventListener('shown.bs.modal', onShown);
      chartModal.show();
    } catch (e) {
      console.error(e);
    }
  };
</script>
@endpush

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
          <div class="col-12">
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

  // formatKategori removed; kategori feature hidden from this view
</script>
<!-- Chart-related scripts removed to declutter view and logic per request -->
@endsection