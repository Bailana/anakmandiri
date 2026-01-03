@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard | Admin')

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
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar avatar-xl">
            @if(Auth::user()->avatar)
            <img src="{{ asset('assets/img/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
            @else
            <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="rounded-circle" />
            @endif
          </div>
          <div>
            <h5 class="card-title text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h5>
            <p class="mb-2 text-white-50">Anda login sebagai Administrator</p>
            <p class="mb-0 text-white-50">Kelola seluruh sistem dengan efektif</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  @if(isset($dashboardData['stats']))
  <div class="col-12">
    <div class="row g-4">
      @php
      $karyawanTetap = \App\Models\Karyawan::where('status_kepegawaian', 'Tetap')->count();
      @endphp
      @foreach($dashboardData['stats'] as $stat)
      @php $lowerLabel = strtolower($stat['label'] ?? ''); @endphp
      @if($lowerLabel === 'terapis')
      @continue
      @endif
      @php
      $displayLabel = $stat['label'] ?? '';
      $displayValue = $stat['value'] ?? 0;
      if ($lowerLabel === 'guru') {
      $displayLabel = 'Karyawan';
      $displayValue = $karyawanTetap;
      }
      @endphp
      <div class="col-6 col-sm-6 col-md-3 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">{{ $displayLabel }}</p>
                <h4 class="mb-0 text-{{ $stat['color'] }}">{{ $displayValue }}</h4>
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

  @if(isset($dashboardData['chartData']))
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Distribusi Pengguna Berdasarkan Role</h5>
        <p class="small text-muted mb-0">Total {{ $dashboardData['totalUsers'] }} pengguna</p>
      </div>
      <div class="card-body">
        <div id="adminRoleChart"></div>
      </div>
    </div>
  </div>
  @endif

  <!-- Anak Didik Line Chart -->
  @if(isset($dashboardData['lineChartData']))
  <div class="col-lg-12">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">{{ $dashboardData['lineChartData']['title'] }}</h5>
        <p class="small text-muted mb-0">Grafik pendaftaran anak didik</p>
      </div>
      <div class="card-body">
        <div id="adminAnakDidikChart"></div>
      </div>
    </div>
  </div>
  @endif

  <!-- Recent Activity -->
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h5 class="card-title m-0">Aktivitas Terbaru</h5>
          <p class="small text-muted mb-0">Menampilkan aktivitas hari ini</p>
        </div>
        <div>
          <span class="badge bg-primary"><i class="ri-calendar-line me-1"></i>Hari ini</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Waktu</th>
              <th>Role</th>
              <th>Pengguna</th>
              <th>Aktivitas</th>
              <th>IP</th>
            </tr>
          </thead>
          <tbody>
            @if(isset($dashboardData['activities']) && count($dashboardData['activities']) > 0)
            @foreach($dashboardData['activities'] as $activity)
            <tr>
              <td>
                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
              </td>
              @php
              $roleColors = [
              'admin' => 'primary',
              'guru' => 'primary',
              'konsultan' => 'warning',
              'terapis' => 'info',
              'karyawan' => 'secondary',
              ];
              $roleName = $activity->user ? ucfirst($activity->user->role) : '-';
              $roleBgColor = $activity->user ? ($roleColors[$activity->user->role] ?? 'secondary') : 'secondary';
              @endphp
              <td><span class="badge bg-{{ $roleBgColor }}">{{ $roleName }}</span></td>
              <td>{{ $activity->user ? $activity->user->name : '-' }}</td>
              <td>{{ $activity->description }}</td>
              <td>{{ $activity->ip_address }}</td>
            </tr>
            @endforeach
            @else
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                Belum ada aktivitas
              </td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center pagination-footer-fix">
        <style>
          /* Pastikan pagination dan info tetap satu baris di mobile */
          .pagination-footer-fix {
            flex-wrap: nowrap !important;
            gap: 0.5rem;
          }

          .pagination-footer-fix>div,
          .pagination-footer-fix>nav {
            min-width: 0;
            max-width: 100%;
          }

          .pagination-footer-fix nav {
            flex-shrink: 1;
            flex-grow: 0;
          }

          @media (max-width: 767.98px) {
            .pagination-footer-fix {
              flex-direction: row !important;
              align-items: center !important;
              flex-wrap: nowrap !important;
            }

            .pagination-footer-fix>div,
            .pagination-footer-fix>nav {
              width: auto !important;
              max-width: 100%;
            }

            .pagination-footer-fix nav ul.pagination {
              flex-wrap: nowrap !important;
            }
          }
        </style>
        <div class="text-body-secondary">
          Menampilkan {{ $dashboardData['activities']->firstItem() ?? 0 }} hingga {{ $dashboardData['activities']->lastItem() ?? 0 }} dari {{ $dashboardData['activities']->total() }} data
        </div>
        <nav>
          {{ $dashboardData['activities']->links('pagination::bootstrap-4') }}
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- @if(isset($dashboardData['chartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('adminRoleChart');
    if (chartElement) {
      const options = {
        series: @json($dashboardData['chartData']['series']),
        chart: {
          type: 'bar',
          height: 350,
          toolbar: {
            show: true
          }
        },
        colors: ['#7367f0'],
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 4,
            dataLabels: {
              position: 'top'
            }
          }
        },
        dataLabels: {
          enabled: true,
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#304758"]
          }
        },
        xaxis: {
          categories: @json($dashboardData['chartData']['categories']),
          labels: {
            style: {
              fontSize: '12px'
            }
          }
        },
        yaxis: {
          title: {
            text: 'Jumlah Pengguna'
          }
        },
        grid: {
          borderColor: '#f1f1f1'
        }
      };
      const chart = new ApexCharts(chartElement, options);
      chart.render();
    }
  });
</script>
@endif -->

@if(isset($dashboardData['lineChartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const lineChartElement = document.getElementById('adminAnakDidikChart');
    if (lineChartElement) {
      const lineOptions = {
        series: @json($dashboardData['lineChartData']['series']),
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
          categories: @json($dashboardData['lineChartData']['categories']),
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
@endsection