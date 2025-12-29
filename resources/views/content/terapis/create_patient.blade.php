@extends('layouts.contentNavbarLayout')

@section('title', isset($assignment) ? 'Edit Pasien Terapis' : 'Tambah Pasien Terapis')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">{{ isset($assignment) ? 'Edit Pasien Terapis' : 'Tambah Pasien Terapis' }}</h4>
        @if(isset($assignment))
        <form method="POST" action="{{ route('terapis.pasien.update', $assignment->id) }}">
          @csrf
          @method('PUT')
          @else
          <form method="POST" action="{{ route('terapis.pasien.store') }}">
            @csrf
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
              <strong>Terjadi kesalahan:</strong>
              <ul class="mb-0">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
            @endif
            <div class="row g-3">
              @if(isset($user) && $user->role === 'admin')
              <div class="col-md-6">
                <label class="form-label">Terapis</label>
                <select name="user_id" class="form-select">
                  <option value="">-- Pilih Terapis --</option>
                  @foreach($therapists as $t)
                  <option value="{{ $t->id }}" @if((string)$t->id === (string)($selectedTherapisId ?? '')) selected @endif>{{ $t->name }}</option>
                  @endforeach
                </select>
              </div>
              @endif

              @if(isset($user) && $user->role === 'terapis')
              <input type="hidden" name="user_id" value="{{ $user->id }}">
              @endif

              <div class="col-md-6">
                <label class="form-label">Anak Didik</label>
                <select name="anak_didik_id" class="form-select" required>
                  <option value="">-- Pilih Anak Didik --</option>
                  @foreach($anakDidiks as $a)
                  <option value="{{ $a->id }}" @if(old('anak_didik_id', isset($assignment) ? $assignment->anak_didik_id : '') == $a->id) selected @endif>{{ $a->nama }}</option>
                  @endforeach
                </select>
              </div>

              <!-- removed adjacent Tanggal Mulai and Jam Mulai as requested -->

              <div class="col-md-6">
                <label class="form-label">Jenis Terapi</label>
                <select name="jenis_terapi" class="form-select">
                  <option value="">-- Pilih Jenis Terapi --</option>
                  <option value="Terapi Wicara" @if(old('jenis_terapi', isset($assignment) ? $assignment->jenis_terapi : '')=='Terapi Wicara') selected @endif>Terapi Wicara</option>
                  <option value="Terapi Sensori Integrasi" @if(old('jenis_terapi', isset($assignment) ? $assignment->jenis_terapi : '')=='Terapi Sensori Integrasi') selected @endif>Terapi Sensori Integrasi</option>
                  <option value="Terapi Perilaku" @if(old('jenis_terapi', isset($assignment) ? $assignment->jenis_terapi : '')=='Terapi Perilaku') selected @endif>Terapi Perilaku</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Nama Terapis</label>
                <select name="terapis_nama" class="form-select">
                  <option value="">-- Pilih Nama Terapis --</option>
                  @foreach($therapists as $t)
                  <option value="{{ $t->name }}" @if(old('terapis_nama', isset($assignment) ? $assignment->terapis_nama : (isset($user) && $user->role === 'terapis' ? $user->name : ''))==$t->name) selected @endif>{{ $t->name }}</option>
                  @endforeach
                </select>
              </div>

              @if(isset($assignment))
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="aktif" @if(old('status', $assignment->status ?? '')=='aktif') selected @endif>Aktif</option>
                  <option value="non-aktif" @if(old('status', $assignment->status ?? '')=='non-aktif') selected @endif>Tidak Aktif</option>
                </select>
              </div>
              @endif

              <div class="col-12 mt-3">
                <div id="schedules-wrapper">
                  @php
                  $initialSchedules = old('schedules') ?? (isset($assignment) ? $assignment->schedules->map(function($s){ return ['tanggal_mulai' => $s->tanggal_mulai?->format('Y-m-d'), 'jam_mulai' => $s->jam_mulai, 'jenis_terapi' => $s->jenis_terapi]; })->toArray() : [['tanggal_mulai' => null, 'jam_mulai' => null, 'jenis_terapi' => null]]);
                  @endphp
                  @foreach($initialSchedules as $i => $sch)
                  <div class="row g-2 align-items-end schedule-row">
                    <div class="col-md-6">
                      <label class="form-label small">Tanggal Mulai</label>
                      <input type="date" name="schedules[{{ $i }}][tanggal_mulai]" class="form-control" value="{{ $sch['tanggal_mulai'] ?? '' }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                      <div style="flex:11; display:flex; flex-direction:column;">
                        <label class="form-label small">Jam Mulai</label>
                        @php
                        $__jam = $sch['jam_mulai'] ?? '';
                        $__jam = str_replace('.', ':', $__jam);
                        $__jam = preg_replace('/^(\d{1,2}:\d{2}).*/', '$1', $__jam);
                        if (preg_match('/^\d:\d{2}$/', $__jam)) $__jam = '0' . $__jam;
                        @endphp
                        <input type="time" name="schedules[{{ $i }}][jam_mulai]" class="form-control" value="{{ $__jam }}">
                      </div>
                      <div style="flex:1; display:flex; align-items:flex-end; margin-left:0.5rem;">
                        <button type="button" class="btn btn-icon btn-outline-danger btn-remove-schedule" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="mt-2">
                  <button type="button" id="add-schedule" class="btn btn-sm btn-secondary">+ Tambah Jadwal</button>
                </div>
              </div>

              <div class="col-12">
                <button class="btn btn-primary">
                  <i class="ri-save-line me-1"></i>{{ isset($assignment) ? 'Perbarui' : 'Simpan' }}
                </button>
                <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary">
                  <i class="ri-close-line me-1"></i>Batal
                </a>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- @section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  /* reduce right padding for schedule delete button to make it more compact */
  .btn-remove-schedule {
    padding-right: 0.35rem !important;
    padding-left: 0.35rem !important;
  }

  /* ensure the icon inside is centered when using reduced padding */
  .btn-remove-schedule i {
    margin: 0;
  }
