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

  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">Aksi Cepat</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-2">
          <a href="#" class="btn btn-sm btn-info d-flex align-items-center justify-content-between">
            <span>Buat Catatan Pasien</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-primary d-flex align-items-center justify-content-between">
            <span>Daftar Pasien</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-success d-flex align-items-center justify-content-between">
            <span>Jadwal Sesi</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-warning d-flex align-items-center justify-content-between">
            <span>Laporan Terapi</span>
            <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Sesi Hari Ini -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">⏰ Sesi Hari Ini</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">Pasien: Ibu Ani</h6>
                <p class="text-muted small mb-1">Jam: 10:00 - 11:00</p>
                <p class="text-muted small mb-0">Tipe: Konseling Keluarga</p>
              </div>
              <span class="badge bg-success">Akan Dimulai</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">Pasien: Pak Budi</h6>
                <p class="text-muted small mb-1">Jam: 14:00 - 15:00</p>
                <p class="text-muted small mb-0">Tipe: Terapi Individu</p>
              </div>
              <span class="badge bg-primary">Akan Dimulai</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pasien dengan Progres Positif -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">📈 Pasien dengan Progres Positif</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Siti Nurhaliza</h6>
                <div class="progress mt-2" style="height: 6px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-muted small mb-0 mt-1">Perbaikan: 85%</p>
              </div>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Ahmad Wijaya</h6>
                <div class="progress mt-2" style="height: 6px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-muted small mb-0 mt-1">Perbaikan: 78%</p>
              </div>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Dewi Kusuma</h6>
                <div class="progress mt-2" style="height: 6px;">
                  <div class="progress-bar bg-info" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-muted small mb-0 mt-1">Perbaikan: 65%</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Daftar Pasien Aktif -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">👥 Daftar Pasien Aktif</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Pasien</th>
              <th>Jenis Terapi</th>
              <th>Total Sesi</th>
              <th>Progres</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Ibu Ani Wijaya</td>
              <td>Konseling Keluarga</td>
              <td>8</td>
              <td>
                <div class="progress" style="height: 6px; width: 100px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </td>
              <td><span class="badge bg-success">Aktif</span></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Pak Budi Santoso</td>
              <td>Terapi Stress</td>
              <td>6</td>
              <td>
                <div class="progress" style="height: 6px; width: 100px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </td>
              <td><span class="badge bg-success">Aktif</span></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Siti Nurhaliza</td>
              <td>Terapi Depresi</td>
              <td>10</td>
              <td>
                <div class="progress" style="height: 6px; width: 100px;">
                  <div class="progress-bar bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </td>
              <td><span class="badge bg-info">Monitoring</span></td>
            </tr>
            <tr>
              <td>4</td>
              <td>Ahmad Rizki</td>
              <td>Terapi Kecemasan</td>
              <td>7</td>
              <td>
                <div class="progress" style="height: 6px; width: 100px;">
                  <div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </td>
              <td><span class="badge bg-warning">Perlu Perhatian</span></td>
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
@endsection