@extends('layouts.contentNavbarLayout')

@section('title', 'Pasien Terapis')

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
            <h4 class="mb-0">Pasien Terapis</h4>
            <p class="text-body-secondary mb-0">Daftar pasien yang mengikuti terapis</p>
          </div>
          <div class="d-flex gap-2">
            @if(isset($user) && in_array($user->role, ['admin','terapis']))
            <a href="{{ route('terapis.pasien.create') }}" class="btn btn-primary">
              <i class="ri-add-line me-2"></i>Tambah Pasien Terapis
            </a>
            @endif

            @if(isset($user) && $user->role === 'admin')
            <form method="get" class="d-flex gap-2 align-items-end" action="{{ route('terapis.pasien.index') }}">
              <div style="min-width: 200px;">
                <select name="user_id" class="form-select">
                  <option value="">-- Semua Terapis --</option>
                  @foreach($therapists as $t)
                  <option value="{{ $t->id }}" @if((string)$t->id === (string)($selectedTherapisId ?? '')) selected @endif>{{ $t->name }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn btn-outline-primary">Filter</button>
              <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
            @endif
          </div>
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

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover" id="patientsTable" style="font-size: 1rem;">
          <thead>
            <tr class="table-light">
              <th>No</th>
              <th>Anak Didik</th>
              <th>Tanggal Lahir</th>
              <th>Terapis</th>
              <th>Status</th>
              <th>Tgl Mulai</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($assignments as $index => $assign)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                @if($assign->anakDidik)
                <a href="{{ route('anak-didik.show', $assign->anak_didik) }}">{{ $assign->anakDidik->nama }}</a>
                @else
                -
                @endif
              </td>
              <td>{{ optional($assign->anakDidik->tanggal_lahir)->format('Y-m-d') ?? '-' }}</td>
              <td>{{ $assign->user->name ?? '-' }}</td>
              <td>{{ $assign->status ?? '-' }}</td>
              <td>{{ optional($assign->tanggal_mulai)->format('Y-m-d') ?? '-' }}</td>
              <td>
                @if($assign->anakDidik)
                <a class="btn btn-sm btn-outline-primary" href="{{ route('anak-didik.show', $assign->anak_didik) }}">Lihat</a>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7">
                <div class="alert alert-warning mb-0" role="alert">
                  <i class="ri-alert-line me-2"></i>Tidak ada pasien.
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-body-secondary">
          Menampilkan {{ $assignments->count() }} data
        </div>
        <nav>
          @if(method_exists($assignments, 'links'))
          {{ $assignments->links('pagination::bootstrap-4') }}
          @endif
        </nav>
      </div>
    </div>
  </div>
</div>

@endsection