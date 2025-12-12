@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard - Admin')

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
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h5>
            <p class="mb-2 text-white-50">Anda login sebagai Administrator</p>
            <p class="mb-0 text-white-50">Kelola seluruh sistem dengan efektif</p>
          </div>
          <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" class="img-fluid" width="120" alt="admin" />
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

  <!-- Users Distribution Chart -->
  @if(isset($dashboardData['chartData']))
  <div class="col-lg-8">
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

  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Aksi Cepat</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-2">
          <a href="#" class="btn btn-sm btn-primary d-flex align-items-center justify-content-between">
            <span>Manajemen Pengguna</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-success d-flex align-items-center justify-content-between">
            <span>Kelola Role & Izin</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-warning d-flex align-items-center justify-content-between">
            <span>Laporan Sistem</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-info d-flex align-items-center justify-content-between">
            <span>Pengaturan Sistem</span>
            <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Aktivitas Terbaru</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Waktu</th>
              <th>Pengguna</th>
              <th>Aktivitas</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <small class="text-muted">10 menit lalu</small>
              </td>
              <td>
                <span class="badge bg-primary">Guru</span> Ahmad Wijaya
              </td>
              <td>Login ke sistem</td>
              <td><span class="badge bg-success">Sukses</span></td>
            </tr>
            <tr>
              <td>
                <small class="text-muted">25 menit lalu</small>
              </td>
              <td>
                <span class="badge bg-warning">Konsultan</span> Siti Nurhaliza
              </td>
              <td>Membuat konsultasi baru</td>
              <td><span class="badge bg-success">Sukses</span></td>
            </tr>
            <tr>
              <td>
                <small class="text-muted">1 jam lalu</small>
              </td>
              <td>
                <span class="badge bg-info">Terapis</span> Dr. Bambang
              </td>
              <td>Update profil</td>
              <td><span class="badge bg-success">Sukses</span></td>
            </tr>
            <tr>
              <td>
                <small class="text-muted">2 jam lalu</small>
              </td>
              <td>
                <span class="badge bg-primary">Admin</span> System Admin
              </td>
              <td>Backup database</td>
              <td><span class="badge bg-success">Sukses</span></td>
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
    const chartElement = document.getElementById('adminRoleChart');
    if (chartElement) {
      const options = {
        series: @json($dashboardData['chartData']['series']),
        chart: {
          type: 'bar',
          height: 350,
          stacked: false,
        },
        colors: ['#4680ff', '#2ed8b6', '#ffa500', '#00d4ff'],
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 4,
          }
        },
        xaxis: {
          categories: @json($dashboardData['chartData']['categories']),
        },
        yaxis: {
          title: {
            text: 'Jumlah Pengguna'
          }
        },
        dataLabels: {
          enabled: true,
        }
      };
      const chart = new ApexCharts(chartElement, options);
      chart.render();
    }
  });
</script>
@endif
@endsection