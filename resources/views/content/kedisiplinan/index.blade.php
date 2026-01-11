@extends('layouts/contentNavbarLayout')

@section('title', 'Kedisiplinan')

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
            <h4 class="mb-0">Kedisiplinan</h4>
            <p class="text-body-secondary mb-0">Kelola catatan kedisiplinan anak didik</p>
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

<!-- Search & Filter -->
<div class="row">
  <div class="col-12 mb-4">
    <form method="GET" action="{{ route('kedisiplinan.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
      <div class="flex-grow-1" style="min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama anak atau NIS..."
          value="{{ request('search') }}">
      </div>
      <button type="submit" class="btn btn-outline-primary" title="Cari">
        <i class="ri-search-line"></i>
      </button>
      <a href="{{ route('kedisiplinan.index') }}" class="btn btn-outline-secondary" title="Reset">
        <i class="ri-refresh-line"></i>
      </a>
    </form>
  </div>
</div>

<!-- Table -->
<!-- Table with Tabs -->
<div class="row">
  <div class="col-12">
    <div class="card">
      @php $activeTab = request('tab', 'penilaian'); @endphp
      <div class="card-body p-2">
        <div class="mb-0" style="overflow-x:auto; white-space:nowrap; -webkit-overflow-scrolling:touch;">
          <ul class="nav nav-tabs mb-0 flex-nowrap" role="tablist" style="display:inline-flex;">
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $activeTab === 'penilaian' ? 'active' : '' }}" id="tab-penilaian"
                data-bs-toggle="tab" data-bs-target="#penilaianTab" type="button" role="tab"
                aria-controls="penilaianTab" aria-selected="{{ $activeTab === 'penilaian' ? 'true' : 'false' }}">
                <i class="ri-file-list-3-line me-2"></i>Penilaian
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $activeTab === 'peringkat' ? 'active' : '' }}" id="tab-peringkat"
                data-bs-toggle="tab" data-bs-target="#peringkatTab" type="button" role="tab"
                aria-controls="peringkatTab" aria-selected="{{ $activeTab === 'peringkat' ? 'true' : 'false' }}">
                <i class="ri-award-line me-2"></i>Peringkat
              </button>
            </li>
          </ul>
        </div>

        <div class="tab-content p-0">
          <div class="tab-pane fade {{ $activeTab === 'penilaian' ? 'show active' : '' }}" id="penilaianTab"
            role="tabpanel" aria-labelledby="tab-penilaian">
            <div class="table-responsive p-3">
              <table class="table table-hover">
                <thead>
                  <tr class="table-light">
                    <th>No</th>
                    <th>Guru Fokus</th>
                    <th>Total Wajib</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @php $no = 1; @endphp
                  @forelse($rows ?? [] as $row)
                  <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $row->guru ? $row->guru->nama : '-' }}</td>
                    <td>{{ $row->assessed_count ?? 0 }}/{{ $row->total_wajib ?? 0 }}</td>
                    <td>
                      @if($row->status === 'Tepat Waktu')
                      <span class="badge bg-success">{{ $row->status }}</span>
                      @elseif($row->status === 'Terlambat')
                      <span class="badge bg-warning text-dark">{{ $row->status }}</span>
                      @elseif($row->status === 'Belum Dinilai')
                      <span class="badge bg-danger text-white">{{ $row->status }}</span>
                      @else
                      <span class="badge bg-secondary">{{ $row->status }}</span>
                      @endif
                    </td>
                    <td>
                      @if($row->guru)
                      <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-riwayat" title="Riwayat"
                        data-guru-id="{{ $row->guru->id }}" data-guru-name="{{ $row->guru->nama }}"><i
                          class="ri-history-line"></i></button>
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center text-body-secondary">Belum ada data kedisiplinan.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade {{ $activeTab === 'peringkat' ? 'show active' : '' }}" id="peringkatTab"
            role="tabpanel" aria-labelledby="tab-peringkat">
            <div class="p-3">
              <form method="GET" action="{{ route('kedisiplinan.index') }}"
                class="mb-2 d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                <label class="mb-0 me-2">Bulan:</label>
                <div class="flex-grow-1" style="min-width:0">
                  <input type="month" name="month" class="form-control form-control-sm"
                    value="{{ request('month', \Carbon\Carbon::now()->format('Y-m')) }}">
                </div>
                <input type="hidden" name="tab" value="peringkat">
                <div class="d-flex gap-2 flex-shrink-0">
                  <button class="btn btn-sm btn-primary w-100 w-md-auto" type="submit" aria-label="Tampilkan">
                    <i class="ri-search-line d-inline d-md-none" aria-hidden="true"></i>
                    <span class="d-none d-md-inline"><i class="ri-search-line me-2"></i>Tampilkan</span>
                  </button>
                  <a href="{{ route('kedisiplinan.index') }}?tab=peringkat"
                    class="btn btn-sm btn-outline-secondary w-100 w-md-auto" aria-label="Reset">
                    <i class="ri-refresh-line d-inline d-md-none" aria-hidden="true"></i>
                    <span class="d-none d-md-inline"><i class="ri-refresh-line me-2"></i>Reset</span>
                  </a>
                </div>
              </form>

              @php
              $ranked = collect($rows ?? [])->sortByDesc('on_time_count')->values();
              $rankedFiltered = $ranked->filter(function($r){ return isset($r->on_time_count) && $r->on_time_count > 0;
              })->values();
              @endphp

              @if($rankedFiltered->isEmpty())
              <div class="text-body-secondary">Belum ada data peringkat.</div>
              @else
              <div class="list-group">
                @foreach($rankedFiltered as $i => $r)
                <div class="list-group-item">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <h6 class="mb-0">{{ $i + 1 }}. {{ $r->guru ? $r->guru->nama : '-' }}</h6>
                      <div class="small text-muted">Tepat Waktu: {{ $r->on_time_count ?? 0 }} · Total Wajib:
                        {{ $r->total_wajib ?? 0 }}</div>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                        data-bs-target="#guruDetail{{ $i }}" aria-expanded="false" aria-label="Lihat Rincian">
                        <i class="ri-eye-line d-inline d-md-none" aria-hidden="true"></i>
                        <span class="d-none d-md-inline"><i class="ri-eye-line me-2"></i>Lihat Rincian</span>
                      </button>
                    </div>
                  </div>

                  <div class="collapse mt-3" id="guruDetail{{ $i }}">
                    <div class="table-responsive">
                      <table class="table table-sm mb-0">
                        <thead>
                          <tr class="table-light">
                            <th style="width:40px">#</th>
                            <th>Anak</th>
                            <th style="width:110px">Wajib</th>
                            <th style="width:120px">Tepat Waktu</th>
                            <th style="width:120px">Dinilai</th>
                            <th style="width:90px">% Tepat</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($r->per_anak as $j => $pa)
                          <tr>
                            <td>{{ $j + 1 }}</td>
                            <td>{{ $pa->anak_nama ?? '-' }}</td>
                            <td>{{ $pa->total_wajib ?? 0 }}</td>
                            <td>{{ $pa->on_time_count ?? 0 }}</td>
                            <td>{{ $pa->assessed_count ?? 0 }}</td>
                            <td>{{ $pa->percent_on_time ?? 0 }}%</td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal Riwayat Kedisiplinan -->
  <div class="modal fade" id="kedisiplinanRiwayatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Riwayat Kedisiplinan Penilaian</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
          <div class="mb-3 d-flex align-items-center">
            <label for="kedisiplinanDate" class="mb-0 me-3">Pilih Tanggal:</label>
            <div class="d-flex align-items-center w-100">
              <input type="date" id="kedisiplinanDate" class="form-control me-2" style="flex:1; min-width:0;">
              <button id="kedisiplinanShowBtn" class="btn btn-primary" title="Tampilkan" aria-label="Tampilkan"><i
                  class="ri-search-line"></i></button>
            </div>
          </div>
          <div id="kedisiplinanRiwayatWrapper">
            <div class="text-center py-4 text-body-secondary">Pilih tanggal lalu tekan "Tampilkan" untuk memuat riwayat.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @endsection


  @push('page-script')
  <script>
    // helper: format date as Indonesian day, dd-mm-yyyy
    function formatDateDisplay(dateStr) {
      try {
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        return `${days[d.getDay()]}, ${dd}-${mm}-${yyyy}`;
      } catch (e) {
        return dateStr;
      }
    }

    // Open modal and prepare date filter for riwayat
    function showKedisiplinanRiwayat(guruId, guruName) {
      const modalEl = document.getElementById('kedisiplinanRiwayatModal');
      modalEl.dataset.guruId = guruId;
      modalEl.dataset.guruName = guruName;
      // default date to today and immediately fetch riwayat for today
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      const todayStr = `${yyyy}-${mm}-${dd}`;
      document.getElementById('kedisiplinanDate').value = todayStr;
      document.getElementById('kedisiplinanRiwayatWrapper').innerHTML =
        '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
      try {
        new bootstrap.Modal(modalEl).show();
      } catch (e) {
        /* ignore */
      }
      // fetch for today
      fetchKedisiplinanRiwayat(guruId, guruName, todayStr);
    }

    async function fetchKedisiplinanRiwayat(gid, gname, date) {
      const wrapper = document.getElementById('kedisiplinanRiwayatWrapper');
      wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
      try {
        const res = await fetch(`/kedisiplinan/${gid}/riwayat?date=${encodeURIComponent(date)}`, {
          credentials: 'same-origin'
        });
        const json = await res.json();
        if (!json || !json.success) {
          wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat riwayat.</div>';
          return;
        }
        const data = json.riwayat || [];
        if (data.length === 0 || !data[0].items.length) {
          wrapper.innerHTML =
            '<div class="text-center py-4 text-body-secondary">Tidak ada data pada tanggal tersebut.</div>';
          return;
        }
        let html = '';
        data.forEach(group => {
          html += `<div class="mt-3"><strong>${formatDateDisplay(group.date)}</strong>`;
          const byChild = {};
          (group.items || []).forEach(it => {
            const child = it.anak || '-';
            if (!byChild[child]) byChild[child] = [];
            byChild[child].push(it);
          });
          Object.keys(byChild).forEach(child => {
            html += `<div class="mt-2"><div class="fw-semibold">${child}</div><div class="list-group mt-2">`;
            (byChild[child] || []).forEach(it => {
              html += `<div class="list-group-item d-flex justify-content-between align-items-start">
								<div>
									<div class="fw-semibold">${it.program_display || it.program || '-'}</div>
									<div class="text-muted small">Waktu: ${it.waktu || '-'}</div>
									<div class="text-muted small">Penilai: ${it.penilai_nama ? it.penilai_nama : '-'}</div>
								</div>
								<div>
									<span class="badge ${it.status === 'Tepat Waktu' ? 'bg-success' : (it.status === 'Belum Dinilai' ? 'bg-danger text-white' : 'bg-warning text-dark')}">${it.status}</span>
								</div>
							</div>`;
            });
            html += `</div></div>`;
          });
          html += '</div>';
        });
        wrapper.innerHTML = html;
      } catch (err) {
        console.error(err);
        // if server returned HTML error page, show a concise message
        wrapper.innerHTML =
          '<div class="text-center py-4 text-body-secondary">Terjadi kesalahan saat memuat riwayat.</div>';
      }
    }

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-riwayat');
      if (!btn) return;
      const gid = btn.getAttribute('data-guru-id');
      const gname = btn.getAttribute('data-guru-name');
      if (gid) showKedisiplinanRiwayat(gid, gname);
    });

    // When user clicks Tampilkan, fetch riwayat for selected date
    document.addEventListener('click', function (e) {
      if (!e.target) return;
      const btn = e.target.closest('#kedisiplinanShowBtn');
      if (!btn) return;
      const modalEl = document.getElementById('kedisiplinanRiwayatModal');
      const gid = modalEl.dataset.guruId;
      const gname = modalEl.dataset.guruName;
      const date = document.getElementById('kedisiplinanDate').value;
      if (!date) {
        alert('Pilih tanggal terlebih dahulu');
        return;
      }

      (async () => {
        const wrapper = document.getElementById('kedisiplinanRiwayatWrapper');
        wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Memuat data...</div>';
        try {
          const res = await fetch(`/kedisiplinan/${gid}/riwayat?date=${encodeURIComponent(date)}`, {
            credentials: 'same-origin'
          });
          const json = await res.json();
          if (!json || !json.success) {
            wrapper.innerHTML = '<div class="text-center py-4 text-body-secondary">Gagal memuat riwayat.</div>';
            return;
          }
          const data = json.riwayat || [];
          if (data.length === 0 || !data[0].items.length) {
            wrapper.innerHTML =
              '<div class="text-center py-4 text-body-secondary">Tidak ada data pada tanggal tersebut.</div>';
            return;
          }
          let html = '';
          data.forEach(group => {
            html += `<div class="mt-3"><strong>${formatDateDisplay(group.date)}</strong>`;
            const byChild = {};
            (group.items || []).forEach(it => {
              const child = it.anak || '-';
              if (!byChild[child]) byChild[child] = [];
              byChild[child].push(it);
            });
            Object.keys(byChild).forEach(child => {
              html +=
                `<div class="mt-2"><div class="fw-semibold">${child}</div><div class="list-group mt-2">`;
              (byChild[child] || []).forEach(it => {
                html += `<div class="list-group-item d-flex justify-content-between align-items-start">
									<div>
										<div class="fw-semibold">${it.program_display || it.program || '-'}</div>
										<div class="text-muted small">Waktu: ${it.waktu || '-'}</div>
										<div class="text-muted small">Penilai: ${it.penilai_nama ? it.penilai_nama : '-'}</div>
									</div>
									<div>
										<span class="badge ${it.status === 'Tepat Waktu' ? 'bg-success' : (it.status === 'Belum Dinilai' ? 'bg-danger text-white' : 'bg-warning text-dark')}">${it.status}</span>
									</div>
								</div>`;
              });
              html += `</div></div>`;
            });
            html += '</div>';
          });
          wrapper.innerHTML = html;
        } catch (err) {
          console.error(err);
          wrapper.innerHTML =
            '<div class="text-center py-4 text-body-secondary">Terjadi kesalahan saat memuat riwayat.</div>';
        }
      })();
    });

  </script>
  @endpush
