@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Program Anak')

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Program Anak</h5>
        <a href="{{ route('program-anak.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('program-anak.store') }}">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="konsultan_id" class="form-label">Nama Konsultan</label>
              <select name="konsultan_id" id="konsultan_id" class="form-select" required>
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" data-spesialisasi="{{ strtolower($konsultan->spesialisasi) }}">{{ $konsultan->nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="anak_didik_id" class="form-label">Nama Anak Didik</label>
              <select name="anak_didik_id" id="anak_didik_id" class="form-select" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}">{{ $anak->nama }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-12" id="daftarProgramAnakWrapper">
            <label class="form-label">Daftar Program Anak</label>
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="programItemsTable">
                <thead class="table-light">
                  <tr>
                    <th style="width:15%">Kode Program</th>
                    <th style="width:20%">Nama Program</th>
                    <th style="width:35%">Tujuan</th>
                    <th style="width:35%">Aktivitas</th>
                    <th style="width:10%">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><input type="text" name="program_items[0][kode_program]" class="form-control" required></td>
                    <td><input type="text" name="program_items[0][nama_program]" class="form-control" required></td>
                    <td><textarea name="program_items[0][tujuan]" class="form-control" rows="1" required></textarea></td>
                    <td><textarea name="program_items[0][aktivitas]" class="form-control" rows="1" required></textarea></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
                  </tr>
                </tbody>
                <!-- Tombol tambah baris di dalam tabel -->
                <tr>
                  <td colspan="4">
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnTambahBaris"><i class="ri-add-line"></i> Tambah Baris</button>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          @csrf

          <div class="row mb-3 mt-2">
            <div class="col-md-6">
              <label for="periode_mulai" class="form-label">Periode Mulai</label>
              <input type="date" name="periode_mulai" id="periode_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="periode_selesai" class="form-label">Periode Selesai</label>
              <input type="date" name="periode_selesai" id="periode_selesai" class="form-control" required>
            </div>
          </div>
          <!-- Kolom khusus untuk konsultan psikologi -->
          <div class="row mb-3" id="psikologiFields" style="display:none;">
            <div class="col-md-12 mb-2">
              <label for="latar_belakang" class="form-label">Latar Belakang</label>
              <textarea name="latar_belakang" id="latar_belakang" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="metode_assessment" class="form-label">Metode Assessment</label>
              <textarea name="metode_assessment" id="metode_assessment" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="hasil_assessment" class="form-label">Hasil Assessment</label>
              <textarea name="hasil_assessment" id="hasil_assessment" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="diagnosa" class="form-label">Diagnosa</label>
              <textarea name="diagnosa" id="diagnosa" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="kesimpulan" class="form-label">Kesimpulan</label>
              <textarea name="kesimpulan" id="kesimpulan" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12 mb-2">
              <label for="rekomendasi" class="form-label">Rekomendasi</label>
              <textarea name="rekomendasi" id="rekomendasi" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="keterangan" class="form-label">Keterangan</label>
              <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-start gap-2">
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-2"></i>Simpan</button>
            <a href="{{ route('program-anak.index') }}" class="btn btn-outline-danger"><i class="ri-close-line me-2"></i>Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@push('page-script')
<script>
  let barisIdx = 1;
  document.getElementById('btnTambahBaris').addEventListener('click', function() {
    const tbody = document.querySelector('#programItemsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
    <td><input type="text" name="program_items[${barisIdx}][kode_program]" class="form-control" required></td>
    <td><input type="text" name="program_items[${barisIdx}][nama_program]" class="form-control" required></td>
    <td><textarea name="program_items[${barisIdx}][tujuan]" class="form-control" rows="1" required></textarea></td>
    <td><textarea name="program_items[${barisIdx}][aktivitas]" class="form-control" rows="1" required></textarea></td>
    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris"><i class="ri-delete-bin-line"></i></button></td>
  `;
    tbody.appendChild(tr);
    barisIdx++;
  });
  document.querySelector('#programItemsTable').addEventListener('click', function(e) {
    if (e.target.closest('.btn-hapus-baris')) {
      const tr = e.target.closest('tr');
      if (tr.parentNode.children.length > 1) tr.remove();
    }
  });

  // Tampilkan/hidden form daftar program anak & field psikologi sesuai konsultan
  function toggleDaftarProgramAnak() {
    const select = document.getElementById('konsultan_id');
    const selected = select.options[select.selectedIndex];
    const spesialisasi = selected ? selected.getAttribute('data-spesialisasi') : '';
    const wrapper = document.getElementById('daftarProgramAnakWrapper');
    const psikologiFields = document.getElementById('psikologiFields');
    // Tampilkan daftar program anak hanya untuk wicara/sensori integrasi
    if (spesialisasi === 'wicara' || spesialisasi === 'sensori integrasi') {
      wrapper.style.display = '';
    } else {
      wrapper.style.display = 'none';
    }
    // Tampilkan field psikologi jika konsultan psikologi
    if (spesialisasi === 'psikologi') {
      psikologiFields.style.display = '';
    } else {
      psikologiFields.style.display = 'none';
    }
  }
  document.getElementById('konsultan_id').addEventListener('change', toggleDaftarProgramAnak);
  // Inisialisasi saat load
  toggleDaftarProgramAnak();
</script>
@endpush
@endsection