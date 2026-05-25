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

<div class="modal fade" id="buatRaporModal" tabindex="-1" aria-labelledby="buatRaporModalLabel" aria-hidden="true">
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

          <div class="row mt-3 g-3">
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
  let isConsultantEducation = {{ isset($isConsultantEducation) && $isConsultantEducation ? 'true' : 'false'}};

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

        // Build subgroups by program code prefix (A, B, etc.)
        const subMap = {};
        group.programs.forEach(p => {
          const name = (p.nama_program || '').trim();
          const m = name.match(/^([A-Za-z])/);
          const key = m ? m[1].toUpperCase() : '_';
          if (!subMap[key]) subMap[key] = [];
          subMap[key].push(p);
        });

        // mapping for special subgroup titles
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

        // render each subgroup inside the category
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
      // clear saran fields
      const saranGuruInput = document.getElementById('saran_guru');
      const saranOrtuInput = document.getElementById('saran_orang_tua');
      if (saranGuruInput) saranGuruInput.value = '';
      if (saranOrtuInput) saranOrtuInput.value = '';
    }

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

    // When modal is fully hidden, always reset to create mode to avoid leftover edit state
    modalEl.addEventListener('hidden.bs.modal', function() {
      resetFormToCreateMode();
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

      // Collect catatan from program-level textareas and group-level notes
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
                kategori_label: groupLabel
              });
            }
          }
        });
      });

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
            saran_orang_tua: (document.getElementById('saran_orang_tua') || {}).value || ''
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
      const used = new Set();

      if (pgData.groups && pgData.groups.length > 0) {
        let gIndex = 1;
        pgData.groups.forEach((group) => {
          const programs = (group.programs || []).filter(p => !!p.nama_program);
          if (programs.length === 0) return;

          // Build header with numbering
          groupHtml += `<div style="font-weight:700;font-size:15px;margin-top:18px;margin-bottom:6px;">${gIndex}. ${group.label}</div>`;

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
              if (matched) used.add(matched.id);
              const nilai = matched ? matched.nilai_huruf : '-';
              const cat = matched && matched.catatan ? String(matched.catatan).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '-';
              groupHtml += `<tr><td class="rapor-cell-program">${p.nama_program}</td><td class="rapor-cell-nilai text-center align-middle"><span class="badge ${getBadgeClass(nilai)}">${nilai}</span></td><td class="rapor-cell-catatan" style="white-space: pre-wrap; word-break: break-word; vertical-align: top;">${cat}</td></tr>`;
            });

            groupHtml += `</tbody></table></div>`;
          });

          const gNote = (rapor && rapor.group_notes && rapor.group_notes[group.label]) ? rapor.group_notes[group.label] : null;
          const safe = gNote ? String(gNote).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : '-';
          groupHtml += `<div style="margin-top:8px; font-size:13px; color:#374151;">\n<strong>Catatan (${group.label}):</strong><div style="margin-top:6px;">${safe}</div></div>`;
          gIndex++;
        });
      }

      // Any remaining items that weren't matched -> Lainnya
      const others = (rapor.items || []).filter(it => !used.has(it.id));
      if (others.length > 0) {
        // number for Lainnya should continue after existing groups
        const otherLabelNumber = typeof gIndex !== 'undefined' ? gIndex : 1;
        groupHtml += `<div style="font-weight:700;font-size:15px;margin-top:18px;margin-bottom:6px;">${otherLabelNumber}. Lainnya</div>`;
        groupHtml += `<div class="table-responsive"><table class="table table-bordered mb-2 rapor-table" style="table-layout:fixed;width:100%;"><thead class="table-light"><tr><th style="width:58%;">Program</th><th class="text-center" style="width:18%;">Nilai</th><th>Catatan</th></tr></thead><tbody>`;
        others.forEach(item => {
          const cat = item.catatan ? String(item.catatan).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '-';
          groupHtml += `<tr><td class="rapor-cell-program">${item.nama_program}</td><td class="rapor-cell-nilai text-center align-middle"><span class="badge ${getBadgeClass(item.nilai_huruf)}">${item.nilai_huruf}</span></td><td class="rapor-cell-catatan" style="white-space: pre-wrap; word-break: break-word; vertical-align: top;">${cat}</td></tr>`;
        });
        groupHtml += `</tbody></table></div>`;
        const gNote = (rapor && rapor.group_notes && (rapor.group_notes['Lainnya'] || rapor.group_notes['lainnya'])) ? (rapor.group_notes['Lainnya'] || rapor.group_notes['lainnya']) : null;
        const safe = gNote ? String(gNote).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') : '-';
        groupHtml += `<div style="margin-top:8px; font-size:13px; color:#374151;">\n<strong>Catatan (Lainnya):</strong><div style="margin-top:6px;">${safe}</div></div>`;
      }

      content.innerHTML = `
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <p class="mb-1"><strong>Nama Anak Didik:</strong></p>
            <p>${rapor.anak_didik_nama}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Kelas:</strong></p>
            <p>${rapor.kelas || '-'}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Semester:</strong></p>
            <p>Semester ${rapor.semester}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Tahun Pelajaran:</strong></p>
            <p>${rapor.tahun_pelajaran}</p>
          </div>
        </div>
        ${groupHtml}
        <div class="row g-3 mt-4">
          <div class="col-md-6">
            <p class="mb-1"><strong>Saran Guru:</strong></p>
            <p style="white-space: pre-wrap;">${saranGuru || '-'}</p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Saran Orang Tua:</strong></p>
            <p style="white-space: pre-wrap;">${saranOrtu || '-'}</p>
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

                rapor.items.forEach(item => {
                  itemsMap[normalizeProgramName(item.nama_program)] = item;
                });

                // Update groups with catatan from rapor and collect group notes
                programData.groups.forEach(group => {
                  group.programs.forEach(program => {
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

                programGroupsContainer.innerHTML = tableHtmlParts.join('');
                programSummary.textContent = `${totalPrograms} program ditemukan untuk semester ini.`;
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