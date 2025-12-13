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
            <p class="mb-2 text-white-50">Dashboard Guru Pembelajaran</p>
            <p class="mb-0 text-white-50">Kelola kelas dan pantau perkembangan siswa</p>
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
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">{{ $dashboardData['chartData']['title'] }}</h5>
      </div>
      <div class="card-body">
        <div id="guruPerformanceChart"></div>
      </div>
    </div>
  </div>
  @endif

  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Aksi Cepat</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-2">
          <a href="#" class="btn btn-sm btn-success d-flex align-items-center justify-content-between">
            <span>Buat Kelas Baru</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-primary d-flex align-items-center justify-content-between">
            <span>Lihat Siswa</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-info d-flex align-items-center justify-content-between">
            <span>Berikan Tugas</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-warning d-flex align-items-center justify-content-between">
            <span>Nilai Siswa</span>
            <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Kelas Hari Ini -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">📅 Kelas Hari Ini</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Matematika - 08:00</h6>
                <p class="text-muted small mb-0">Ruang 101 | 25 siswa</p>
              </div>
              <span class="badge bg-success">Akan Dimulai</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Bahasa Inggris - 10:30</h6>
                <p class="text-muted small mb-0">Ruang 205 | 22 siswa</p>
              </div>
              <span class="badge bg-primary">Akan Dimulai</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">IPA - 13:00</h6>
                <p class="text-muted small mb-0">Laboratorium | 20 siswa</p>
              </div>
              <span class="badge bg-info">Akan Dimulai</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tugas Pending -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">✋ Tugas Menunggu Penilaian</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Essay Matematika</h6>
                <p class="text-muted small mb-0">3 siswa | Batas: 2 jam lagi</p>
              </div>
              <span class="badge bg-warning">Pending</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Presentasi Bahasa Inggris</h6>
                <p class="text-muted small mb-0">5 siswa | Batas: 1 hari lagi</p>
              </div>
              <span class="badge bg-warning">Pending</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Proyek Grup IPA</h6>
                <p class="text-muted small mb-0">2 grup | Batas: 3 hari lagi</p>
              </div>
              <span class="badge bg-warning">Pending</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Siswa Terbaik -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">⭐ Siswa dengan Performa Terbaik</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>Kelas</th>
              <th>Rata-rata Nilai</th>
              <th>Kehadiran</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Ahmad Rizki</td>
              <td>X-1</td>
              <td><strong>95</strong></td>
              <td>100%</td>
              <td><span class="badge bg-success">Sangat Baik</span></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Siti Fatimah</td>
              <td>X-1</td>
              <td><strong>93</strong></td>
              <td>98%</td>
              <td><span class="badge bg-success">Sangat Baik</span></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Budi Santoso</td>
              <td>X-2</td>
              <td><strong>91</strong></td>
              <td>96%</td>
              <td><span class="badge bg-success">Sangat Baik</span></td>
            </tr>
            <tr>
              <td>4</td>
              <td>Dewi Kusuma</td>
              <td>X-2</td>
              <td><strong>88</strong></td>
              <td>94%</td>
              <td><span class="badge bg-info">Baik</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@if(isset($dashboardData['chartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('guruPerformanceChart');
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
        colors: ['#28c76f'],
        stroke: {
          curve: 'smooth',
          width: 3
        },
        xaxis: {
          categories: @json($dashboardData['chartData']['categories']),
        },
        yaxis: {
          title: {
            text: 'Jumlah Siswa'
          }
        },
        dataLabels: {
          enabled: false
        },
        fill: {
          type: 'gradient',
          gradient: {
            opacityFrom: 0.6,
            opacityTo: 0.1
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
        }
      };
      const chart = new ApexCharts(chartElement, options);
      chart.render();
    }
  });
</script>
@endif
@endsection