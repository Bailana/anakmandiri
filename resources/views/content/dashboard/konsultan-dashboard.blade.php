@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard - Konsultan')

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
    <div class="card bg-warning text-white">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar avatar-xl">
            @if(Auth::user()->avatar)
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
              class="rounded-circle" />
            @else
            <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="rounded-circle" />
            @endif
          </div>
          <div>
            <h5 class="card-title text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 💡</h5>
            <p class="mb-2 text-white-50">Dashboard Konsultan Profesional</p>
            <p class="mb-0 text-white-50">Kelola konsultasi dan respon klien dengan efisien</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  @if(isset($dashboardData['stats']))
  <div class="col-12">
    <div class="row g-2">
      @foreach($dashboardData['stats'] as $stat)
      <div class="col-md-6">
        <div class="card">
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
  <!-- Pie Chart & Trend Chart in One Row -->
  @if(isset($dashboardData['pieChartData']) || isset($dashboardData['chartData']))
  <div class="col-12">
    <div class="row g-2">
      @if(isset($dashboardData['pieChartData']))
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title m-0">Perbandingan Observasi Anak Didik (6 Bulan Terakhir)</h5>
          </div>
          <div class="card-body">
            @if(!empty($dashboardData['pieChartData']['series']) && array_sum($dashboardData['pieChartData']['series']) > 0)
            <div id="pieChartObservasi" style="width:100%; min-height:320px;"></div>
            @else
            <div class="text-center text-muted py-5">Belum ada data observasi/evaluasi</div>
            @endif
          </div>
        </div>
      </div>
      @endif
      @if(isset($dashboardData['chartData']))
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title m-0">{{ $dashboardData['chartData']['title'] }}</h5>
          </div>
          <div class="card-body">
            @php
            $chartCategories = $dashboardData['chartData']['categories'];
            $chartSeries = $dashboardData['chartData']['series'];
            if (empty($chartCategories) || empty($chartSeries[0]['data'])) {
            $chartCategories = ['-'];
            $chartSeries = [[ 'name' => 'Jumlah Observasi', 'data' => [0] ]];
            }
            @endphp
            <div id="konsultanTrendChart" style="width:100%; min-height:320px;"></div>
          </div>
        </div>
      </div>
      @endif
      @if(isset($dashboardData['pieChartData']) && !empty($dashboardData['pieChartData']['series']) && array_sum($dashboardData['pieChartData']['series']) > 0)
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const pieChartEl = document.getElementById('pieChartObservasi');
          if (pieChartEl) {
            const options = {
              chart: {
                type: 'pie',
                height: 320
              },
              labels: @json($dashboardData['pieChartData']['labels']),
              series: @json($dashboardData['pieChartData']['series']),
              colors: ['#28c76f', '#ff9f43'],
              legend: {
                position: 'bottom'
              },
              responsive: [{
                breakpoint: 480,
                options: {
                  chart: {
                    width: 200
                  },
                  legend: {
                    position: 'bottom'
                  }
                }
              }]
            };
            const chart = new ApexCharts(pieChartEl, options);
            chart.render();
          }
        });
      </script>
      @endif
      @if(isset($dashboardData['chartData']))
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const lineChartElement = document.getElementById('konsultanTrendChart');
          if (lineChartElement) {
            const lineOptions = {
              series: @json($chartSeries),
              chart: {
                type: 'line',
                height: 350,
                toolbar: {
                  show: true,
                  tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                  },
                  autoSelected: 'zoom'
                },
                zoom: {
                  enabled: true
                }
              },
              legend: {
                show: false
              },
              colors: ['#28c76f'],
              stroke: {
                curve: 'smooth',
                width: 3
              },
              markers: {
                size: 5,
                colors: ['#28c76f'],
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
                categories: @json($chartCategories),
                labels: {
                  style: {
                    fontSize: '12px'
                  }
                }
              },
              yaxis: {
                title: {
                  text: 'Jumlah Anak Didik'
                }
              },
              grid: {
                borderColor: '#f1f1f1'
              },
              tooltip: {
                y: {
                  formatter: function(val) {
                    return val + " anak didik"
                  }
                }
              }
            };
            const lineChart = new ApexCharts(lineChartElement, lineOptions);
            lineChart.render();
          }
        });
      </script>
      @endif
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="card-title m-0">Riwayat Aktivitas Konsultan</h5>
            <p class="small text-muted mb-0">Menampilkan aktivitas observasi/evaluasi terbaru</p>
          </div>
          <div>
            <span class="badge bg-primary"><i class="ri-calendar-line me-1"></i>Terbaru</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Waktu</th>
                <th>Anak Didik</th>
                <th>Kategori</th>
                <th>Hasil</th>
              </tr>
            </thead>
            <tbody>
              @if(count($dashboardData['riwayatAktivitas']) > 0)
              @foreach($dashboardData['riwayatAktivitas'] as $aktivitas)
              <tr>
                <td>
                  <small class="text-muted">
                    {{ $aktivitas->tanggal_assessment ? $aktivitas->tanggal_assessment->diffForHumans() : '' }}
                  </small>
                </td>
                <td>{{ $aktivitas->anakDidik->nama ?? 'Anak Didik' }}</td>
                <td>{{ $aktivitas->kategori ?? 'Observasi/Evaluasi' }}</td>
                <td><span class="badge bg-primary">{{ $aktivitas->hasil_penilaian ?? '-' }}</span></td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas observasi/evaluasi.</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

  </div>

  @endsection