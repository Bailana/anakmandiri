@extends('layouts/contentNavbarLayout')

@section('content')
<div class="card">
  <div class="card-body">
    <h4 class="card-title">Akses Dibatasi</h4>
    <p>Anda tidak memiliki akses untuk melihat PPI untuk <strong>{{ $anak->nama }}</strong>.</p>
    <form method="POST" action="{{ route('ppi.request-access') }}">
      @csrf
      <input type="hidden" name="anak_didik_id" value="{{ $anak->id }}">
      <div class="mb-3">
        <label>Alasan (opsional)</label>
        <textarea name="reason" class="form-control" rows="3"></textarea>
      </div>
      <button class="btn btn-primary">Kirim Permintaan Akses</button>
      <a href="{{ route('ppi.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</div>
@endsection