@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Pasien Terapis')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Edit Pasien Terapis</h4>
        <form method="POST" action="{{ route('terapis.pasien.update', $assignment->id) }}">
          @csrf
          @method('PUT')

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
            {{-- Kolom terapis, jenis terapi, nama terapis disembunyikan pada edit --}}
            <input type="hidden" name="user_id" value="{{ $assignment->user_id }}">
            <input type="hidden" name="jenis_terapi" value="{{ $assignment->jenis_terapi }}">
            <input type="hidden" name="terapis_nama" value="{{ $assignment->terapis_nama }}">

            <div class="col-md-6">
              <label class="form-label">Anak Didik</label>
              <input type="text" class="form-control" value="{{ $assignment->anakDidik->nama ?? '-' }}" disabled>
              <input type="hidden" name="anak_didik_id" value="{{ $assignment->anak_didik_id }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="aktif" @if(old('status', $assignment->status)=='aktif') selected @endif>Aktif</option>
                <option value="non-aktif" @if(old('status', $assignment->status)=='non-aktif') selected @endif>Tidak Aktif</option>
              </select>
            </div>

            {{-- Kolom tanggal mulai & jam mulai jadwal disembunyikan pada edit --}}

            <div class="col-12">
              <button type="submit" class="btn btn-primary">
                <i class="ri-save-2-line" style="margin-right:4px;"></i> Perbarui
              </button>
              <a href="{{ route('terapis.pasien.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-go-back-line" style="margin-right:4px;"></i> Batal
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
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

    // when page loads, normalize names
    normalizeNames();
    updateRemoveButtons();
  });
</script>
@endpush