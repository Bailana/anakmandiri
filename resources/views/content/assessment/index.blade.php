@extends('layouts/contentNavbarLayout')

@section('title', 'Penilaian Anak')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
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
      _a_error('selectProgramAndClose render error', e);
    }
    try {
      const modalEl = document.getElementById('riwayatObservasiModal');
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
    } catch (e) {
      /* ignore */
    }
  };

  // Debug flag: set `window.ASSESSMENT_DEBUG = true` in console to enable logs temporarily
  if (typeof window.ASSESSMENT_DEBUG === 'undefined') window.ASSESSMENT_DEBUG = false;
  window._a_log = function() {
    if (window.ASSESSMENT_DEBUG && console && console.log) console.log.apply(console, arguments);
  };
  window._a_debug = function() {
    if (window.ASSESSMENT_DEBUG && console && console.debug) console.debug.apply(console, arguments);
  };
  window._a_warn = function() {
    if (window.ASSESSMENT_DEBUG && console && console.warn) console.warn.apply(console, arguments);
  };
  window._a_error = function() {
    if (window.ASSESSMENT_DEBUG && console && console.error) console.error.apply(console, arguments);
  };

  // Shared normalizer used across list and chart matching.
  if (typeof window.normalizeProgramName !== 'function') {
    window.normalizeProgramName = function(s) {
      let str = String(s || '').trim();
      if (!str) return '';
      if (str.indexOf(' - ') !== -1) {
        const parts = str.split(' - ');
        const left = parts[0] || '';
        if (/^[A-Za-z0-9\-\.]{1,10}$/.test(left)) {
          str = parts.slice(1).join(' - ').trim();
        }
      }
      return str.toLowerCase();
    };
  }

  window.showRiwayatObservasi = async function(anakDidikId) {
    _a_debug('showRiwayatObservasi called for', anakDidikId);
    let modal;
    try {
      modal = new bootstrap.Modal(document.getElementById('riwayatObservasiModal'));
    } catch (err) {
      _a_warn('Bootstrap Modal constructor failed, trying jQuery fallback', err);
      try {
        if (window.jQuery) {
          $('#riwayatObservasiModal').modal('show');
        }
      } catch (e) {
        _a_error('Failed to show modal via fallback', e);
      }
    }
    const wrapper = document.getElementById('riwayatObservasiTableWrapper');
    wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
    try {
      if (modal && typeof modal.show === 'function') modal.show();
    } catch (e) {
      _a_error('modal.show failed', e);
    }

    // Build modal using assessment program-history as primary source.
    try {
      const data = {
        success: true,
        riwayat: []
      };

      // helpers for kategori: normalize key, display label, and badge class
      const normalizeKey = (k) => String(k || '').toLowerCase().replace(/[\s\-]+/g, '_').replace(/[^a-z0-9_]/g, '');
      // normalize program name for lookups: strip leading "KODE - " if present, trim and lowercase
      const normalizeProgramName = window.normalizeProgramName;
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
      // Fetch per-program assessment history to extract last score per program name
      const scoreIndex = {}; // nameLower -> last score
      const historyProgramKeyByAlnum = {}; // normalized-alnum name -> history key (id:/name:)
      let programHistoryPrograms = [];
      try {
        const phRes = await fetch(`/assessment/${anakDidikId}/program-history`, {
          credentials: 'same-origin'
        });
        if (phRes && phRes.ok) {
          const phJson = await phRes.json().catch(() => null);
          if (phJson && phJson.success && Array.isArray(phJson.programs)) {
            programHistoryPrograms = phJson.programs;
            phJson.programs.forEach(p => {
              const name = normalizeProgramName(p.nama_program || '');
              if (!name) return;
              const alnumKey = String(name).replace(/[^a-z0-9]/g, '');
              if (alnumKey && p.id) historyProgramKeyByAlnum[alnumKey] = String(p.id);
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
        // program-history fetch failed (debug log removed)
      }

      // Fetch programs from current-month lesson plan first (single source of truth)
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

      // Seed groups from assessment history first. This is the most reliable source
      // for programs that were actually scored today.
      if (Array.isArray(programHistoryPrograms) && programHistoryPrograms.length) {
        programHistoryPrograms.forEach(p => {
          const rawKat = p.kategori || 'Lainnya';
          const g = ensureGroup(rawKat);
          const exists = g.items.find(x => (x.id && p.id && x.id == p.id) || (x.nama_program && p.nama_program && x.nama_program === p.nama_program));
          if (!exists) g.items.push(p);
        });
      }

      try {
        const todayIso = new Date().toISOString().slice(0, 10);
        const lpRes = await fetch(`/assessment/ppi-programs?anak_didik_id=${encodeURIComponent(anakDidikId)}&tanggal=${encodeURIComponent(todayIso)}`, {
          credentials: 'same-origin'
        }).then(r => r.json().catch(() => null)).catch(() => null);

        // Create all default kategori groups to keep UI consistent.
        kategoriKeys.forEach(k => ensureGroup(k));

        if (lpRes && lpRes.success && Array.isArray(lpRes.programs) && lpRes.programs.length > 0) {
          lpRes.programs.forEach(p => {
            const rawKat = p.kategori_key || 'Lainnya';
            const g = ensureGroup(rawKat);
            const exists = g.items.find(x => (x.id && p.id && x.id == p.id) || (x.nama_program && p.nama_program && x.nama_program === p.nama_program));
            if (!exists) g.items.push(p);
          });
        }

        // Fallback for legacy data: if LP source returns empty, use legacy category fetch.
        const hasLpPrograms = Object.keys(groupMap).some(k => groupMap[k].items && groupMap[k].items.length);
        if (!hasLpPrograms) {
          const results = await Promise.all(kategoriKeys.map(k => fetch(`/assessment/ppi-programs?anak_didik_id=${encodeURIComponent(anakDidikId)}&kategori=${encodeURIComponent(k)}&include_inactive=1`, {
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
        }


        // Final fallback: if lesson-plan lookup still yields nothing, use assessed programs
        // from program-history so the modal never renders as completely empty.
        const stillEmpty = !Object.keys(groupMap).some(k => groupMap[k].items && groupMap[k].items.length);
        if (stillEmpty && Array.isArray(programHistoryPrograms) && programHistoryPrograms.length) {
          programHistoryPrograms.forEach(p => {
            const rawKat = p.kategori || 'Lainnya';
            const g = ensureGroup(rawKat);
            const exists = g.items.find(x => (x.id && p.id && x.id == p.id) || (x.nama_program && p.nama_program && x.nama_program === p.nama_program));
            if (!exists) {
              g.items.push({
                id: p.id || null,
                nama_program: p.nama_program || '-',
                kategori: rawKat
              });
            }
          });
        }
        const hasAnyProgram = Object.keys(groupMap).some(k => groupMap[k].items && groupMap[k].items.length);
        if (!hasAnyProgram) {
          wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Belum ada riwayat penilaian.</div>';
          return;
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
              const nameKeyLookup = normalizeProgramName(name || '');
              const alnumLookup = String(nameKeyLookup || '').replace(/[^a-z0-9]/g, '');
              const hasScore = scoreIndex && typeof scoreIndex[nameKeyLookup] !== 'undefined';
              const scoreVal = hasScore ? scoreIndex[nameKeyLookup] : null;
              const scoreText = hasScore ? ('Skor: ' + (Number.isInteger(scoreVal) ? scoreVal : (Math.round(scoreVal * 10) / 10))) : null;
              const metaHtml = hasScore ?
                `<div class="text-muted small">${scoreText}</div>` :
                `<span class="badge bg-warning text-dark" title="Tidak ada penilaian pada program ini."><i class="ri-alert-line me-1"></i><span class="d-inline d-sm-none">Tidak ada penilaian</span><span class="d-none d-sm-inline">Tidak ada penilaian pada program ini.</span></span>`;
              const starHtml = (scoreVal == 4) ? `<i class="ri-star-fill me-2" style="color:#f59e0b;font-size:1.1em;flex-shrink:0"></i>` : '';
              const rawId = (p && p.id !== null && typeof p.id !== 'undefined') ? String(p.id).trim() : '';
              const idLooksLikeHistoryKey = rawId.startsWith('id:') || rawId.startsWith('name:');
              const mappedHistoryKey = alnumLookup ? historyProgramKeyByAlnum[alnumLookup] : null;
              const stableProgramKey = idLooksLikeHistoryKey ? rawId : (mappedHistoryKey || ('name:' + normalizeProgramName(p.nama_program || '')));
              const pid = String(stableProgramKey || '').replace(/'/g, "\\'");
              html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-start">
                    ${starHtml}
                    <div>
                      <div class="fw-semibold">${name}</div>
                      ${metaHtml}
                    </div>
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
        _a_error('Failed to load ppi programs', err);
        wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat daftar program.</div>';
      }
    } catch (err) {
      _a_error('Failed to load riwayat penilaian', err);
      wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat data.</div>';
    }
  }

  // Delegate click on riwayat buttons (safe if functions defined later)
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-riwayat');
    if (!btn) return;
    const anakId = btn.dataset.anakId;
    const anakName = btn.dataset.anakName || '';
    _a_debug('btn-riwayat clicked', anakId, anakName);
    if (window.showRiwayatObservasi) {
      try {
        window.showRiwayatObservasi(anakId);
      } catch (err) {
        _a_error('error calling showRiwayatObservasi', err);
      }
    } else {
      _a_warn('showRiwayatObservasi not defined yet');
    }
    if (window.renderChartsForAnak) {
      try {
        window.renderChartsForAnak(anakId, anakName);
      } catch (err) {
        _a_error(err);
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
  <div class="col-12 mb-4">
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
              <th>Wajib Nilai</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assessments as $index => $assessment)
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
                {{ $wajibDoneToday[$assessment->anakDidik->id ?? 0] ?? 0 }}/{{ $wajibTotals[$assessment->anakDidik->id ?? 0] ?? 0 }}
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-info btn-riwayat"
                    data-anak-id="{{ $assessment->anakDidik->id ?? 0 }}"
                    data-anak-name="{{ addslashes($assessment->anakDidik->nama ?? '-') }}"
                    title="Riwayat Penilaian"
                    aria-label="Riwayat Penilaian">
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center py-5">
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
        <div class="mb-3">
          <label class="form-label">Filter Bulan</label>
          <select id="chartMonthFilter" class="form-select">
            <option value="">Semua Bulan</option>
          </select>
        </div>
        <div id="programChartContainer" style="min-height:240px;">
          <!-- Legend removed as requested -->
          <div id="programApexChart" style="width:100%;min-height:320px"></div>
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

  function findProgramFromHistory(programs, programIdOrName) {
    const normalizeProgramName = (window.normalizeProgramName && typeof window.normalizeProgramName === 'function') ? window.normalizeProgramName : function(s) {
      return String(s || '').toLowerCase().trim();
    };
    const normalizeAlnum = s => String(s || '').toLowerCase().replace(/[^a-z0-9]/g, '');

    const raw = String(programIdOrName || '').trim();
    const stripped = raw.replace(/^id:/i, '').replace(/^name:/i, '').trim();
    const nameNorm = normalizeProgramName(stripped);
    const nameAlnum = normalizeAlnum(nameNorm);

    // 1) Direct id-key matching (supports p.id like "id:545" and "name:...")
    let prog = programs.find(p => String(p.id || '').trim() === raw);
    if (prog) return prog;

    // 2) Raw numeric id from UI vs prefixed id from history, and vice versa
    const idCandidates = [
      stripped,
      `id:${stripped}`,
      `name:${nameNorm}`
    ].filter(Boolean);
    prog = programs.find(p => idCandidates.includes(String(p.id || '').trim()));
    if (prog) return prog;

    // 3) Normalized exact name
    prog = programs.find(p => normalizeProgramName(p.nama_program) === nameNorm);
    if (prog) return prog;

    // 4) Contains name match
    prog = programs.find(p => {
      const pNorm = normalizeProgramName(p.nama_program);
      return pNorm.indexOf(nameNorm) !== -1 || nameNorm.indexOf(pNorm) !== -1;
    });
    if (prog) return prog;

    // 5) Alphanumeric fuzzy name match
    if (nameAlnum) {
      prog = programs.find(p => {
        const pAl = normalizeAlnum(p.nama_program || '');
        return pAl === nameAlnum || pAl.indexOf(nameAlnum) !== -1 || nameAlnum.indexOf(pAl) !== -1;
      });
      if (prog) return prog;
    }

    return null;
  }

  async function renderProgramChart(anakId, programIdOrName) {
    const chartEl = document.getElementById('programApexChart');
    const titleEl = document.getElementById('programChartTitle');
    const monthFilterEl = document.getElementById('chartMonthFilter');
    if (!chartEl) return;

    try {
      await _loadScript('https://cdn.jsdelivr.net/npm/apexcharts');
    } catch (e) {
      _a_error('Failed loading ApexCharts', e);
      if (titleEl) titleEl.textContent = 'Gagal memuat grafik.';
      return;
    }

    // Always fetch full program history first. Month filter is applied client-side
    // to avoid stale previous selections affecting newly opened programs.
    try {
      const url = `/assessment/${anakId}/program-history`;
      const res = await fetch(url, {
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

      const prog = findProgramFromHistory(json.programs, programIdOrName);
      if (!prog) {
        try {
          if (window._programApexChartInstance && typeof window._programApexChartInstance.destroy === 'function') {
            window._programApexChartInstance.destroy();
          }
        } catch (e) {}
        if (titleEl) titleEl.textContent = 'Program tidak ditemukan.';
        return;
      }
      if (titleEl) titleEl.textContent = prog.nama_program || 'Grafik Program';

      const dps = Array.isArray(prog.datapoints) ? prog.datapoints : [];
      if (!dps.length) {
        if (titleEl) titleEl.textContent = prog.nama_program + ' — (Belum ada penilaian)';
        // clear previous chart if any
        try {
          if (window._programApexChartInstance && typeof window._programApexChartInstance.destroy === 'function') window._programApexChartInstance.destroy();
        } catch (e) {}
        return;
      }

      // Store data globally for filter usage
      window._currentProgramData = {
        anakId: anakId,
        programIdOrName: programIdOrName,
        prog: prog,
        datapoints: dps
      };

      // Populate month filter dropdown
      const monthSet = new Set();
      dps.forEach(d => {
        if (!d || !d.tanggal) return;
        const dt = new Date(d.tanggal);
        if (isNaN(dt)) return;
        const monthKey = dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0');
        monthSet.add(monthKey);
      });

      const months = Array.from(monthSet).sort();
      if (monthFilterEl) {
        monthFilterEl.innerHTML = '<option value="">Semua Bulan</option>';
        months.forEach(m => {
          const [year, month] = m.split('-');
          const monthName = new Date(year, parseInt(month) - 1, 1).toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric'
          });
          const opt = document.createElement('option');
          opt.value = m;
          opt.textContent = monthName;
          monthFilterEl.appendChild(opt);
        });

        // Set default to current month if available
        const now = new Date();
        const currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
        if (months.includes(currentMonth)) {
          monthFilterEl.value = currentMonth;
        } else if (months.length > 0) {
          monthFilterEl.value = months[months.length - 1]; // latest month
        }

        // Add event listener for filter change
        monthFilterEl.onchange = function() {
          renderChartWithFilter(monthFilterEl.value);
        };
      }

      // Render with default filter (current/latest month)
      renderChartWithFilter(monthFilterEl ? monthFilterEl.value : '');

    } catch (err) {
      _a_error('renderProgramChart failed', err);
      if (titleEl) titleEl.textContent = 'Gagal memuat grafik.';
    }
  }

  function renderChartWithFilter(selectedMonth) {
    if (!window._currentProgramData) return;

    const {
      prog,
      datapoints
    } = window._currentProgramData;
    const titleEl = document.getElementById('programChartTitle');
    const chartEl = document.getElementById('programApexChart');

    if (!chartEl) return;

    // Filter datapoints by selected month
    let filteredDps = datapoints;
    if (selectedMonth) {
      const [year, month] = selectedMonth.split('-');
      filteredDps = datapoints.filter(d => {
        if (!d || !d.tanggal) return false;
        const dt = new Date(d.tanggal);
        if (isNaN(dt)) return false;
        return dt.getFullYear() === parseInt(year) && (dt.getMonth() + 1) === parseInt(month);
      });
    }

    if (!filteredDps.length) {
      if (titleEl) titleEl.textContent = prog.nama_program + ' — (Tidak ada data untuk periode ini)';
      try {
        if (window._programApexChartInstance && typeof window._programApexChartInstance.destroy === 'function') {
          window._programApexChartInstance.destroy();
        }
      } catch (e) {}
      return;
    }

    // find min/max dates
    let minDate = null,
      maxDate = null;
    filteredDps.forEach(d => {
      if (!d || !d.tanggal) return;
      const dt = new Date(d.tanggal);
      if (isNaN(dt)) return;
      if (!minDate || dt < minDate) minDate = dt;
      if (!maxDate || dt > maxDate) maxDate = dt;
    });

    if (!minDate || !maxDate) {
      if (titleEl) titleEl.textContent = prog.nama_program + ' — (Tidak ada data untuk periode ini)';
      return;
    }

    // Aggregate per day (for monthly view)
    const dayMap = {};
    filteredDps.forEach(d => {
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

    const labels = [];
    const seriesData = [];
    for (let dt = new Date(minDate); dt <= maxDate; dt.setDate(dt.getDate() + 1)) {
      const key = dt.toISOString().slice(0, 10);
      labels.push(new Date(key).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short'
      }));
      if (dayMap[key] && dayMap[key].count) {
        const avg = Math.round((dayMap[key].sum / dayMap[key].count) * 100) / 100;
        seriesData.push(avg);
      } else {
        seriesData.push(0);
      }
    }

    // destroy previous instance
    try {
      if (window._programApexChartInstance && typeof window._programApexChartInstance.destroy === 'function') {
        window._programApexChartInstance.destroy();
      }
    } catch (e) {}

    const options = {
      series: [{
        name: prog.nama_program || 'Program',
        data: seriesData
      }],
      chart: {
        type: 'line',
        height: 320,
        toolbar: {
          show: true
        },
        zoom: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      markers: {
        size: 5
      },
      colors: ['#10b981'],
      dataLabels: {
        enabled: true
      },
      xaxis: {
        categories: labels,
        labels: {
          rotate: -45
        }
      },
      yaxis: {
        min: 0,
        max: 4,
        tickAmount: 4
      },
      grid: {
        borderColor: '#f1f5f9'
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val;
          }
        }
      }
    };

    window._programApexChartInstance = new ApexCharts(chartEl, options);
    window._programApexChartInstance.render();
  }

  // small helper to show Bootstrap toasts programmatically
  function showBootstrapToast(message, opts = {}) {
    try {
      const containerId = 'bootstrap-toast-container';
      let container = document.getElementById(containerId);
      if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.style.position = 'fixed';
        container.style.zIndex = 10800;
        container.style.top = '1rem';
        container.style.right = '1rem';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '0.5rem';
        container.style.pointerEvents = 'none';
        document.body.appendChild(container);
      }

      const variant = opts.variant || 'primary';
      const delay = typeof opts.delay === 'number' ? opts.delay : 4000;

      const toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center border-0';
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('aria-live', 'assertive');
      toastEl.setAttribute('aria-atomic', 'true');
      toastEl.style.pointerEvents = 'auto';
      toastEl.setAttribute('data-bs-autohide', String(opts.autohide === false ? 'false' : 'true'));
      toastEl.setAttribute('data-bs-delay', String(delay));

      // variant classes
      let variantClass = 'bg-primary text-white';
      if (variant === 'warning') variantClass = 'bg-warning text-dark';
      else if (variant === 'success') variantClass = 'bg-success text-white';
      else if (variant === 'danger') variantClass = 'bg-danger text-white';
      else if (variant === 'info') variantClass = 'bg-info text-dark';

      toastEl.innerHTML = `
        <div class="d-flex ${variantClass}">
          <div class="toast-body">${String(message)}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;

      container.appendChild(toastEl);
      const b = new bootstrap.Toast(toastEl);
      b.show();
      toastEl.addEventListener('hidden.bs.toast', function() {
        try {
          toastEl.remove();
        } catch (e) {}
        if (container && !container.querySelector('.toast')) {
          try {
            container.remove();
          } catch (e) {}
        }
      });
      return b;
    } catch (err) {
      _a_debug('showBootstrapToast failed', err);
      return null;
    }
  }



  // override selectProgramAndClose to show chart modal (but only if there are datapoints)
  window.selectProgramAndClose = async function(anakId, programId) {
    // Pre-check: fetch program history and ensure the selected program has datapoints
    try {
      const res = await fetch(`/assessment/${anakId}/program-history`, {
        credentials: 'same-origin'
      });
      if (res && res.ok) {
        const json = await res.json().catch(() => null);
        const programs = json && Array.isArray(json.programs) ? json.programs : [];
        const prog = findProgramFromHistory(programs, programId);
        // debug logs removed
        if (prog && (!Array.isArray(prog.datapoints) || prog.datapoints.length === 0)) {
          // show Bootstrap toast (fallback for toastr/alert)
          if (typeof showBootstrapToast === 'function') {
            showBootstrapToast('Belum ada penilaian pada program ini.', {
              variant: 'warning',
              delay: 3500
            });
          } else if (window.toastr && typeof window.toastr.info === 'function') {
            window.toastr.info('Belum ada penilaian pada program ini.');
          } else {
            alert('Belum ada penilaian pada program ini.');
          }
          return;
        }
      }
    } catch (e) {
      // program-history pre-check failed (debug log removed)
      // On error, continue to show modal so the existing chart error handling can run
    }
    try {
      // hide the riwayat modal now that we're proceeding to show the chart
      try {
        const modalEl = document.getElementById('riwayatObservasiModal');
        const inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
      } catch (e) {}

      const chartModalEl = document.getElementById('programChartModal');
      const chartModal = new bootstrap.Modal(chartModalEl);
      // render chart after modal is fully shown to ensure correct sizing
      const onShown = function() {
        try {
          renderProgramChart(anakId, programId);
        } catch (err) {
          _a_error(err);
        }
        chartModalEl.removeEventListener('shown.bs.modal', onShown);
      };
      // when the chart modal is closed, reopen the riwayat modal
      const onHidden = function() {
        try {
          const riwayatEl = document.getElementById('riwayatObservasiModal');
          if (riwayatEl) {
            const riwayatModal = new bootstrap.Modal(riwayatEl);
            riwayatModal.show();
          }
        } catch (e) {}
        chartModalEl.removeEventListener('hidden.bs.modal', onHidden);
      };
      chartModalEl.addEventListener('shown.bs.modal', onShown);
      chartModalEl.addEventListener('hidden.bs.modal', onHidden);
      chartModal.show();
    } catch (e) {
      _a_error(e);
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