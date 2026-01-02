@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard - Guru')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection
@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection
@section('page-script')
@vite(['resources/assets/js/dashboards-analytics.js'])
@endsection

@section('content')
<div class="row gy-6">
  <!-- Welcome Card -->
  <div class="col-md-12">
    <div class="card bg-success text-white">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar avatar-xl">
            @if(Auth::user()->avatar)
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
            @else
            <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="rounded-circle" />
            @endif
          </div>
          <div>
            <h5 class="card-title text-white mb-2">Halo {{ Auth::user()->name }}! 📚</h5>
            <p class="mb-2 text-white-50">Dashboard Guru Fokus</p>
            <p class="mb-0 text-white-50">Kelola perkembangan siswa</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Jumlah Anak Didik Card -->
  <div class="col-lg-3 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <p class="text-muted small mb-1">Anak Didik Anda</p>
            <h4 class="mb-0 text-primary">{{ number_format($anakCount ?? 0) }}</h4>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-primary rounded">
              <i class="icon-base ri ri-group-line icon-24px"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  @if(isset($dashboardData['stats']))
  <div class="col-12">
    <div class="row g-4">
      @foreach($dashboardData['stats'] as $stat)
      <div class="col-lg-3 col-sm-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">{{ $stat['label'] }}</p>
                <h4 class="mb-0 text-{{ $stat['color'] }}">{{ $stat['value'] }}</h4>
              </div>
              <div class="avatar">
                <div class="avatar-initial bg-{{ $stat['color'] }} rounded">
                  <i class="icon-base ri {{ $stat['icon'] }} icon-24px"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Performance Chart -->
  @if(isset($dashboardData['chartData']))
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Perkembangan Anak Didik Berdasarkan 5 Kategori Penilaian</h5>
        <div class="mt-3">
          <div class="row g-2">
            <div class="col-sm-4">
              <select id="selectAnak" class="form-select">
                <option value="">Pilih Anak Didik</option>
                @foreach($anakList as $ad)
                <option value="{{ $ad->id }}">{{ $ad->nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-4">
              <select id="selectKategori" class="form-select">
                <option value="">Pilih Kategori</option>
                <option>Bina Diri</option>
                <option>Akademik</option>
                <option>Motorik</option>
                <option>Perilaku</option>
                <option>Vokasi</option>
              </select>
            </div>
            <div class="col-sm-4">
              <select id="selectProgram" class="form-select">
                <option value="">Pilih Program</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="guruPerformanceFiltersOnly" style="display:block"></div>
        <div id="guruPerformanceChart" style="display:none"></div>
      </div>
    </div>
  </div>
  @endif




</div>

@if(isset($dashboardData['chartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('guruPerformanceChart');
    if (chartElement) {
      // Initialize interactive filters + chart rendering
      const selectAnak = document.getElementById('selectAnak');
      const selectKategori = document.getElementById('selectKategori');
      const selectProgram = document.getElementById('selectProgram');
      let guruChart = null;

      function resetProgramOptions() {
        selectProgram.innerHTML = '<option value="">Pilih Program</option>';
      }

      selectAnak.addEventListener('change', function() {
        resetProgramOptions();
        const anakId = this.value;
        if (!anakId) return;
        fetch('{{ url('dashboard-guru/programs-for-anak') }}' + '/' + encodeURIComponent(anakId))
          .then(r => r.json())
          .then(json => {
            if (json.success && Array.isArray(json.programs)) {
              json.programs.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nama_program;
                selectProgram.appendChild(opt);
              });
            }
          });
        tryRenderChart();
      });

      [selectKategori, selectProgram].forEach(el => el.addEventListener('change', tryRenderChart));

      function tryRenderChart() {
        const anakId = selectAnak.value;
        const kategori = selectKategori.value;
        const programId = selectProgram.value;
        const chartDiv = document.getElementById('guruPerformanceChart');
        const filtersOnly = document.getElementById('guruPerformanceFiltersOnly');
        if (!anakId || !kategori || !programId) {
          // hide chart
          if (guruChart) {
            try {
              guruChart.destroy();
            } catch (e) {}
            guruChart = null;
          }
          chartDiv.style.display = 'none';
          filtersOnly.style.display = 'block';
          filtersOnly.innerHTML = '';
          return;
        }

        // show loading
        filtersOnly.style.display = 'none';
        chartDiv.style.display = 'block';
        chartDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';

        const url = new URL('{{ route('dashboard-guru.chart-data') }}', window.location.origin);
        url.searchParams.set('anak_id', anakId);
        url.searchParams.set('kategori', kategori);
        url.searchParams.set('program_id', programId);

        fetch(url.toString()).then(r => r.json()).then(json => {
          if (!json.success) {
            chartDiv.innerHTML = '<div class="text-center text-danger">Tidak ada data penilaian.</div>';
            return;
          }
          const labels = json.labels || [];
          const data = json.data || [];
          if (!labels.length) {
            chartDiv.innerHTML = '<div class="text-center text-muted">Tidak ada data penilaian.</div>';
            return;
          }

          const options = {
            series: [{
              name: kategori,
              data: data
            }],
            chart: {
              type: 'bar',
              height: 350
            },
            xaxis: {
              categories: labels
            },
            dataLabels: {
              enabled: false
            },
            colors: ['#28c76f']
          };

          if (guruChart) {
            try {
              guruChart.destroy();
            } catch (e) {}
          }
          guruChart = new ApexCharts(chartElement, options);
          guruChart.render();
        }).catch(() => {
          chartDiv.innerHTML = '<div class="text-center text-danger">Gagal memuat data.</div>';
        });
      }
    }
  });
</script>
@endif
@endsection