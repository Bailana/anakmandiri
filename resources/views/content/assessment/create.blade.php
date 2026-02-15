@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Penilaian')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
<style>
  /* Make select and date inputs visually match height on desktop in this form */
  @media (min-width: 768px) {

    .assessment-form .form-select,
    .assessment-form input[type="date"],
    .assessment-form .form-control {
      box-sizing: border-box;
      height: 46px;
      /* normalize height */
      padding: .5rem .75rem;
      line-height: 1.2;
      width: 100%;
    }

    .assessment-form .form-select {
      padding-left: .75rem
    }

    .assessment-form .form-text {
      margin-top: .35rem
    }
  }
</style>
<style>
  /* keep small styling for badges but let bootstrap/bg-* control colors */
  .kategori-badge {
    border-radius: 6px;
    padding: .28rem .5rem;
    font-weight: 600;
    font-size: .85rem;
  }
</style>

<script>
  // Colorize kategori badges inside #programs_list using explicit mapping with fallback
  (function() {
    const paletteCount = 8;

    function hashString(str) {
      let h = 2166136261 >>> 0;
      for (let i = 0; i < str.length; i++) {
        h ^= str.charCodeAt(i);
        h = Math.imul(h, 16777619) >>> 0;
      }
      return h;
    }

    // Map kategori to the same bootstrap classes used in the Riwayat PPI modal
    function pickClassForKategori(rawText) {
      const s = (rawText || '').toLowerCase();
      if (s.includes('bina')) return 'bg-success'; // Bina Diri -> green
      if (s.includes('akademik')) return 'bg-primary'; // Akademik -> primary (blue)
      if (s.includes('motorik')) return 'bg-info text-dark'; // Motorik -> info (light) with dark text
      if (s.includes('perilaku') || s.includes('basic')) return 'bg-warning text-dark'; // Basic Learning -> warning (yellow)
      if (s.includes('vokasi')) return 'bg-secondary';
      return null;
    }

    function colorizeKategoriBadges(container) {
      const badges = (container || document).querySelectorAll('#programs_list .badge');
      badges.forEach(b => {
        const text = (b.textContent || '').trim();
        if (!text) return;
        // pick explicit class first (bootstrap classes), otherwise fallback to deterministic palette
        let cls = pickClassForKategori(text);
        if (cls) {
          // remove previous kategori-related classes and conflicting bg-* classes
          Array.from(b.classList).forEach(c => {
            if (c.startsWith('kategori-color-') || c.startsWith('kategori-') || c.startsWith('bg-') || c === 'text-dark' || c === 'text-white') b.classList.remove(c);
          });
          // ensure base badge class remains and add chosen classes
          b.classList.add('badge', 'kategori-badge');
          cls.split(/\s+/).forEach(c => b.classList.add(c));
        } else {
          const idx = Math.abs(hashString(text)) % paletteCount;
          const fallback = 'kategori-color-' + idx;
          Array.from(b.classList).forEach(c => {
            if (c.startsWith('kategori-color-') || c.startsWith('kategori-')) b.classList.remove(c);
          });
          b.classList.add('kategori-badge', fallback);
        }
      });
    }

    // Observe programs list for new content and colorize when it changes
    const programsListEl = document.getElementById('programs_list');
    if (programsListEl) {
      const mo = new MutationObserver(() => colorizeKategoriBadges(programsListEl));
      mo.observe(programsListEl, {
        childList: true,
        subtree: true
      });
      // initial run in case content already present
      colorizeKategoriBadges(programsListEl);
    }
  })();
</script>

