@extends('layouts/contentNavbarLayout')

@section('title', 'Rapor Anak')

@section('content')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

@push('head')
<style>
  /* Reduce table header height in the Create Rapor modal */
  #programGroupsContainer .table thead th {
    padding: 4px 8px !important;
    font-size: 12px !important;
    line-height: 1.05 !important;
    text-transform: none !important;
    font-weight: 600 !important;
  }

  #programGroupsContainer .program-group .table thead th {
    padding-top: 4px;
    padding-bottom: 4px;
  }

  #programGroupsContainer .table tbody td {
    padding: 6px 10px !important;
  }

  /* ensure small textarea minimum height doesn't enlarge row excessively */
  #programGroupsContainer .program-note {
    min-height: 60px !important;
  }
</style>
@endpush

<!-- Header Section -->
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
          <div>
            <h4 class="mb-1">Rapor Anak</h4>
            <p class="text-body-secondary mb-0">Buat rapor berdasarkan penilaian program yang telah dilakukan untuk anak didik fokus Anda.</p>
          </div>
          @if(!isset($isConsultantEducation) || !$isConsultantEducation)
          <div class="align-self-md-center">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#buatRaporModal">
              <i class="ri-add-line me-2"></i>Buat Rapor
            </button>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter -->
<div class="row">
  <div class="col-12 mb-4">
    <form method="GET" action="{{ route('rapor-anak.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <!-- Search Field -->
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau kelas..." value="{{ request('search') }}">
      </div>

      <!-- Action Buttons -->
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('rapor-anak.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<!-- Tabel Rapor yang Sudah Dibuat -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Nama Guru Fokus</th>
              <th>Nama Orang Tua</th>
              <th>Umur Anak</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="raporTableBody">
            <tr>
              <td colspan="6" class="text-center text-body-secondary py-4">Belum ada rapor yang dibuat.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="buatRaporModal" tabindex="-1" aria-labelledby="buatRaporModalLabel" aria-hidden="true" data-is-consultant-education="{{ isset($isConsultantEducation) && $isConsultantEducation ? '1' : '0' }}">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="buatRaporModalLabel">Buat Rapor</h5>
          <p class="text-body-secondary mb-0 small">Pilih anak didik dan tentukan semester untuk menampilkan program yang dapat dicatat pada rapor.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <label for="anak_didik_id" class="form-label">Nama Anak Didik <span class="text-danger">*</span></label>
            <select id="anak_didik_id" class="form-select">
              @if($children->isNotEmpty())
              <option value="">Pilih Anak Didik</option>
              @foreach($children as $child)
              <option value="{{ $child->id }}">{{ $child->nama }}</option>
              @endforeach
              @else
              <option value="">Belum ada anak didik tersedia</option>
              @endif
            </select>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <label for="kelas" class="form-label">Kelas <span class="text-danger">*</span></label>
            <select id="kelas" class="form-select">
              <option value="">Pilih Kelas</option>
              <option value="Kemandirian I">Kemandirian I</option>
              <option value="Kemandirian II">Kemandirian II</option>
              <option value="Transisi">Transisi</option>
              <option value="SMP">SMP</option>
              <option value="Inklusi">Inklusi</option>
              <option value="Vokasi">Vokasi</option>
            </select>
          </div>
          <div class="col-12 col-md-6 col-lg-3">
            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
            <select id="semester" class="form-select">
              <option value="I">Semester I</option>
              <option value="II">Semester II</option>
            </select>
          </div>
          <div class="col-12">
            <label for="tahun_pelajaran" class="form-label">Tahun Pelajaran <span class="text-danger">*</span></label>
            <input id="tahun_pelajaran" type="text" class="form-control" placeholder="Contoh: 2025/2026">
            <div class="form-text">Gunakan format tahun akademik, misalnya <strong>2025/2026</strong>.</div>
          </div>
        </div>

        <div id="semesterInfo" class="alert alert-light border mt-3 mb-0 d-none"></div>

        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h6 class="mb-0">Program Semester</h6>
              <p class="text-body-secondary mb-0 small" id="programSummary">Pilih anak didik untuk memuat daftar program.</p>
            </div>
          </div>

          <div id="programGroupsContainer">
            <div class="text-center text-body-secondary py-4" id="programPlaceholder">Belum ada program yang dimuat.</div>
          </div>

          <div class="mt-4 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <h6 class="mb-0">Program Kustom</h6>
                <p class="text-body-secondary mb-0 small">Tambahkan program sendiri jika belum tersedia pada daftar semester.</p>
              </div>
              <button type="button" class="btn btn-sm btn-success" id="btnTambahProgramGlobal">
                <i class="ri-add-line me-1"></i>Tambah Program Kustom
              </button>
            </div>
            <div id="customProgramsContainer"></div>
          </div>

          <div class="row mt-3 g-3">
            <div class="col-12" id="therapyNotesContainer" style="display:none;">
              <div id="therapyNotesFields"></div>
            </div>
            <div class="col-12 col-md-6">
              <label for="saran_guru" class="form-label">Saran Guru</label>
              <textarea id="saran_guru" class="form-control" rows="4" placeholder="Masukkan saran dari guru..."></textarea>
            </div>
            <div class="col-12 col-md-6">
              <label for="saran_orang_tua" class="form-label">Saran Orang Tua</label>
              <textarea id="saran_orang_tua" class="form-control" rows="4" placeholder="Masukkan saran dari orang tua..."></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="btnSimpanRapor">
          <i class="ri-save-line me-2"></i>Simpan Rapor
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Riwayat Rapor -->
<div class="modal fade" id="riwayatRaporModal" tabindex="-1" aria-labelledby="riwayatRaporModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="riwayatRaporModalLabel">Riwayat Rapor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatRaporContent" style="max-height: 400px; overflow-y: auto;">
          <div class="text-center text-body-secondary py-4">Memuat data...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Rapor -->
<div class="modal fade" id="detailRaporModal" tabindex="-1" aria-labelledby="detailRaporModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailRaporModalLabel">Detail Rapor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="detailRaporContent">
          <div class="text-center text-body-secondary py-4">Memuat data...</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        <a id="detailRaporPdfBtn" href="#" target="_blank" class="btn btn-outline-danger">
          <i class="ri-file-pdf-line me-1"></i>Export PDF
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal Riwayat Rapor -->
<div class="modal fade" id="riwayatRaporModal" tabindex="-1" aria-labelledby="riwayatRaporModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="riwayatRaporModalLabel">Riwayat Rapor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="riwayatRaporContent">
          <div class="text-center text-body-secondary py-4">Memuat data...</div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<!-- jQuery (must load before Toastr) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  // Global variables for edit mode tracking
  let isEditMode = false;
  let editingRaporId = null;
  let isConsultantEducation = document.getElementById('buatRaporModal')?.dataset.isConsultantEducation === '1';
  let createRaporModalIsDirty = false;
  let createRaporModalAllowClose = false;

  // Configure toastr
  toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  };

  // Safe toastr wrapper (global)
  window.showToastr = function(type, message, title) {
    if (typeof toastr !== 'undefined' && toastr[type]) {
      toastr[type](message, title);
    } else {
      console.warn(`Toastr not available, using fallback: ${type} - ${message}`);
      alert(`${title || 'Notifikasi'}: ${message}`);
    }
  };

  // Helper function untuk get badge class berdasarkan nilai huruf
  function getBadgeClass(nilaiHuruf) {
    switch (nilaiHuruf) {
      case 'A':
        return 'bg-label-success'; // Hijau
      case 'B':
        return 'bg-label-info'; // Biru muda
      case 'C':
        return 'bg-label-warning'; // Kuning
      case 'D':
        return 'bg-label-danger'; // Merah
      default:
        return 'bg-label-secondary'; // Abu-abu
    }
  }

  (function() {
    const raporDataUrl = "{{ route('rapor-anak.data') }}";
    const modalEl = document.getElementById('buatRaporModal');
    const anakSelect = document.getElementById('anak_didik_id');
    const semesterSelect = document.getElementById('semester');
    const tahunInput = document.getElementById('tahun_pelajaran');
    const semesterInfo = document.getElementById('semesterInfo');
    const programSummary = document.getElementById('programSummary');
    const programGroupsContainer = document.getElementById('programGroupsContainer');
    const programPlaceholder = document.getElementById('programPlaceholder');
    const customProgramsContainer = document.getElementById('customProgramsContainer');
    const btnTambahKategori = document.getElementById('btnTambahKategori');
    const btnTambahProgramGlobal = document.getElementById('btnTambahProgramGlobal');

    function setCreateRaporModalDirty(isDirty) {
      createRaporModalIsDirty = Boolean(isDirty);
    }

    function resetCreateRaporModalState() {
      setCreateRaporModalDirty(false);
      createRaporModalAllowClose = false;
    }

    function getDefaultSemester() {
      const month = new Date().getMonth() + 1;
      return month >= 7 ? 'I' : 'II';
    }

    function getDefaultAcademicYear() {
      const currentYear = new Date().getFullYear();
      const month = new Date().getMonth() + 1;
      return month >= 7 ? `${currentYear}/${currentYear + 1}` : `${currentYear - 1}/${currentYear}`;
    }

    function setPlaceholderState(message) {
      if (programPlaceholder) programPlaceholder.textContent = message;
      programGroupsContainer.innerHTML = `<div class="text-center text-body-secondary py-4">${message}</div>`;
    }

    function escapeHtml(value) {
      return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function normalizeProgramName(value) {
      return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function detectTherapyTypesFromText(raw) {
      const text = String(raw || '').toLowerCase();
      const types = [];

      // Raw fields may contain combined values like "SI | TW", "TW, Perilaku", or "SI TW".
      if (text.match(/(?:\b|[^a-z])(si|senso\s*-?motor\s*integrasi|sensori\s*integrasi)(?:\b|[^a-z])/)) {
        types.push('si');
      }
      if (text.match(/(?:\b|[^a-z])(wicara|tw|terapi\s*wicara|terapi\s*tw)(?:\b|[^a-z])/)) {
        types.push('wicara');
      }
      if (text.match(/(?:\b|[^a-z])(perilaku|tp|terapi\s*perilaku|terapi\s*tp)(?:\b|[^a-z])/)) {
        types.push('perilaku');
      }

      return types;
    }

    function getTherapyLabel(therapies) {
      if (!Array.isArray(therapies) || therapies.length === 0) {
        return 'Keterangan Perkembangan Terapi';
      }

      const detected = therapies
        .flatMap(t => detectTherapyTypesFromText(t.jenis_terapi || t.type_therapy || t.therapy_type || ''))
        .filter(Boolean);

      if (detected.includes('si')) {
        return 'Perkembangan Terapi Senso-motor Integrasi';
      }
      if (detected.includes('wicara')) {
        return 'Perkembangan Terapi Wicara';
      }
      if (detected.includes('perilaku')) {
        return 'Perkembangan Terapi Perilaku';
      }

      return 'Keterangan Perkembangan Terapi';
    }

    function getTherapyTypes(therapies) {
      if (!Array.isArray(therapies) || therapies.length === 0) {
        return [];
      }

      const types = therapies
        .flatMap(t => detectTherapyTypesFromText(t.jenis_terapi || t.type_therapy || t.therapy_type || ''))
        .filter(Boolean);

      const unique = [];
      types.forEach(type => {
        if (!unique.includes(type)) unique.push(type);
      });

      return unique.length > 0 ? unique : ['generic'];
    }

    function getTherapyLabelForType(type) {
      switch (type) {
        case 'si':
          return 'Perkembangan Terapi Senso-motor Integrasi';
        case 'wicara':
          return 'Perkembangan Terapi Wicara';
        case 'perilaku':
          return 'Perkembangan Terapi Perilaku';
        default:
          return 'Keterangan Perkembangan Terapi';
      }
    }

    function parseTherapyNotesByType(noteString) {
      if (!noteString) return {
        generic: ''
      };
      if (typeof noteString === 'object') {
        return noteString;
      }

      const raw = String(noteString || '').trim();
      const result = {
        generic: raw
      };
      if (!raw) {
        return result;
      }

      const typeMap = {
        'perkembangan terapi senso-motor integrasi': 'si',
        'perkembangan terapi wicara': 'wicara',
        'perkembangan terapi perilaku': 'perilaku'
      };

      const lines = raw.split(/\r?\n/);
      let currentType = 'generic';
      let buffer = [];

      lines.forEach(line => {
        const trimmed = line.trim();
        const normalized = trimmed.replace(/[:]+$/, '').toLowerCase();
        if (typeMap[normalized]) {
          if (buffer.length) {
            result[currentType] = buffer.join('\n').trim();
            buffer = [];
          }
          currentType = typeMap[normalized];
          return;
        }
        buffer.push(line);
      });

      result[currentType] = buffer.join('\n').trim();
      return result;
    }

    function buildTherapyNotesString() {
      const textareas = Array.from(document.querySelectorAll('.therapy-notes-field'));
      if (textareas.length === 0) {
        return '';
      }

      const parts = textareas.map(textarea => {
        const type = textarea.dataset.therapyType || textarea.id.replace('therapy_notes_', '');
        const value = String(textarea.value || '').trim();
        if (!value) {
          return null;
        }
        const label = getTherapyLabelForType(type);
        return `${label}:\n${value}`;
      }).filter(Boolean);

      return parts.join('\n\n');
    }

    function gatherTherapyNotesObject() {
      const textareas = Array.from(document.querySelectorAll('.therapy-notes-field'));
      if (textareas.length === 0) return {};

      const result = {};
      textareas.forEach(textarea => {
        const type = textarea.dataset.therapyType || textarea.id.replace('therapy_notes_', '');
        const value = String(textarea.value || '').trim();
        if (value) result[type] = value;
      });

      return result;
    }

    function updateTherapyNotesField(therapies, existingNotes = '') {
      const therapyContainer = document.getElementById('therapyNotesContainer');
      const therapyFields = document.getElementById('therapyNotesFields');

      // Debug logs to help diagnose why dynamic therapy fields may not appear
      try {
        console.log('updateTherapyNotesField called', {
          therapies: therapies,
          existingNotes: existingNotes
        });
      } catch (e) {}

      if (!therapyContainer || !therapyFields) {
        return;
      }

      const therapyTypes = getTherapyTypes(therapies);
      try {
        console.log('detected therapyTypes:', therapyTypes);
      } catch (e) {}
      if (therapyTypes.length === 0) {
        therapyContainer.style.display = 'none';
        therapyFields.innerHTML = '';
        return;
      }

      const parsedNotes = parseTherapyNotesByType(existingNotes);
      const noteValues = {
        ...parsedNotes
      };
      if (therapyTypes.length > 1 && parsedNotes.generic) {
        noteValues[therapyTypes[0]] = parsedNotes.generic;
        noteValues.generic = '';
      }

      therapyFields.innerHTML = therapyTypes.map(type => {
        const labelText = getTherapyLabelForType(type);
        const value = noteValues[type] || '';
        return `
          <div class="mb-3">
            <label class="form-label" for="therapy_notes_${type}">${labelText}</label>
            <textarea id="therapy_notes_${type}" data-therapy-type="${type}" class="form-control therapy-notes-field" rows="4" placeholder="Isi ${labelText.toLowerCase()}...">${escapeHtml(value)}</textarea>
          </div>
        `;
      }).join('');

      therapyContainer.style.display = '';
    }

    window.updateTherapyNotesField = updateTherapyNotesField;

    function getNextCustomCategoryLabel() {
      if (!customProgramsContainer) return 'Kategori baru';

      const existingLabels = Array.from(customProgramsContainer.querySelectorAll('.custom-group-name'))
        .map(input => String(input.value || '').trim().toLowerCase())
        .filter(Boolean);

      let index = 1;
      let label = 'Kategori baru';
      while (existingLabels.includes(label.toLowerCase())) {
        index += 1;
        label = `Kategori baru ${index}`;
      }

      return label;
    }

    function createCustomProgramRow(name = '', nilai = 'A', catatan = '') {
      return `
        <tr>
          <td>
            <input type="text" class="form-control form-control-sm custom-program-name" value="${escapeHtml(name)}" placeholder="Nama program kustom">
          </td>
          <td class="text-center align-middle">
            <select class="form-select form-select-sm custom-program-nilai">
              <option value="A" ${nilai === 'A' ? 'selected' : ''}>A</option>
              <option value="B" ${nilai === 'B' ? 'selected' : ''}>B</option>
              <option value="C" ${nilai === 'C' ? 'selected' : ''}>C</option>
              <option value="D" ${nilai === 'D' ? 'selected' : ''}>D</option>
              <option value="-" ${nilai === '-' ? 'selected' : ''}>-</option>
            </select>
          </td>
          <td>
            <div class="d-flex gap-2 align-items-start">
              <textarea class="form-control form-control-sm custom-program-catatan" rows="3" style="min-height: 90px; resize: vertical;" placeholder="Catatan program">${escapeHtml(catatan)}</textarea>
              <button type="button" class="btn btn-sm btn-outline-danger btn-remove-custom-row" title="Hapus program">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </td>
        </tr>`;
    }

    function createCustomGroupMarkup(groupName = '', note = '', rows = []) {
      const safeGroupName = escapeHtml(groupName || 'Kategori baru');
      const safeNote = escapeHtml(note || '');
      const rowMarkup = (rows.length > 0 ? rows.map(row => createCustomProgramRow(row.name, row.nilai, row.catatan)).join('') : createCustomProgramRow());

      return `
        <div class="program-group custom-program-group mb-4" data-custom-group="true">
          <div class="border rounded p-3">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
              <div class="flex-grow-1">
                <label class="form-label small mb-1">Nama Kategori</label>
                <input type="text" class="form-control form-control-sm custom-group-name" value="${safeGroupName}" placeholder="Masukkan nama kategori">
              </div>
              <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-remove-custom-group" title="Hapus kategori">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-center" style="width:40%;">Program</th>
                    <th class="text-center" style="width:12%;">Nilai</th>
                    <th class="text-center">Catatan</th>
                  </tr>
                </thead>
                <tbody>
                  ${rowMarkup}
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
              <button type="button" class="btn btn-sm btn-primary btn-add-custom-row">
                <i class="ri-add-line me-1"></i>Tambah Baris Program
              </button>
            </div>
            <div class="mt-3">
              <label class="form-label small">Catatan Kategori</label>
              <textarea class="form-control form-control-sm group-note" rows="3" placeholder="Catatan untuk kategori ini">${safeNote}</textarea>
            </div>
          </div>
        </div>`;
    }

    function addCustomGroup(groupName = '', note = '', rows = []) {
      if (!customProgramsContainer) return;
      customProgramsContainer.insertAdjacentHTML('beforeend', createCustomGroupMarkup(groupName, note, rows));
    }

    function renderCustomGroups(groups = []) {
      if (!customProgramsContainer) return;
      customProgramsContainer.innerHTML = '';
      if (!Array.isArray(groups) || groups.length === 0) {
        return;
      }
      groups.forEach(group => addCustomGroup(group.label, group.note || '', group.programs || []));
    }

    window.renderCustomGroups = renderCustomGroups;

    function formatSemesterLabel(semester, tahun) {
      if (!tahun) {
        tahun = getDefaultAcademicYear();
      }

      if (semester === 'I') {
        return `Semester I (Juli - Desember ${tahun.split('/')[0]})`;
      }

      return `Semester II (Januari - Juni ${tahun.split('/')[1] || tahun.split('/')[0]})`;
    }

    function renderPrograms(groups) {
      if (customProgramsContainer) {
        customProgramsContainer.innerHTML = '';
      }
      // ensure groups are shown in desired priority order
      const preferredOrder = ['Basic Learning', 'Akademik', 'Bina Diri', 'Motorik'];
      const orderMap = {};
      preferredOrder.forEach((v, i) => orderMap[v.toLowerCase()] = i);
      groups = (groups || []).slice().sort((a, b) => {
        const la = String(a.label || '').toLowerCase();
        const lb = String(b.label || '').toLowerCase();
        const ia = orderMap.hasOwnProperty(la) ? orderMap[la] : 1000;
        const ib = orderMap.hasOwnProperty(lb) ? orderMap[lb] : 1000;
        if (ia !== ib) return ia - ib;
        return la.localeCompare(lb);
      });
      if (!groups || groups.length === 0 || (groups.length === 1 && groups[0].programs.length === 0)) {
        setPlaceholderState('Belum ada program pada semester tersebut.');
        programSummary.textContent = 'Belum ada program pada semester tersebut.';
        return;
      }

      let totalPrograms = 0;
      let html = '';
      let idx = 1;

      groups.forEach(group => {
        if (!group.programs || group.programs.length === 0) return;
        totalPrograms += group.programs.length;
        html += `<div class="program-group mb-4" data-group-index="${idx}">`;
        html += `<div class="d-flex justify-content-between align-items-center mb-2"><div><h6 class="mb-0">${idx}. ${group.label}</h6><div class="text-muted small">${group.programs.length} program</div></div></div>`;

        const subMap = {};
        group.programs.forEach(p => {
          const name = (p.nama_program || '').trim();
          const m = name.match(/^([A-Za-z])/);
          const key = m ? m[1].toUpperCase() : '_';
          if (!subMap[key]) subMap[key] = [];
          subMap[key].push(p);
        });

        const subgroupTitles = {
          'A': 'Sikap Kooperatif dan Penguatan Kemampuan yang Efektif (A1-A19)',
          'B': 'Kemampuan Visual (B1-B27)',
          'C': 'Bahasa Reseptif (Reseptive Language) (C1-C57)',
          'D': 'Menirukan (Imitation) (D1-D27)',
          'E': 'Menirukan Secara Lisan (E1-E20)',
          'F': 'Kemampuan Permintaan (F1-F29)',
          'G': 'Menamakan (Labeling) (G1-G47)',
          'H': 'Kemampuan Intraverbal (Intraverbal) (H1-H49)',
          'I': 'Spontan Secara Lisan (I1-I9)',
          'J': 'Aturan Penyusunan Kata dan Tata Bahasa (Syntax and Grammar) (J1-J20)',
          'K': 'Kemampuan Bermain (K1-K15)',
          'L': 'Interaksi Sosial (L1-L34)',
          'M': 'Belajar Berkelompok (M1-M12)',
          'N': 'Mengikuti Rutinitas di dalam Kelas (N1-N10)',
          'P': 'Menggeneralisasikan Respon (Generalized Respon) (P1-P6)',
          'Q': 'Kemampuan Membaca (Reading Skills) (Q1-Q17)',
          'R': 'Kemampuan Berhitung (Math Skills) (R1-R29)',
          'S': 'Kemampuan Menulis (Writing Skills) (S1-S10)',
          'T': 'Mengeja (Spelling) (T1-T7)',
          'U': 'Kemampuan Berpakaian (Dressing Skill) (U1-U15)',
          'V': 'Kemampuan/Tata Cara Makan (Eating Skills) (V1-V10)',
          'W': 'Kebersihan Diri (Grooming Skills) (W1-W7)',
          'X': 'Kemampuan Menggunakan Toilet (Toileting Skills) (X1-X10)',
          'Y': 'Kemampuan Motorik Kasar (Gross Motor Skills) (Y1-Y30)',
          'Z': 'Kemampuan Motorik Halus (Fine Motor Skills) (Z1-Z28)'
        };

        Object.keys(subMap).sort().forEach(subKey => {
          const programs = subMap[subKey];
          const title = subgroupTitles[subKey] || (subKey === '_' ? '' : subKey);

          if (title) {
            html += `<div style="font-weight:700;margin-top:8px;margin-bottom:6px;">${title}</div>`;
          }

          html += `<div class="table-responsive"><table class="table table-bordered align-middle mb-0"><thead class="table-light"><tr><th class="text-center" style="width:58%;">Program</th><th class="text-center" style="width:18%;">Nilai</th><th class="text-center">Catatan</th></tr></thead><tbody>`;

          programs.forEach(program => {
            const safeCatatan = (program.catatan || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            html += `
              <tr>
                <td><div class="fw-medium">${program.nama_program}</div></td>
                <td class="text-center align-middle"><span class="badge ${getBadgeClass(program.nilai_huruf)}">${program.nilai_huruf}</span></td>
                <td><textarea class="form-control form-control-sm program-note" rows="3" style="min-height: 90px; resize: vertical;" placeholder="Tambah catatan program">${safeCatatan}</textarea></td>
              </tr>
            `;
          });

          html += `</tbody></table></div>`;
        });

        // Group-level note textarea (spans the group)
        html += `<div class="mt-2"><label class="form-label small">Catatan (${group.label})</label><textarea class="form-control form-control-sm group-note" rows="3" placeholder="Catatan untuk seluruh program dalam kelompok ini"></textarea></div>`;

        html += `</div>`;
        idx++;
      });

      programGroupsContainer.innerHTML = html;
      programSummary.textContent = `${totalPrograms} program ditemukan untuk semester ini.`;
    }

    async function loadChildren() {
      try {
        const response = await fetch(raporDataUrl);
        if (!response.ok) {
          console.error('Failed to fetch children:', response.status, response.statusText);
          anakSelect.innerHTML = '<option value="">Gagal memuat anak didik</option>';
          return;
        }

        const data = await response.json();
        if (!data.success) {
          console.error('API returned error:', data);
          anakSelect.innerHTML = '<option value="">Gagal memuat anak didik</option>';
          return;
        }

        anakSelect.innerHTML = '<option value="">Pilih Anak Didik</option>';

        if (!data.children || data.children.length === 0) {
          anakSelect.innerHTML = '<option value="">Belum ada anak didik tersedia</option>';
          setPlaceholderState('Belum ada anak didik yang tersedia untuk akun Anda.');
          return;
        }

        data.children.forEach(child => {
          const option = document.createElement('option');
          option.value = child.id;
          option.textContent = child.nama;
          anakSelect.appendChild(option);
        });

        if (data.child) {
          anakSelect.value = data.child.id;
        }
      } catch (error) {
        console.error('Error loading children:', error);
        anakSelect.innerHTML = '<option value="">Gagal memuat anak didik</option>';
      }
    }

    async function loadPrograms() {
      const selectedChild = anakSelect.value;
      const semester = semesterSelect.value;
      const tahun = tahunInput.value.trim() || getDefaultAcademicYear();

      semesterInfo.classList.remove('d-none');
      semesterInfo.textContent = formatSemesterLabel(semester, tahun);

      if (!selectedChild) {
        setPlaceholderState('Pilih anak didik untuk menampilkan program semester ini.');
        programSummary.textContent = 'Pilih anak didik untuk memuat daftar program.';
        if (customProgramsContainer) {
          customProgramsContainer.innerHTML = '';
        }
        return;
      }

      try {
        const url = new URL(raporDataUrl, window.location.origin);
        url.searchParams.set('anak_didik_id', selectedChild);
        url.searchParams.set('semester', semester);
        url.searchParams.set('tahun_pelajaran', tahun);

        const response = await fetch(url.toString());
        if (!response.ok) {
          console.error('Failed to fetch programs:', response.status, response.statusText);
          setPlaceholderState('Terjadi kesalahan saat memuat program.');
          return;
        }

        const data = await response.json();
        if (!data.success) {
          console.error('API returned error:', data);
          setPlaceholderState('Terjadi kesalahan saat memuat program.');
          return;
        }

        renderPrograms(data.groups);
        updateTherapyNotesField(data.therapies);
      } catch (error) {
        console.error('Error loading programs:', error);
        setPlaceholderState('Terjadi kesalahan saat memuat program.');
      }
    }

    function initDefaults() {
      semesterSelect.value = getDefaultSemester();
      tahunInput.value = getDefaultAcademicYear();
    }

    function resetFormToCreateMode() {
      isEditMode = false;
      editingRaporId = null;
      resetCreateRaporModalState();

      // Update modal title
      const modalTitle = modalEl.querySelector('.modal-title');
      modalTitle.textContent = 'Buat Rapor';

      // Update button text
      const btnSimpan = document.getElementById('btnSimpanRapor');
      const originalButtonHtml = '<i class="ri-save-line me-2"></i>Simpan Rapor';
      btnSimpan.innerHTML = originalButtonHtml;

      // Reset form fields
      anakSelect.value = '';
      semesterSelect.value = getDefaultSemester();
      tahunInput.value = getDefaultAcademicYear();
      setPlaceholderState('Pilih anak didik untuk menampilkan program semester ini.');
      if (customProgramsContainer) {
        customProgramsContainer.innerHTML = '';
      }
      // clear saran fields
      const saranGuruInput = document.getElementById('saran_guru');
      const saranOrtuInput = document.getElementById('saran_orang_tua');
      if (saranGuruInput) saranGuruInput.value = '';
      if (saranOrtuInput) saranOrtuInput.value = '';
      updateTherapyNotesField([]);
    }

    if (btnTambahKategori) {
      btnTambahKategori.addEventListener('click', function() {
        addCustomGroup(getNextCustomCategoryLabel());
      });
    }

    if (btnTambahProgramGlobal) {
      btnTambahProgramGlobal.addEventListener('click', function() {
        addCustomGroup(getNextCustomCategoryLabel());
      });
    }

    if (customProgramsContainer) {
      customProgramsContainer.addEventListener('click', function(event) {
        const removeRowBtn = event.target.closest('.btn-remove-custom-row');
        if (removeRowBtn) {
          const row = removeRowBtn.closest('tr');
          if (row) {
            row.remove();
          }
          return;
        }

        const removeGroupBtn = event.target.closest('.btn-remove-custom-group');
        if (removeGroupBtn) {
          const group = removeGroupBtn.closest('.custom-program-group');
          if (group) {
            group.remove();
          }
          return;
        }

        const addRowBtn = event.target.closest('.btn-add-custom-row');
        if (addRowBtn) {
          const group = addRowBtn.closest('.custom-program-group');
          const tbody = group ? group.querySelector('tbody') : null;
          if (tbody) {
            tbody.insertAdjacentHTML('beforeend', createCustomProgramRow());
          }
        }
      });
    }

    modalEl.addEventListener('input', function(event) {
      if (event.target.matches('input, textarea, select')) {
        setCreateRaporModalDirty(true);
      }
    });

    modalEl.addEventListener('change', function(event) {
      if (event.target.matches('input, textarea, select')) {
        setCreateRaporModalDirty(true);
      }
    });

    modalEl.addEventListener('shown.bs.modal', async function() {
      // Avoid overwriting edit form data when opening the modal in edit mode
      if (isEditMode) {
        return;
      }

      resetFormToCreateMode();
      initDefaults();

      try {
        await loadChildren();
        await loadPrograms();
      } catch (error) {
        console.error('Error initializing modal:', error);
        setPlaceholderState('Terjadi kesalahan saat memuat data.');
      }
    });

    // Confirm closing the create modal if there are unsaved changes.
    modalEl.addEventListener('hide.bs.modal', function(event) {
      if (createRaporModalAllowClose || !createRaporModalIsDirty) {
        return;
      }

      event.preventDefault();

      const shouldClose = window.confirm('Inputan yang belum disimpan akan terhapus. Yakin ingin menutup modal ini?');
      if (shouldClose) {
        createRaporModalAllowClose = true;
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.hide();
      }
    });

    // Keep the current input state while the modal is hidden unless close was confirmed.
    modalEl.addEventListener('hidden.bs.modal', function() {
      if (createRaporModalAllowClose) {
        resetFormToCreateMode();
      } else {
        resetCreateRaporModalState();
      }
    });

    anakSelect.addEventListener('change', loadPrograms);
    semesterSelect.addEventListener('change', loadPrograms);
    tahunInput.addEventListener('change', loadPrograms);

    initDefaults();
    setPlaceholderState('Pilih anak didik untuk menampilkan program semester ini.');

    // Handler untuk tombol Simpan Rapor (Create & Edit)
    const btnSimpan = document.getElementById('btnSimpanRapor');
    const kelasInput = document.getElementById('kelas');

    btnSimpan.addEventListener('click', async function() {
      const anakDidikId = anakSelect.value;
      const kelas = kelasInput.value.trim();
      const semester = semesterSelect.value;
      const tahunPelajaran = tahunInput.value.trim();

      // Validasi field required
      if (!anakDidikId) {
        showToastr('error', 'Silakan pilih Anak Didik terlebih dahulu', 'Error');
        return;
      }

      if (!semester) {
        showToastr('error', 'Silakan pilih Semester terlebih dahulu', 'Error');
        return;
      }

      if (!kelas) {
        showToastr('error', 'Silakan pilih Kelas terlebih dahulu', 'Error');
        return;
      }

      if (!tahunPelajaran) {
        showToastr('error', 'Silakan isi Tahun Pelajaran terlebih dahulu', 'Error');
        return;
      }

      // Collect catatan from standard and custom program textareas and group-level notes
      const programs = [];
      const groupNotes = {};

      const groups = programGroupsContainer.querySelectorAll('.program-group');
      groups.forEach(groupEl => {
        const labelEl = groupEl.querySelector('h6');
        const groupLabel = labelEl ? labelEl.textContent.trim().replace(/^\d+\.\s*/, '') : '';
        const groupNoteEl = groupEl.querySelector('.group-note');
        const groupNote = groupNoteEl ? groupNoteEl.value.trim() : '';
        if (groupLabel) groupNotes[groupLabel] = groupNote;

        const rows = groupEl.querySelectorAll('tbody tr');
        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length >= 3) {
            const namaProgram = cells[0].textContent.trim();
            const badgeSpan = cells[1].querySelector('span.badge');
            const nilaiHuruf = badgeSpan ? badgeSpan.textContent.trim() : '';
            const catatanInput = cells[2].querySelector('textarea.program-note');
            const catatan = catatanInput ? catatanInput.value.trim() : '';

            if (nilaiHuruf) {
              programs.push({
                nama_program: namaProgram,
                nilai_huruf: nilaiHuruf,
                catatan: catatan,
                kategori: groupLabel
              });
            }
          }
        });
      });

      if (customProgramsContainer) {
        const customGroups = customProgramsContainer.querySelectorAll('.custom-program-group');
        customGroups.forEach(groupEl => {
          const groupNameInput = groupEl.querySelector('.custom-group-name');
          const groupName = groupNameInput ? groupNameInput.value.trim() : '';
          const groupNote = groupEl.querySelector('.group-note') ? groupEl.querySelector('.group-note').value.trim() : '';

          if (!groupName) {
            return;
          }

          groupNotes[groupName] = groupNote;

          const rows = groupEl.querySelectorAll('tbody tr');
          rows.forEach(row => {
            const nameInput = row.querySelector('.custom-program-name');
            const nilaiSelect = row.querySelector('.custom-program-nilai');
            const catatanInput = row.querySelector('.custom-program-catatan');
            const namaProgram = nameInput ? nameInput.value.trim() : '';
            const nilaiHuruf = nilaiSelect ? nilaiSelect.value.trim() : '';
            const catatan = catatanInput ? catatanInput.value.trim() : '';

            if (!namaProgram) {
              return;
            }

            programs.push({
              nama_program: namaProgram,
              nilai_huruf: nilaiHuruf || 'A',
              catatan: catatan,
              kategori: groupName
            });
          });
        });
      }

      if (programs.length === 0) {
        showToastr('error', 'Tidak ada program untuk disimpan', 'Error');
        return;
      }

      // Disable button dan show loading state
      const originalText = btnSimpan.innerHTML;
      btnSimpan.disabled = true;
      btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

      try {
        // Determine endpoint dan method based on mode
        const endpoint = isEditMode ? `/rapor-anak/${editingRaporId}` : '/rapor-anak/store';
        const method = isEditMode ? 'PATCH' : 'POST';

        // Send data to server
        const response = await fetch(endpoint, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          // include saran fields
          body: JSON.stringify({
            anak_didik_id: anakDidikId,
            kelas: kelas,
            semester: semester,
            tahun_pelajaran: tahunPelajaran,
            programs: programs,
            group_notes: groupNotes,
            saran_guru: (document.getElementById('saran_guru') || {}).value || '',
            saran_orang_tua: (document.getElementById('saran_orang_tua') || {}).value || '',
            therapy_notes: gatherTherapyNotesObject()
          })
        });

        const data = await response.json();

        console.log('Response status:', response.status);
        console.log('Response data:', data);

        if (data.success) {
          const successMessage = isEditMode ? 'Rapor berhasil diubah' : 'Rapor berhasil disimpan';
          showToastr('success', data.message || successMessage, 'Sukses');

          // Reset form and mode
          resetFormToCreateMode();
          createRaporModalAllowClose = true;

          // Close modal
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) {
            modalInstance.hide();
          }

          // Reload rapor list
          setTimeout(() => {
            loadRapors();
          }, 1000);
        } else {
          showToastr('error', data.message || 'Terjadi kesalahan saat menyimpan rapor', 'Error');
        }
      } catch (error) {
        console.error('Error:', error);
        showToastr('error', 'Terjadi kesalahan saat menyimpan rapor: ' + error.message, 'Error');
      } finally {
        // Restore button state
        btnSimpan.disabled = false;
        btnSimpan.innerHTML = originalText;
      }
    });

    // Function untuk load rapor list
    window.loadRapors = async function() {
      const tableBody = document.getElementById('raporTableBody');
      try {
        const response = await fetch('/rapor-anak/list', {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        const data = await response.json();

        if (data.success && data.rapors.length > 0) {
          const rows = data.rapors.map((child, index) => `
            <tr>
              <td>${index + 1}</td>
              <td>
                <p class="text-heading mb-0 fw-medium">${child.anak_didik_nama}</p>
              </td>
              <td>${child.guru_fokus_nama}</td>
              <td>${child.nama_orang_tua}</td>
              <td>${child.umur ? child.umur + ' tahun' : '-'}</td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-outline-info"
                    onclick="loadRiwayatRapor(${child.anak_didik_id}); return false;"
                    title="Lihat Riwayat Rapor"
                    aria-label="Lihat Riwayat Rapor">
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              </td>
            </tr>
          `).join('');
          tableBody.innerHTML = rows;
        } else {
          tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-body-secondary py-4">Belum ada rapor yang dibuat.</td></tr>';
        }
      } catch (error) {
        console.error('Error loading rapors:', error);
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Terjadi kesalahan saat memuat rapor.</td></tr>';
      }
    };

    // Load rapor list on page load
    loadRapors();
  })();

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // Global functions untuk detail, edit, delete rapor
  async function loadDetailRapor(raporId) {
    // Keep behavior consistent with edit modal: fetch rapor, then fetch program groups
    const riwayatEl = document.getElementById('riwayatRaporModal');
    try {
      const riwayatInstance = bootstrap.Modal.getInstance(riwayatEl) || bootstrap.Modal.getOrCreateInstance(riwayatEl);
      riwayatInstance.hide();
    } catch (e) {
      // ignore
    }

    const modal = new bootstrap.Modal(document.getElementById('detailRaporModal'));
    const content = document.getElementById('detailRaporContent');
    const pdfBtn = document.getElementById('detailRaporPdfBtn');
    const previewUrl = `/rapor-anak/${raporId}/preview`;
    pdfBtn.href = previewUrl;
    pdfBtn.rel = 'noopener noreferrer';
    pdfBtn.onclick = (e) => {
      // ensure it opens correct preview even if href not updated for some reason
      e.preventDefault();
      window.open(previewUrl, '_blank');
    };

    try {
      const resp = await fetch(`/rapor-anak/${raporId}`, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      });
      const data = await resp.json();

      if (!data.success) {
        showToastr('error', 'Gagal memuat detail rapor', 'Error');
        return;
      }

      const rapor = data.rapor;

      // Fetch program groups for the same anak & periode so grouping matches create/edit
      const raporDataUrl = "{{ route('rapor-anak.data') }}";
      const url = new URL(raporDataUrl, window.location.origin);
      url.searchParams.set('anak_didik_id', rapor.anak_didik_id);
      url.searchParams.set('semester', rapor.semester);
      url.searchParams.set('tahun_pelajaran', rapor.tahun_pelajaran);

      const pgResp = await fetch(url.toString());
      const pgData = await pgResp.json();

      // sort fetched groups by preferred order so modal detail matches Create/Edit
      try {
        const preferredOrder = ['Basic Learning', 'Akademik', 'Bina Diri', 'Motorik'];
        const orderMap = {};
        preferredOrder.forEach((v, i) => orderMap[v.toLowerCase()] = i);
        if (pgData && Array.isArray(pgData.groups)) {
          pgData.groups.sort((a, b) => {
            const la = String(a.label || '').toLowerCase();
            const lb = String(b.label || '').toLowerCase();
            const ia = orderMap.hasOwnProperty(la) ? orderMap[la] : 1000;
            const ib = orderMap.hasOwnProperty(lb) ? orderMap[lb] : 1000;
            if (ia !== ib) return ia - ib;
            return la.localeCompare(lb);
          });
        }
      } catch (e) {
        // ignore sorting errors
      }

      // populate saran fields for detail view
      const saranGuru = rapor.saran_guru || '';
      const saranOrtu = rapor.saran_orang_tua || '';

      // Map rapor items by normalized name for easy matching
      const normalize = v => String(v || '').toLowerCase().trim().replace(/\s+/g, ' ');
      const itemsMap = {};
      (rapor.items || []).forEach(item => {
        itemsMap[normalize(item.nama_program)] = item;
      });

      let groupHtml = '';
      let nextGroupIndex = 1;
      const standardNames = new Set();
      const customGroups = [];

      if (pgData.groups && pgData.groups.length > 0) {
        pgData.groups.forEach((group) => {
          const programs = (group.programs || []).filter(p => !!p.nama_program);
          if (programs.length === 0) return;

          programs.forEach(p => standardNames.add(normalize(p.nama_program)));

          // Build header with numbering
          groupHtml += `<div style="font-weight:700;font-size:15px;margin-top:18px;margin-bottom:6px;">${nextGroupIndex}. ${escapeHtml(group.label)}</div>`;

          // create subgroups by initial letter (A, B, etc.) inside this category
          const subMap = {};
          (programs || []).forEach(p => {
            const name = (p.nama_program || '').trim();
            const m = name.match(/^([A-Za-z])/);
            const key = m ? m[1].toUpperCase() : '_';
            if (!subMap[key]) subMap[key] = [];
            subMap[key].push(p);
          });
          const subKeys = Object.keys(subMap).sort();
          const subTitles = {
            'A': 'Sikap Kooperatif dan Penguatan Kemampuan yang Efektif (A1-A19)',
            'B': 'Kemampuan Visual (B1-B27)',
            'C': 'Bahasa Reseptif (Reseptive Language) (C1-C57)',
            'D': 'Menirukan (Imitation) (D1-D27)',
            'E': 'Menirukan Secara Lisan (E1-E20)',
            'F': 'Kemampuan Permintaan (F1-F29)',
            'G': 'Menamakan (Labeling) (G1-G47)',
            'H': 'Kemampuan Intraverbal (Intraverbal) (H1-H49)',
            'I': 'Spontan Secara Lisan (I1-I9)',
            'J': 'Aturan Penyusunan Kata dan Tata Bahasa (Syntax and Grammar) (J1-J20)',
            'K': 'Kemampuan Bermain (K1-K15)',
            'L': 'Interaksi Sosial (L1-L34)',
            'M': 'Belajar Berkelompok (M1-M12)',
            'N': 'Mengikuti Rutinitas di dalam Kelas (N1-N10)',
            'P': 'Menggeneralisasikan Respon (Generalized Respon) (P1-P6)',
            'Q': 'Kemampuan Membaca (Reading Skills) (Q1-Q17)',
            'R': 'Kemampuan Berhitung (Math Skills) (R1-R29)',
            'S': 'Kemampuan Menulis (Writing Skills) (S1-S10)',
            'T': 'Mengeja (Spelling) (T1-T7)',
            'U': 'Kemampuan Berpakaian (Dressing Skill) (U1-U15)',
            'V': 'Kemampuan/Tata Cara Makan (Eating Skills) (V1-V10)',
            'W': 'Kebersihan Diri (Grooming Skills) (W1-W7)',
            'X': 'Kemampuan Menggunakan Toilet (Toileting Skills) (X1-X10)',
            'Y': 'Kemampuan Motorik Kasar (Gross Motor Skills) (Y1-Y30)',
            'Z': 'Kemampuan Motorik Halus (Fine Motor Skills) (Z1-Z28)'
          };

          subKeys.forEach(subKey => {
            const subPrograms = subMap[subKey];
            if (subTitles[subKey]) {
              groupHtml += `<div style="font-weight:700;margin-top:8px;margin-bottom:6px;">${subTitles[subKey]}</div>`;
            }
            groupHtml += `<div class="table-responsive"><table class="table table-bordered mb-2 rapor-table" style="table-layout:fixed;width:100%;"><thead class="table-light"><tr><th style="width:58%;">Program</th><th class="text-center" style="width:18%">Nilai</th><th>Catatan</th></tr></thead><tbody>`;

            subPrograms.forEach(p => {
              const key = normalize(p.nama_program);
              const matched = itemsMap[key];
              const nilai = matched ? matched.nilai_huruf : '-';
              const cat = matched && matched.catatan ? String(matched.catatan).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '-';
              groupHtml += `<tr><td class="rapor-cell-program">${escapeHtml(p.nama_program)}</td><td class="rapor-cell-nilai text-center align-middle"><span class="badge ${getBadgeClass(nilai)}">${nilai}</span></td><td class="rapor-cell-catatan" style="white-space: pre-wrap; word-break: break-word; vertical-align: top;">${cat}</td></tr>`;
            });

            groupHtml += `</tbody></table></div>`;
          });

          const gNote = (rapor && rapor.group_notes && rapor.group_notes[group.label]) ? rapor.group_notes[group.label] : null;
          const safe = gNote ? String(gNote).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : '-';
          groupHtml += `<div style="margin-top:8px; font-size:13px; color:#374151;">\n<strong>Catatan (${escapeHtml(group.label)}):</strong><div style="margin-top:6px;">${safe}</div></div>`;
          nextGroupIndex++;
        });
      }

      (rapor.items || []).forEach(item => {
        const normalizedName = normalize(item.nama_program);
        if (!normalizedName || standardNames.has(normalizedName)) {
          return;
        }

        const label = (item.kategori_label || item.kategori || 'Lainnya').toString().trim();
        const existingGroup = customGroups.find(group => group.label === label);
        if (existingGroup) {
          existingGroup.programs.push({
            name: item.nama_program,
            nilai: item.nilai_huruf || 'A',
            catatan: item.catatan || ''
          });
          return;
        }

        customGroups.push({
          label,
          note: (rapor.group_notes && (rapor.group_notes[label] || rapor.group_notes[String(label).toLowerCase()] || '')) || '',
          programs: [{
            name: item.nama_program,
            nilai: item.nilai_huruf || 'A',
            catatan: item.catatan || ''
          }]
        });
      });

      customGroups.forEach((group) => {
        const currentIndex = nextGroupIndex;
        nextGroupIndex++;
        const groupNote = group.note ? String(group.note).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : '-';
        groupHtml += `<div style="font-weight:700;font-size:15px;margin-top:18px;margin-bottom:6px;">${currentIndex}. ${escapeHtml(group.label)}</div>`;
        groupHtml += `<div class="table-responsive"><table class="table table-bordered mb-2 rapor-table" style="table-layout:fixed;width:100%;"><thead class="table-light"><tr><th style="width:58%;">Program</th><th class="text-center" style="width:18%;">Nilai</th><th>Catatan</th></tr></thead><tbody>`;

        group.programs.forEach(item => {
          const cat = item.catatan ? String(item.catatan).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '-';
          groupHtml += `<tr><td class="rapor-cell-program">${escapeHtml(item.name)}</td><td class="rapor-cell-nilai text-center align-middle"><span class="badge ${getBadgeClass(item.nilai)}">${item.nilai}</span></td><td class="rapor-cell-catatan" style="white-space: pre-wrap; word-break: break-word; vertical-align: top;">${cat}</td></tr>`;
        });

        groupHtml += `</tbody></table></div>`;
        groupHtml += `<div style="margin-top:8px; font-size:13px; color:#374151;">\n<strong>Catatan (${escapeHtml(group.label)}):</strong><div style="margin-top:6px;">${groupNote}</div></div>`;
      });

      const therapyHtml = (() => {
        const notes = rapor.therapy_notes;

        if (!notes) {
          return '<p>-</p>';
        }

        if (typeof notes === 'object') {
          const order = ['si', 'wicara', 'perilaku'];
          const labels = {
            si: 'Perkembangan Terapi Senso-motor Integrasi',
            wicara: 'Perkembangan Terapi Wicara',
            perilaku: 'Perkembangan Terapi Perilaku'
          };
          const parts = [];

          order.forEach((key) => {
            if (notes[key]) {
              parts.push(`
              <div>
                <p class="mb-1"><strong>${labels[key]}</strong></p>
                <p style="white-space: pre-wrap; text-align: justify;">${escapeHtml(notes[key])}</p>
              </div>
            `);
            }
          });

          Object.keys(notes).forEach((key) => {
            if (!order.includes(key) && notes[key]) {
              parts.push(`
              <div>
                <p class="mb-1"><strong>${escapeHtml(key)}</strong></p>
                <p style="white-space: pre-wrap; text-align: justify;">${escapeHtml(notes[key])}</p>
              </div>
            `);
            }
          });

          return parts.length > 0 ? parts.join('<hr class="my-2">') : '<p>-</p>';
        }

        return `<p style="white-space: pre-wrap; text-align: justify;">${escapeHtml(notes)}</p>`;
      })();

      content.innerHTML = `
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <p class="mb-1"><strong>Nama Anak Didik:</strong></p>
            <p>${escapeHtml(rapor.anak_didik_nama)}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Kelas:</strong></p>
            <p>${escapeHtml(rapor.kelas || '-')}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Semester:</strong></p>
            <p>Semester ${escapeHtml(rapor.semester)}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Tahun Pelajaran:</strong></p>
            <p>${escapeHtml(rapor.tahun_pelajaran || '-')}</p>
          </div>
        </div>
        ${groupHtml}
        <div class="row g-3 mt-2">
          <div class="col-12">
            ${therapyHtml}
          </div>
        </div>
        <div class="row g-3 mt-4">
          <div class="col-md-6">
            <p class="mb-1"><strong>Saran Guru:</strong></p>
            <p style="white-space: pre-wrap; text-align: justify;">${escapeHtml(saranGuru || '-')}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Saran Orang Tua:</strong></p>
            <p style="white-space: pre-wrap; text-align: justify;">${escapeHtml(saranOrtu || '-')}</p>
          </div>
        </div>
      `;

      modal.show();
    } catch (error) {
      console.error('Error:', error);
      showToastr('error', 'Terjadi kesalahan saat memuat detail rapor', 'Error');
    }
  }

  // Function untuk load riwayat rapor
  async function loadRiwayatRapor(anakDidikId) {
    const modal = new bootstrap.Modal(document.getElementById('riwayatRaporModal'));
    const content = document.getElementById('riwayatRaporContent');

    try {
      const response = await fetch(`/rapor-anak/${anakDidikId}/riwayat`, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      });

      if (!response.ok) {
        const text = await response.text();
        console.error('Failed to load riwayat:', response.status, response.statusText, text);
        throw new Error('Gagal memuat riwayat rapor. Silakan coba lagi.');
      }

      const data = await response.json();

      if (data.success) {
        // Update modal title dengan nama anak
        const modalTitle = document.getElementById('riwayatRaporModalLabel');
        modalTitle.textContent = `Riwayat Rapor - ${data.anak_didik_nama}`;

        if (data.rapors.length > 0) {
          let tableHtml = `
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr class="table-light">
                    <th>No</th>
                    <th>Kelas</th>
                    <th style="width: 180px;">Semester</th>
                    <th>Tahun Pelajaran</th>
                    <th style="width: 170px;">Tanggal Dibuat</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
          `;

          data.rapors.forEach((rapor, index) => {
            const tanggalDibuat = rapor.tanggal_dibuat || '-';

            tableHtml += `
              <tr>
                <td>${index + 1}</td>
                <td>${rapor.kelas || '-'}</td>
                <td>Semester ${rapor.semester}</td>
                <td>${rapor.tahun_pelajaran}</td>
                <td>${tanggalDibuat}</td>
                <td>
                  <div class="d-none d-md-flex gap-2 align-items-center">
                    <button
                      type="button"
                      class="btn btn-sm btn-icon btn-outline-info"
                      onclick="loadDetailRapor(${rapor.id}); return false;"
                      title="Lihat Rapor"
                      aria-label="Lihat Rapor">
                      <i class="ri-eye-line"></i>
                    </button>
                    ${!isConsultantEducation ? `
                    <button
                      type="button"
                      class="btn btn-sm btn-icon btn-outline-warning"
                      onclick="editRapor(${rapor.id}); return false;"
                      title="Edit Rapor"
                      aria-label="Edit Rapor">
                      <i class="ri-edit-line"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-icon btn-outline-danger"
                      onclick="deleteRapor(${rapor.id}, this); return false;"
                      title="Hapus Rapor"
                      aria-label="Hapus Rapor">
                      <i class="ri-delete-bin-line"></i>
                    </button>
                    ` : ''}
                  </div>
                  <div class="dropdown d-md-none">
                    <button
                      class="btn btn-sm p-0 border-0 bg-transparent"
                      type="button"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                      title="Aksi Rapor"
                      aria-label="Aksi Rapor"
                      style="box-shadow:none;">
                      <i class="ri-more-2-fill" style="font-weight: bold; font-size: 1.5em;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="#" onclick="loadDetailRapor(${rapor.id}); return false;">
                          <i class="ri-eye-line me-2"></i>Lihat
                        </a>
                      </li>
                      ${!isConsultantEducation ? `
                      <li>
                        <a class="dropdown-item" href="#" onclick="editRapor(${rapor.id}); return false;">
                          <i class="ri-edit-line me-2"></i>Edit
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item text-danger" href="#" onclick="deleteRapor(${rapor.id}, this); return false;">
                          <i class="ri-delete-bin-line me-2"></i>Hapus
                        </a>
                      </li>
                      ` : ''}
                    </ul>
                  </div>
                </td>
              </tr>
            `;
          });

          tableHtml += `
                </tbody>
              </table>
            </div>
          `;
          content.innerHTML = tableHtml;
        } else {
          content.innerHTML = '<div class="alert alert-info">Belum ada riwayat rapor untuk anak didik ini.</div>';
        }

        modal.show();
      } else {
        showToastr('error', 'Gagal memuat riwayat rapor', 'Error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToastr('error', 'Terjadi kesalahan saat memuat riwayat rapor: ' + error.message, 'Error');
    }
  }

  function editRapor(raporId) {
    // Set edit mode
    isEditMode = true;
    editingRaporId = raporId;

    // Fetch rapor data
    // Ensure any riwayat/detail modal is hidden first so edit modal is on top
    try {
      const riwayatEl = document.getElementById('riwayatRaporModal');
      const riwayatInst = bootstrap.Modal.getInstance(riwayatEl) || bootstrap.Modal.getOrCreateInstance(riwayatEl);
      riwayatInst.hide();
    } catch (e) {}
    try {
      const detailEl = document.getElementById('detailRaporModal');
      const detailInst = bootstrap.Modal.getInstance(detailEl) || bootstrap.Modal.getOrCreateInstance(detailEl);
      detailInst.hide();
    } catch (e) {}

    fetch(`/rapor-anak/${raporId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const rapor = data.rapor;

          // Update modal title
          const modalTitle = document.querySelector('#buatRaporModal .modal-title');
          modalTitle.textContent = `Edit Rapor - ${rapor.anak_didik_nama}`;

          // Update button text
          const btnSimpan = document.getElementById('btnSimpanRapor');
          btnSimpan.innerHTML = '<i class="ri-save-line me-2"></i>Perbarui Rapor';

          // Pre-fill form fields
          const anakSelect = document.getElementById('anak_didik_id');
          const kelasInput = document.getElementById('kelas');
          const semesterSelect = document.getElementById('semester');
          const tahunInput = document.getElementById('tahun_pelajaran');

          // Set values
          anakSelect.value = rapor.anak_didik_id || '';
          kelasInput.value = rapor.kelas || '';
          semesterSelect.value = rapor.semester === 'II' ? 'II' : 'I';
          tahunInput.value = rapor.tahun_pelajaran || '';
          // set saran fields
          const saranGuruInput = document.getElementById('saran_guru');
          const saranOrtuInput = document.getElementById('saran_orang_tua');
          const existingTherapyNotes = rapor.therapy_notes || '';
          if (saranGuruInput) saranGuruInput.value = rapor.saran_guru || '';
          if (saranOrtuInput) saranOrtuInput.value = rapor.saran_orang_tua || '';

          // Load programs for this rapor
          const raporDataUrl = "{{ route('rapor-anak.data') }}";
          const url = new URL(raporDataUrl, window.location.origin);
          url.searchParams.set('anak_didik_id', rapor.anak_didik_id);
          url.searchParams.set('semester', rapor.semester);
          url.searchParams.set('tahun_pelajaran', rapor.tahun_pelajaran);

          fetch(url.toString())
            .then(response => response.json())
            .then(programData => {
              // Render programs with existing catatan values from rapor
              // order program groups by preferred categories
              try {
                const preferredOrder = ['Basic Learning', 'Akademik', 'Bina Diri', 'Motorik'];
                const orderMap = {};
                preferredOrder.forEach((v, i) => orderMap[v.toLowerCase()] = i);
                if (programData && Array.isArray(programData.groups)) {
                  programData.groups.sort((a, b) => {
                    const la = String(a.label || '').toLowerCase();
                    const lb = String(b.label || '').toLowerCase();
                    const ia = orderMap.hasOwnProperty(la) ? orderMap[la] : 1000;
                    const ib = orderMap.hasOwnProperty(lb) ? orderMap[lb] : 1000;
                    if (ia !== ib) return ia - ib;
                    return la.localeCompare(lb);
                  });
                }
              } catch (e) {
                // ignore
              }

              if (programData.groups && programData.groups.length > 0) {
                const normalizeProgramName = (value) => String(value || '').toLowerCase().trim().replace(/\s+/g, ' ');
                const itemsMap = {};
                const standardNames = new Set();
                const customGroups = [];

                rapor.items.forEach(item => {
                  itemsMap[normalizeProgramName(item.nama_program)] = item;
                });

                // Update groups with catatan from rapor and collect group notes
                programData.groups.forEach(group => {
                  group.programs.forEach(program => {
                    standardNames.add(normalizeProgramName(program.nama_program));
                    const matchedItem = itemsMap[normalizeProgramName(program.nama_program)];
                    if (matchedItem) {
                      program.catatan = matchedItem.catatan || '';
                    }
                  });
                });
                const tableHtmlParts = [];
                let totalPrograms = 0;

                programData.groups.forEach((group, gIndex) => {
                  if (!group.programs || group.programs.length === 0) return;
                  totalPrograms += group.programs.length;

                  tableHtmlParts.push(`<div class="program-group mb-4" data-group-index="${gIndex + 1}">`);
                  tableHtmlParts.push(`<div class="d-flex justify-content-between align-items-center mb-2"><div><h6 class="mb-0">${gIndex + 1}. ${group.label}</h6><div class="text-muted small">${group.programs.length} program</div></div></div>`);

                  // build subgroups by initial letter
                  const subMap = {};
                  (group.programs || []).forEach(p => {
                    const m = String(p.nama_program || '').trim().match(/^([A-Za-z])/);
                    const key = m ? m[1].toUpperCase() : '_';
                    if (!subMap[key]) subMap[key] = [];
                    subMap[key].push(p);
                  });
                  const subKeys = Object.keys(subMap).sort();
                  const subTitles = {
                    'A': 'Sikap Kooperatif dan Penguatan Kemampuan yang Efektif (A1-A19)',
                    'B': 'Kemampuan Visual (B1-B27)',
                    'C': 'Bahasa Reseptif (Reseptive Language) (C1-C57)',
                    'D': 'Menirukan (Imitation) (D1-D27)',
                    'E': 'Menirukan Secara Lisan (E1-E20)',
                    'F': 'Kemampuan Permintaan (F1-F29)',
                    'G': 'Menamakan (Labeling) (G1-G47)',
                    'H': 'Kemampuan Intraverbal (Intraverbal) (H1-H49)',
                    'I': 'Spontan Secara Lisan (I1-I9)',
                    'J': 'Aturan Penyusunan Kata dan Tata Bahasa (Syntax and Grammar) (J1-J20)',
                    'K': 'Kemampuan Bermain (K1-K15)',
                    'L': 'Interaksi Sosial (L1-L34)',
                    'M': 'Belajar Berkelompok (M1-M12)',
                    'N': 'Mengikuti Rutinitas di dalam Kelas (N1-N10)',
                    'P': 'Menggeneralisasikan Respon (Generalized Respon) (P1-P6)',
                    'Q': 'Kemampuan Membaca (Reading Skills) (Q1-Q17)',
                    'R': 'Kemampuan Berhitung (Math Skills) (R1-R29)',
                    'S': 'Kemampuan Menulis (Writing Skills) (S1-S10)',
                    'T': 'Mengeja (Spelling) (T1-T7)',
                    'U': 'Kemampuan Berpakaian (Dressing Skill) (U1-U15)',
                    'V': 'Kemampuan/Tata Cara Makan (Eating Skills) (V1-V10)',
                    'W': 'Kebersihan Diri (Grooming Skills) (W1-W7)',
                    'X': 'Kemampuan Menggunakan Toilet (Toileting Skills) (X1-X10)',
                    'Y': 'Kemampuan Motorik Kasar (Gross Motor Skills) (Y1-Y30)',
                    'Z': 'Kemampuan Motorik Halus (Fine Motor Skills) (Z1-Z28)'
                  };

                  subKeys.forEach(subKey => {
                    const subs = subMap[subKey];
                    if (subTitles[subKey]) {
                      tableHtmlParts.push(`<div style="font-weight:700;margin-top:8px;margin-bottom:6px;">${subTitles[subKey]}</div>`);
                    }
                    tableHtmlParts.push(`<div class="table-responsive"><table class="table table-bordered align-middle mb-0"><thead class="table-light"><tr><th class="text-center" style="width:58%;">Program</th><th class="text-center" style="width:18%;">Nilai</th><th class="text-center">Catatan</th></tr></thead><tbody>`);

                    subs.forEach(program => {
                      tableHtmlParts.push(`
                        <tr>
                          <td><div class="fw-medium">${program.nama_program}</div></td>
                          <td class="text-center align-middle"><span class="badge ${getBadgeClass(program.nilai_huruf)}">${program.nilai_huruf}</span></td>
                          <td><textarea class="form-control form-control-sm program-note" rows="3" style="min-height: 90px; resize: vertical;" placeholder="Tambah catatan program">${(program.catatan || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea></td>
                        </tr>
                      `);
                    });

                    tableHtmlParts.push(`</tbody></table></div>`);
                  });

                  // Prefill group note if available in rapor
                  const groupNoteValue = (rapor && rapor.group_notes && rapor.group_notes[group.label]) ? rapor.group_notes[group.label] : (group.group_note || '');
                  tableHtmlParts.push(`<div class="mt-2"><label class="form-label small">Catatan (${group.label})</label><textarea class="form-control form-control-sm group-note" rows="3" placeholder="Catatan untuk seluruh program dalam kelompok ini">${(groupNoteValue || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea></div>`);
                  tableHtmlParts.push(`</div>`);
                });

                (rapor.items || []).forEach(item => {
                  const normalizedName = normalizeProgramName(item.nama_program);
                  if (!normalizedName || standardNames.has(normalizedName)) {
                    return;
                  }

                  const label = (item.kategori_label || item.kategori || 'Kategori Baru').toString().trim();
                  if (!label) {
                    return;
                  }

                  const existingGroup = customGroups.find(group => group.label === label);
                  if (existingGroup) {
                    existingGroup.programs.push({
                      name: item.nama_program,
                      nilai: item.nilai_huruf || 'A',
                      catatan: item.catatan || ''
                    });
                    return;
                  }

                  customGroups.push({
                    label,
                    note: (rapor.group_notes && rapor.group_notes[label]) ? rapor.group_notes[label] : '',
                    programs: [{
                      name: item.nama_program,
                      nilai: item.nilai_huruf || 'A',
                      catatan: item.catatan || ''
                    }]
                  });
                });

                programGroupsContainer.innerHTML = tableHtmlParts.join('');
                programSummary.textContent = `${totalPrograms} program ditemukan untuk semester ini.`;
                updateTherapyNotesField(programData.therapies, existingTherapyNotes);

                if (customGroups.length > 0 && typeof window.renderCustomGroups === 'function') {
                  const customProgramsContainer = document.getElementById('customProgramsContainer');
                  if (customProgramsContainer) {
                    customProgramsContainer.innerHTML = '';
                    window.renderCustomGroups(customGroups);
                  }
                }
              }

              // Open modal
              const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('buatRaporModal'));
              modal.show();
            })
            .catch(error => {
              console.error('Error loading programs:', error);
              showToastr('error', 'Gagal memuat program rapor', 'Error');
            });
        } else {
          showToastr('error', 'Gagal memuat detail rapor', 'Error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToastr('error', 'Terjadi kesalahan saat memuat detail rapor', 'Error');
      });
  }

  function deleteRapor(raporId) {
    if (confirm('Apakah Anda yakin ingin menghapus rapor ini?')) {
      fetch(`/rapor-anak/${raporId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToastr('success', 'Rapor berhasil dihapus', 'Sukses');
            // If element provided, remove row from modal/table immediately
            try {
              if (el && el.closest) {
                const row = el.closest('tr');
                if (row) row.remove();

                // If the riwayat table is now empty, show a friendly message
                const tbody = document.querySelector('#riwayatRaporContent table tbody');
                if (!tbody || tbody.children.length === 0) {
                  document.getElementById('riwayatRaporContent').innerHTML = '<div class="alert alert-info">Belum ada riwayat rapor untuk anak didik ini.</div>';
                }
              }
            } catch (e) {
              // ignore DOM errors
            }

            // Refresh main list as well
            loadRapors();
          } else {
            showToastr('error', data.message || 'Gagal menghapus rapor', 'Error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToastr('error', 'Terjadi kesalahan saat menghapus rapor', 'Error');
        });
    }
  }
</script>
@endpush