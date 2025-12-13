@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Penilaian')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Penilaian Anak</h5>
        <a href="{{ route('assessment.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('assessment.update', $assessment->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Anak Didik <span class="text-danger">*</span></label>
              <select name="anak_didik_id" class="form-select @error('anak_didik_id') is-invalid @enderror" required>
                <option value="">Pilih Anak Didik</option>
                @foreach($anakDidiks as $anak)
                <option value="{{ $anak->id }}" {{ old('anak_didik_id', $assessment->anak_didik_id) == $anak->id ? 'selected' : '' }}>
                  {{ $anak->nama }} ({{ $anak->nis }})
                </option>
                @endforeach
              </select>
              @error('anak_didik_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Konsultan <span class="text-danger">*</span></label>
              <select name="konsultan_id" class="form-select @error('konsultan_id') is-invalid @enderror" required>
                <option value="">Pilih Konsultan</option>
                @foreach($konsultans as $konsultan)
                <option value="{{ $konsultan->id }}" {{ old('konsultan_id', $assessment->konsultan_id) == $konsultan->id ? 'selected' : '' }}>
                  {{ $konsultan->nama }} ({{ $konsultan->spesialisasi }})
                </option>
                @endforeach
              </select>
              @error('konsultan_id')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Kategori Penilaian <span class="text-danger">*</span></label>
              <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                <option value="">Pilih Kategori</option>
                <option value="bina_diri" {{ old('kategori', $assessment->kategori) === 'bina_diri' ? 'selected' : '' }}>Bina Diri</option>
                <option value="akademik" {{ old('kategori', $assessment->kategori) === 'akademik' ? 'selected' : '' }}>Akademik</option>
                <option value="motorik" {{ old('kategori', $assessment->kategori) === 'motorik' ? 'selected' : '' }}>Motorik</option>
                <option value="perilaku" {{ old('kategori', $assessment->kategori) === 'perilaku' ? 'selected' : '' }}>Perilaku</option>
                <option value="vokasi" {{ old('kategori', $assessment->kategori) === 'vokasi' ? 'selected' : '' }}>Vokasi</option>
              </select>
              @error('kategori')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal Penilaian</label>
              <input type="date" name="tanggal_assessment" class="form-control @error('tanggal_assessment') is-invalid @enderror"
                value="{{ old('tanggal_assessment', $assessment->tanggal_assessment?->format('Y-m-d')) }}">
              @error('tanggal_assessment')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Hasil Penilaian</label>
              <textarea name="hasil_penilaian" class="form-control @error('hasil_penilaian') is-invalid @enderror"
                rows="4" placeholder="Deskripsikan hasil penilaian yang telah dilakukan">{{ old('hasil_penilaian', $assessment->hasil_penilaian) }}</textarea>
              @error('hasil_penilaian')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Rekomendasi</label>
              <textarea name="rekomendasi" class="form-control @error('rekomendasi') is-invalid @enderror"
                rows="3" placeholder="Berikan rekomendasi berdasarkan hasil penilaian">{{ old('rekomendasi', $assessment->rekomendasi) }}</textarea>
              @error('rekomendasi')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Saran</label>
              <textarea name="saran" class="form-control @error('saran') is-invalid @enderror"
                rows="3" placeholder="Berikan saran untuk orang tua/wali dan guru">{{ old('saran', $assessment->saran) }}</textarea>
              @error('saran')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="ri-save-line me-2"></i>Perbarui
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