@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Penilaian Anak</h5>
        <a href="{{ route('assessment.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form class="assessment-form" action="{{ route('assessment.store') }}" method="POST">
          @csrf


          <div class="row mb-3 g-3 align-items-end">
            <div class="col-12 col-md-8">
              <label class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select name="anak_didik_id" class="form-select @error('anak_didik_id') is-invalid @enderror" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                @php
                $statusAbsen = $absensiHariIni[$anak->id] ?? ['boleh_dinilai' => false, 'status' => null, 'sudah_absen' => false];
                $bolehDinilai = $statusAbsen['boleh_dinilai'];
                @endphp
                @if($bolehDinilai)
                <option value="{{ $anak->id }}" {{ old('anak_didik_id') == $anak->id ? 'selected' : '' }}>
                  {{ $anak->nama }}
                </option>
                @endif
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror

            </div>

            <div class="col-12 col-md-4">
              <label class="form-label">Tanggal Penilaian <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_assessment" required class="form-control @error('tanggal_assessment') is-invalid @enderror"
                value="{{ old('tanggal_assessment') }}">
              @error('tanggal_assessment')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3 g-3 align-items-end">
            @if(!(auth()->user() && auth()->user()->role === 'guru'))
            <div class="col-md-4">
              <label class="form-label">Konsultan <span class="text-danger">*</span></label>
              <select name="konsultan_id" class="form-select @error('konsultan_id') is-invalid @enderror" required>
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" {{ old('konsultan_id') == $konsultan->id ? 'selected' : '' }}>
                  {{ $konsultan->nama }} ({{ $konsultan->spesialisasi }})
                </option>
                @endforeach
              </select>
              @error('konsultan_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            @endif
            <div class="col-12">
              <label class="form-label">Daftar Program Aktif</label>
              <div id="programs_list" class="table-responsive">
                {{-- Akan diisi via JS ketika anak dipilih --}}
                <div class="alert alert-warning rounded-3 d-flex align-items-center mb-0" role="alert">
                  <i class="ri-error-warning-line me-2" style="font-size:1.1rem"></i>
                  <div class="mb-0">Pilih anak untuk menampilkan program aktif.</div>
                </div>
              </div>
            </div>
            {{-- dropdown perkembangan utama dihilangkan; tiap program punya dropdown sendiri --}}
          </div>




          @if(!(auth()->user() && auth()->user()->role === 'guru'))
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Hasil Penilaian</label>
              <textarea name="hasil_penilaian" class="form-control @error('hasil_penilaian') is-invalid @enderror"
                rows="4" placeholder="Deskripsikan hasil penilaian yang telah dilakukan">{{ old('hasil_penilaian') }}</textarea>
              @error('hasil_penilaian')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Rekomendasi</label>
              <textarea name="rekomendasi" class="form-control @error('rekomendasi') is-invalid @enderror"
                rows="3" placeholder="Berikan rekomendasi berdasarkan hasil penilaian">{{ old('rekomendasi') }}</textarea>
              @error('rekomendasi')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Saran</label>
              <textarea name="saran" class="form-control @error('saran') is-invalid @enderror"
                rows="3" placeholder="Berikan saran untuk orang tua/wali dan guru">{{ old('saran') }}</textarea>
              @error('saran')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          @endif

          <div class="row">
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-2"></i>Simpan
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const anakEl = document.querySelector('select[name="anak_didik_id"]');
    const kategoriEl = document.querySelector('select[name="kategori"]');
    const programsListEl = document.getElementById('programs_list');

    const kategoriKeys = ['bina_diri', 'akademik', 'motorik', 'perilaku', 'vokasi'];

    function clearSelection() {
      // clear active styling
      programsListEl.querySelectorAll('.list-group-item.active').forEach(r => r.classList.remove('active'));
    }

    function renderPrograms(programs) {
      if (!programsListEl) return;
      if (!programs || programs.length === 0) {
        programsListEl.innerHTML = '<div class="text-muted">(Tidak ada program aktif untuk anak ini)</div>';
        return;
      }

      // Group programs by kategori_key
      const groups = {};
      programs.forEach(p => {
        const key = p.kategori_key || 'lainnya';
        if (!groups[key]) groups[key] = {
          label: p.kategori_label || (p.kategori_key || 'Lainnya'),
          items: []
        };
        groups[key].items.push(p);
      });

      // badge map same as before
      const badgeMap = {
        'bina_diri': 'bg-success',
        'akademik': 'bg-primary',
        'motorik': 'bg-warning text-white',
        'perilaku': 'bg-danger text-white',
        'vokasi': 'bg-secondary'
      };

      let html = '';
      let globalIdx = 0;
      Object.keys(groups).forEach(k => {
        const g = groups[k];
        const badgeClass = badgeMap[k] || 'bg-dark text-white';

        html += `<div class="mb-3">`;
        // show only badge as group title (no text label)
        html += `<div class="d-flex align-items-center mb-2">`;
        html += `<span class="badge ${badgeClass} kategori-badge text-capitalize">${g.label}</span>`;
        html += `</div>`;

        // ensure list numbers are visible (enough left padding and outside position)
        html += `<ol class="mb-0" style="margin:0;padding-left:1.6rem;list-style-position:outside;">`;
        g.items.forEach(p => {
          const pid = p.id === null ? '' : p.id;
          html += `<li class="mb-3">`;
          html += `<div><strong>${p.nama_program}</strong></div>`;
          html += `<div class="mt-1 w-100">`;
          html += `<select class="form-select form-select-sm" name="programs[${globalIdx}][perkembangan]">`;
          html += `<option value="">Pilih Penilaian</option>`;
          html += `<option value="1">1 - Ada perkembangan 25%</option>`;
          html += `<option value="2">2 - Ada perkembangan 50%</option>`;
          html += `<option value="3">3 - Ada perkembangan 75%</option>`;
          html += `<option value="4">4 - Ada perkembangan 100%</option>`;
          html += `</select>`;
          html += `<input type="hidden" name="programs[${globalIdx}][program_id]" value="${pid}">`;
          html += `<input type="hidden" name="programs[${globalIdx}][kategori]" value="${k}">`;
          html += `</div>`;
          html += `</li>`;
          globalIdx++;
        });
        html += `</ol>`;
        html += `</div>`;
      });

      programsListEl.innerHTML = html;
    }

    function loadProgramsForAnak(anakId) {
      const tanggalEl = document.querySelector('input[name="tanggal_assessment"]');
      const tanggal = tanggalEl ? tanggalEl.value : '';
      if (!anakId || !tanggal) {
        programsListEl.innerHTML = '<div class="alert alert-warning rounded-3 d-flex align-items-center mb-0" role="alert"><i class="ri-error-warning-line me-2" style="font-size:1.1rem"></i><div class="mb-0">Pilih anak dan tanggal penilaian untuk menampilkan program aktif.</div></div>';
        clearSelection();
        return;
      }

      const promises = kategoriKeys.map(k => {
        let url = `/assessment/ppi-programs?anak_didik_id=${encodeURIComponent(anakId)}&kategori=${encodeURIComponent(k)}`;
        if (tanggal) url += `&tanggal=${encodeURIComponent(tanggal)}`;
        return fetch(url, {
            credentials: 'same-origin'
          })
          .then(r => r.json().catch(() => null))
          .then(j => ({
            key: k,
            data: j
          }))
          .catch(() => ({
            key: k,
            data: null
          }));
      });

      programsListEl.innerHTML = '<div class="text-muted">Memuat program...</div>';
      Promise.all(promises).then(results => {
        let aggregated = [];
        results.forEach(res => {
          if (!res || !res.data || !res.data.success || !Array.isArray(res.data.programs)) return;
          res.data.programs.forEach(p => {
            aggregated.push({
              id: p.id,
              nama_program: p.nama_program,
              kategori_key: res.key,
              kategori_label: (res.key === 'perilaku' ? 'Basic Learning' : (res.key.replace('_', ' ') || res.key))
            });
          });
        });
        renderPrograms(aggregated);
        clearSelection();
      }).catch(err => {
        console.error('Failed to load programs', err);
        programsListEl.innerHTML = '<div class="text-danger">Gagal memuat program.</div>';
      });
    }

    if (anakEl) anakEl.addEventListener('change', function() {
      loadProgramsForAnak(this.value);
    });

    if (kategoriEl) kategoriEl.addEventListener('change', function() {
      clearSelection();
    });

    const tanggalEl = document.querySelector('input[name="tanggal_assessment"]');
    if (tanggalEl) tanggalEl.addEventListener('change', function() {
      const anak = anakEl ? anakEl.value : '';
      if (anak) loadProgramsForAnak(anak);
      else programsListEl.innerHTML = '<div class="alert alert-warning rounded-3 d-flex align-items-center mb-0" role="alert"><i class="ri-error-warning-line me-2" style="font-size:1.1rem"></i><div class="mb-0">Pilih anak dan tanggal penilaian untuk menampilkan program aktif.</div></div>';
    });

    // initial load only when both anak and tanggal are present
    if (anakEl && anakEl.value && tanggalEl && tanggalEl.value) {
      loadProgramsForAnak(anakEl.value);
    }

    // Validation: require setiap program dinilai sebelum submit
    const form = document.querySelector('form.assessment-form');
    if (form) {
      // remove validation state when a program select changes
      if (programsListEl) {
        programsListEl.addEventListener('change', function(e) {
          const target = e.target;
          if (target && target.matches && target.matches('select[name$="[perkembangan]"]')) {
            target.classList.toggle('is-invalid', !target.value);
            // remove global alert if all selects now valid
            const remaining = form.querySelectorAll('select[name$="[perkembangan]"]');
            const anyEmpty = Array.from(remaining).some(s => !s.value);
            const alertEl = document.getElementById('programsValidationAlert');
            if (!anyEmpty && alertEl) alertEl.remove();
          }
        });
      }

      form.addEventListener('submit', function(e) {
        const selects = form.querySelectorAll('select[name$="[perkembangan]"]');
        if (!selects || selects.length === 0) return; // nothing to validate
        let firstInvalid = null;
        selects.forEach(s => s.classList.remove('is-invalid'));
        selects.forEach(s => {
          if (!s.value) {
            s.classList.add('is-invalid');
            if (!firstInvalid) firstInvalid = s;
          }
        });
        if (firstInvalid) {
          e.preventDefault();
          // show alert at top of form (create if missing)
          let alertEl = document.getElementById('programsValidationAlert');
          if (!alertEl) {
            alertEl = document.createElement('div');
            alertEl.id = 'programsValidationAlert';
            alertEl.className = 'alert alert-danger';
            alertEl.innerText = 'Semua program harus dinilai. Isi semua pilihan Penilaian sebelum menyimpan.';
            const cardBody = document.querySelector('.card-body');
            if (cardBody) cardBody.insertBefore(alertEl, cardBody.firstChild);
          }
          firstInvalid.focus();
          firstInvalid.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
          return false;
        } else {
          const a = document.getElementById('programsValidationAlert');
          if (a) a.remove();
        }
      });
    }
  });
</script>
@endpush