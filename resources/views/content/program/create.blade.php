@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Program Pembelajaran')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Program Pembelajaran</h5>
        <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm">
          <i class="ri-arrow-left-line me-2"></i>Kembali
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('program.store') }}" method="POST">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
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
            <div class="col-md-6">
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
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nama Program <span class="text-danger">*</span></label>
              <input type="text" name="nama_program" class="form-control @error('nama_program') is-invalid @enderror"
                placeholder="Nama program pembelajaran" value="{{ old('nama_program') }}" required>
              @error('nama_program')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Kategori <span class="text-danger">*</span></label>
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
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                rows="3" placeholder="Deskripsi program">{{ old('deskripsi') }}</textarea>
              @error('deskripsi')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Target Pembelajaran</label>
              <textarea name="target_pembelajaran" class="form-control @error('target_pembelajaran') is-invalid @enderror"
                rows="3" placeholder="Target pembelajaran yang ingin dicapai">{{ old('target_pembelajaran') }}</textarea>
              @error('target_pembelajaran')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                value="{{ old('tanggal_mulai') }}">
              @error('tanggal_mulai')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                value="{{ old('tanggal_selesai') }}">
              @error('tanggal_selesai')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Catatan Konsultan</label>
              <textarea name="catatan_konsultan" class="form-control @error('catatan_konsultan') is-invalid @enderror"
                rows="3" placeholder="Catatan atau evaluasi dari konsultan">{{ old('catatan_konsultan') }}</textarea>
              @error('catatan_konsultan')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="ri-save-line me-2"></i>Simpan
              </button>
              <a href="{{ route('program.index') }}" class="btn btn-outline-secondary">
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