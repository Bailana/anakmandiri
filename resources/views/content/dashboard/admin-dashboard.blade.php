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
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
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

  <!-- Sesi Hari Ini (left) and Jam Terapi per Terapis (right) -->
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
            <div id="terapisHoursChart" style="min-height:320px"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

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