</style>
@endsection -->

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
      $('select[name="anak_didik_id"]').select2({
        width: '100%',
        placeholder: '-- Pilih Anak Didik --',
        allowClear: true,
      });
    }
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var wrapper = document.getElementById('schedules-wrapper');
    var addBtn = document.getElementById('add-schedule');

    function updateRemoveButtons() {
      document.querySelectorAll('.btn-remove-schedule').forEach(function(btn) {
        btn.removeEventListener('click', onRemove);
        btn.addEventListener('click', onRemove);
      });
    }

    function onRemove(e) {
      var row = e.target.closest('.schedule-row');
      if (!row) return;
      // if only one row, clear inputs instead of removing
      if (wrapper.querySelectorAll('.schedule-row').length === 1) {
        row.querySelectorAll('input, select').forEach(function(el) {
          el.value = '';
        });
      } else {
        row.remove();
        normalizeNames();
      }
    }

    function normalizeNames() {
      wrapper.querySelectorAll('.schedule-row').forEach(function(row, idx) {
        var dateInput = row.querySelector('input[type="date"][name^="schedules"]');
        var jam = row.querySelector('input[type="time"][name^="schedules"]');
        if (dateInput) dateInput.setAttribute('name', 'schedules[' + idx + '][tanggal_mulai]');
        if (jam) jam.setAttribute('name', 'schedules[' + idx + '][jam_mulai]');
      });
    }

    addBtn.addEventListener('click', function() {
      var count = wrapper.querySelectorAll('.schedule-row').length;
      var div = document.createElement('div');
      div.className = 'row g-2 align-items-end schedule-row';
      div.innerHTML = `
        <div class="col-md-6">
          <label class="form-label small">Tanggal Mulai</label>
          <input type="date" name="schedules[${count}][tanggal_mulai]" class="form-control">
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <div style="flex:11; display:flex; flex-direction:column;">
            <label class="form-label small">Jam Mulai</label>
            <input type="time" name="schedules[${count}][jam_mulai]" class="form-control">
          </div>
          <div style="flex:1; display:flex; align-items:flex-end; margin-left:0.5rem;">
            <button type="button" class="btn btn-icon btn-outline-danger btn-remove-schedule" title="Hapus"><i class="ri-delete-bin-line"></i></button>
          </div>
        </div>
      `;
      wrapper.appendChild(div);
      updateRemoveButtons();
      normalizeNames();
    });

    updateRemoveButtons();
  });
</script>
@endpush