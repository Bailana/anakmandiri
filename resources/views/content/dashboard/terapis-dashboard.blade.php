@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard - Terapis')

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
    <div class="card bg-info text-white">
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
            <h5 class="card-title text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 💗</h5>
            <p class="mb-2 text-white-50">Dashboard Terapis Profesional</p>
            <p class="mb-0 text-white-50">Pantau kesejahteraan dan perkembangan pasien Anda</p>
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

  <!-- Therapy type cards (top 3) styled like admin stats -->
  @if(isset($dashboardData['therapyCounts']))
  <div class="col-12 mt-3">
    <div class="row g-4">
      @foreach($dashboardData['therapyCounts'] as $tc)
      @php
      // cycle colors for visual variety
      $colors = ['info','success','warning','primary','danger'];
      $color = $colors[array_rand($colors)];
      @endphp
      <div class="col-12 col-sm-6 col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">{{ $tc['label'] }}</p>
                <h4 class="mb-0 text-{{ $color }}">{{ $tc['count'] }}</h4>
                <p class="small text-muted mb-0">Anak mengikuti terapi</p>
              </div>
              <div class="avatar">
                <div class="avatar-initial bg-{{ $color }} rounded">
                  <i class="icon-base ri ri-heart-pulse-line icon-24px"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach

      @php $missing = 3 - count($dashboardData['therapyCounts']); @endphp
      @for($i = 0; $i < max(0, $missing); $i++)
        <div class="col-12 col-sm-6 col-md-4 mb-3">
        <div class="card h-100">
          <div class="card-body text-center text-muted">
            <p class="mb-0">Tidak ada data</p>
          </div>
        </div>
    </div>
    @endfor
  </div>
</div>
@endif

<!-- Therapy Session Trend -->
@if(isset($dashboardData['chartData']))
<div class="col-lg-8">
  <div class="card h-100">
    <div class="card-header">
      <h5 class="card-title m-0">{{ $dashboardData['chartData']['title'] }}</h5>
    </div>
    <div class="card-body">
      <div id="terapisTrendChart"></div>
    </div>
  </div>
</div>
@endif

<!-- Quick Actions removed -->

<!-- Sesi Hari Ini (left) and Daftar Pasien Aktif (right) -->
<div class="col-12">
  <div class="row">
    <div class="col-lg-4 mb-3 mb-lg-0">
      <div class="card h-100 sesi-card">
        <div class="card-header">
          <h5 class="card-title m-0">⏰ Sesi Hari Ini</h5>
        </div>
        <div class="card-body">
          @php $jadwalList = $dashboardData['jadwal_hari_ini'] ?? []; @endphp
          <style>
            .sesi-card .card-body {
              display: flex;
              flex-direction: column;
            }

            .sesi-list-wrapper.scrollable {
              max-height: 360px;
              overflow-y: auto;
              -webkit-overflow-scrolling: touch;
            }
          </style>
          <div class="list-group list-group-flush sesi-list-wrapper {{ count($jadwalList) >= 4 ? 'scrollable' : '' }}">
            @forelse($jadwalList as $jadwal)
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1">Pasien: {{ $jadwal->assignment->anakDidik->nama ?? '-' }}</h6>
                  <p class="text-muted small mb-1">Jam: {{ $jadwal->jam_mulai ?? '-' }}</p>
                  <p class="text-muted small mb-0">Tipe: {{ $jadwal->jenis_terapi ?? '-' }}</p>
                </div>
                @php
                try {
                $now = \Carbon\Carbon::now();
                $start = \Carbon\Carbon::parse($jadwal->jam_mulai);
                $end = (clone $start)->addHour();
                if ($now->between($start, $end)) {
                $badgeClass = 'bg-primary';
                $badgeText = 'Berlangsung';
                } elseif ($now->lt($start)) {
                $badgeClass = 'bg-warning';
                $badgeText = 'Akan Dimulai';
                } else {
                $badgeClass = 'bg-success';
                $badgeText = 'Selesai';
                }
                } catch (\Exception $e) {
                $badgeClass = 'bg-secondary';
                $badgeText = '—';
                }
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
              </div>
            </div>
            @empty
            <div class="list-group-item text-center text-muted">Tidak ada sesi terapi hari ini.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0">📊 Jam Terapi per Terapis</h5>
          <p class="small text-muted mb-0">Jumlah jam (1 jam per anak yang terdaftar)</p>
        </div>
        <div class="card-body">
          <div id="terapisHoursChart" style="min-height:320px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

@if(isset($dashboardData['chartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('terapisTrendChart');
    if (chartElement) {
      const options = {
        series: @json($dashboardData['chartData']['series']),
        chart: {
          type: 'area',
          height: 350,
          toolbar: {
            show: true
          }
        },
        colors: ['#00cfe8'],
        stroke: {
          curve: 'smooth',
          width: 3
        },
        xaxis: {
          categories: @json($dashboardData['chartData']['categories']),
        },
        yaxis: {
          title: {
            text: 'Jumlah Sesi'
          }
        },
        dataLabels: {
          enabled: false
        },
        fill: {
          type: 'gradient',
          gradient: {
            opacityFrom: 0.7,
            opacityTo: 0.2
          }
        },
        markers: {
          size: 5,
          strokeWidth: 2,
          hover: {
            size: 7
          }
        },
        grid: {
          borderColor: '#f1f1f1'
        },
        tooltip: {
          shared: true,
          intersect: false
        }
      };
      const chart = new ApexCharts(chartElement, options);
      chart.render();
    }
  });
</script>
@endif
@if(isset($dashboardData['terapisHoursChart']) && count($dashboardData['terapisHoursChart']['labels'])>0)
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('terapisHoursChart');
    if (!el) return;
    const lineOptions = {
      series: [{
        name: 'Jam Terapi',
        data: @json($dashboardData['terapisHoursChart']['series'])
      }],
      chart: {
        type: 'line',
        height: 350,
        toolbar: {
          show: true
        },
        zoom: {
          enabled: true
        }
      },
      legend: {
        show: false
      },
      colors: ['#7367f0'],
      stroke: {
        curve: 'smooth',
        width: 3
      },
      markers: {
        size: 5,
        colors: ['#7367f0'],
        strokeColors: '#fff',
        strokeWidth: 2,
        hover: {
          size: 7
        }
      },
      dataLabels: {
        enabled: true,
        style: {
          fontSize: '12px',
          colors: ["#304758"]
        }
      },
      xaxis: {
        categories: @json($dashboardData['terapisHoursChart']['labels']),
        labels: {
          style: {
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        title: {
          text: 'Jam'
        }
      },
      grid: {
        borderColor: '#f1f1f1'
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return val + ' jam';
          }
        }
      }
    };
    const chart = new ApexCharts(el, lineOptions);
    chart.render();
  });
</script>
@endif
@endsection