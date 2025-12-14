@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Penilaian')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
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
        <form action="{{ route('assessment.store') }}" method="POST">
          @csrf


          <div class="row mb-3 g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select name="anak_didik_id" class="form-select @error('anak_didik_id') is-invalid @enderror" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}" {{ old('anak_didik_id') == $anak->id ? 'selected' : '' }}>
                  {{ $anak->nama }} ({{ $anak->nis }})
                </option>
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Kategori Penilaian <span class="text-danger">*</span></label>
              <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                <option value="">Pilih Kategori</option>
                <option value="bina_diri" {{ old('kategori') === 'bina_diri' ? 'selected' : '' }}>Bina Diri</option>
                <option value="akademik" {{ old('kategori') === 'akademik' ? 'selected' : '' }}>Akademik</option>
                <option value="motorik" {{ old('kategori') === 'motorik' ? 'selected' : '' }}>Motorik</option>
                <option value="perilaku" {{ old('kategori') === 'perilaku' ? 'selected' : '' }}>Perilaku</option>
                <option value="vokasi" {{ old('kategori') === 'vokasi' ? 'selected' : '' }}>Vokasi</option>
              </select>
              @error('kategori')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Program</label>
              <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror">
                <option value="">Pilih Program</option>
                {{-- Opsi program akan diisi via JS jika ingin dinamis, atau bisa diisi semua program dari backend jika ingin statis --}}
              </select>
              @error('program_id')
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
            <div class="col-md-4">
              <label class="form-label">Tanggal Penilaian</label>
              <input type="date" name="tanggal_assessment" class="form-control @error('tanggal_assessment') is-invalid @enderror"
                value="{{ old('tanggal_assessment') }}">
              @error('tanggal_assessment')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            @if(auth()->user() && auth()->user()->role === 'guru')
            <div class="col-md-4">
              <label class="form-label">Penilaian Perkembangan <span class="text-danger">*</span></label>
              <select name="perkembangan" class="form-select @error('perkembangan') is-invalid @enderror" required>
                <option value="">Pilih Penilaian</option>
                <option value="1" {{ old('perkembangan') == '1' ? 'selected' : '' }}>1 - Ada perkembangan 25%</option>
                <option value="2" {{ old('perkembangan') == '2' ? 'selected' : '' }}>2 - Ada perkembangan 50%</option>
                <option value="3" {{ old('perkembangan') == '3' ? 'selected' : '' }}>3 - Ada perkembangan 75%</option>
                <option value="4" {{ old('perkembangan') == '4' ? 'selected' : '' }}>4 - Ada perkembangan 100%</option>
              </select>
              @error('perkembangan')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            @endif
          </div>


          <!-- Penilaian Kemampuan Anak -->
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Penilaian Kemampuan Anak</label>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40%">Kemampuan</th>
                      <th colspan="5" class="text-center">Skala Penilaian</th>
                    </tr>
                    <tr>
                      <th></th>
                      <th class="text-center">1<br><small>Tidak Mampu</small></th>
                      <th class="text-center">2<br><small>Kurang Mampu</small></th>
                      <th class="text-center">3<br><small>Cukup Mampu</small></th>
                      <th class="text-center">4<br><small>Mampu</small></th>
                      <th class="text-center">5<br><small>Sangat Mampu</small></th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $kemampuanList = old('kemampuan', [
                    ['judul' => 'Kontak mata', 'skala' => ''],
                    ['judul' => 'Atensi', 'skala' => ''],
                    ['judul' => 'Simbolik play', 'skala' => ''],
                    ]);
                    @endphp
                    @foreach($kemampuanList as $i => $kemampuan)
                    <tr id="row-kemampuan-{{ $i }}">
                      <td>
                        <div class="input-group">
                          <input type="text" name="kemampuan[{{ $i }}][judul]" value="{{ $kemampuan['judul'] }}" class="form-control" required>
                          <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kemampuan" onclick="window.hapusKemampuan({{ $i }})"><i class="ri-delete-bin-line"></i></button>
                        </div>
                      </td>
                      @for($skala=1; $skala<=5; $skala++)
                        <td class="text-center">
                        <input type="radio" name="kemampuan[{{ $i }}][skala]" value="{{ $skala }}" {{ (isset($kemampuan['skala']) && $kemampuan['skala']==$skala) ? 'checked' : '' }} required>
                        </td>
                        @endfor
                    </tr>
                    @endforeach
                    <tr id="row-tambah-kemampuan"></tr>
                    <tr>
                      <td colspan="6">
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-tambah-kemampuan">
                          <i class="ri-add-line"></i> Tambah Kemampuan Lainnya
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          @push('page-script')
          <script>
            // Fungsi hapusKemampuan harus global agar bisa dipanggil dari onclick HTML
            window.hapusKemampuan = function(idx) {
              const row = document.getElementById(`row-kemampuan-${idx}`);
              if (row) row.remove();
            };
            document.addEventListener('DOMContentLoaded', function() {
              let kemampuanIndex = {
                {
                  count($kemampuanList)
                }
              };
              const btnTambah = document.getElementById('btn-tambah-kemampuan');
              if (btnTambah) {
                btnTambah.onclick = function() {
                  const tbody = document.querySelector('table tbody');
                  // Cari index terbesar yang masih ada
                  const rows = Array.from(document.querySelectorAll('tr[id^="row-kemampuan-"]'));
                  if (rows.length > 0) {
                    const lastIdx = rows.map(row => parseInt(row.id.replace('row-kemampuan-', ''))).sort((a, b) => b - a)[0];
                    kemampuanIndex = lastIdx + 1;
                  }
                  const tr = document.createElement('tr');
                  tr.id = `row-kemampuan-${kemampuanIndex}`;
                  let html = `<td><div class=\"input-group\"><input type=\"text\" name=\"kemampuan[${kemampuanIndex}][judul]\" class=\"form-control\" required><button type=\"button\" class=\"btn btn-outline-danger btn-sm btn-hapus-kemampuan\" onclick=\"window.hapusKemampuan(${kemampuanIndex})\"><i class=\"ri-delete-bin-line\"></i></button></div></td>`;
                  for (let skala = 1; skala <= 5; skala++) {
                    html += `<td class=\"text-center\"><input type=\"radio\" name=\"kemampuan[${kemampuanIndex}][skala]\" value=\"${skala}\" required></td>`;
                  }
                  tr.innerHTML = html;
                  tbody.insertBefore(tr, document.getElementById('row-tambah-kemampuan'));
                  kemampuanIndex++;
                };
              }
            });
          </script>
          @endpush

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
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="ri-save-line me-2"></i>Simpan
              </button>
              <a href="{{ route('assessment.index') }}" class="btn btn-outline-secondary">
                <i class="ri-close-line me-2"></i>Batal
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection