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
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="card-title text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 💡</h5>
            <p class="mb-2 text-white-50">Dashboard Konsultan Profesional</p>
            <p class="mb-0 text-white-50">Kelola konsultasi dan respon klien dengan efisien</p>
          </div>
          <img src="{{ asset('assets/img/illustrations/man-with-tablet-light.png') }}" class="img-fluid" width="120" alt="konsultan" />
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

  <!-- Consultation Trend Chart -->
  @if(isset($dashboardData['chartData']))
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">{{ $dashboardData['chartData']['title'] }}</h5>
      </div>
      <div class="card-body">
        <div id="konsultanTrendChart"></div>
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
          <a href="#" class="btn btn-sm btn-warning d-flex align-items-center justify-content-between">
            <span>Konsultasi Baru</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-primary d-flex align-items-center justify-content-between">
            <span>Lihat Klien</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-success d-flex align-items-center justify-content-between">
            <span>Jadwal Konsultasi</span>
            <i class="ri-arrow-right-line"></i>
          </a>
          <a href="#" class="btn btn-sm btn-info d-flex align-items-center justify-content-between">
            <span>Laporan Klien</span>
            <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Konsultasi Aktif -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">📞 Konsultasi Aktif</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <h6 class="mb-1">Konsultasi Karir</h6>
                <p class="text-muted small mb-1">Klien: Rina Putri</p>
                <p class="text-muted small mb-0">Durasi: 45 menit</p>
              </div>
              <span class="badge bg-success">Aktif</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <h6 class="mb-1">Konsultasi Bisnis</h6>
                <p class="text-muted small mb-1">Klien: Hendra Wijaya</p>
                <p class="text-muted small mb-0">Durasi: 60 menit</p>
              </div>
              <span class="badge bg-success">Aktif</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <h6 class="mb-1">Konsultasi Pendidikan</h6>
                <p class="text-muted small mb-1">Klien: Siti Maryam</p>
                <p class="text-muted small mb-0">Durasi: 30 menit</p>
              </div>
              <span class="badge bg-info">Scheduled</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Respon Pending -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0">⏱️ Respon yang Menunggu</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Pertanyaan dari Ahmad</h6>
                <p class="text-muted small mb-0">1 hari lalu | Karir</p>
              </div>
              <span class="badge bg-danger">Urgent</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Follow-up dari Dewi</h6>
                <p class="text-muted small mb-0">2 hari lalu | Bisnis</p>
              </div>
              <span class="badge bg-warning">High</span>
            </div>
          </div>
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1">Inquiry dari Budi</h6>
                <p class="text-muted small mb-0">3 hari lalu | Pendidikan</p>
              </div>
              <span class="badge bg-warning">Medium</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Klien Terbaik -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title m-0">⭐ Klien dengan Kepuasan Tertinggi</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama Klien</th>
              <th>Jenis Konsultasi</th>
              <th>Jumlah Sesi</th>
              <th>Rating</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Rina Putri</td>
              <td>Karir & Pengembangan</td>
              <td>5</td>
              <td><strong>5.0/5 ⭐</strong></td>
              <td><span class="badge bg-success">Aktif</span></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Hendra Wijaya</td>
              <td>Strategi Bisnis</td>
              <td>4</td>
              <td><strong>4.9/5 ⭐</strong></td>
              <td><span class="badge bg-success">Aktif</span></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Siti Maryam</td>
              <td>Konsultasi Pendidikan</td>
              <td>3</td>
              <td><strong>4.8/5 ⭐</strong></td>
              <td><span class="badge bg-info">Selesai</span></td>
            </tr>
            <tr>
              <td>4</td>
              <td>Ahmad Rizki</td>
              <td>Pengembangan Diri</td>
              <td>6</td>
              <td><strong>4.7/5 ⭐</strong></td>
              <td><span class="badge bg-success">Aktif</span></td>
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
    const chartElement = document.getElementById('konsultanTrendChart');
    if (chartElement) {
      const options = {
        series: [{
          name: 'Jumlah Konsultasi',
          data: @json($dashboardData['chartData']['series'])
        }],
        chart: {
          type: 'line',
          height: 350,
          stacked: false,
        },
        colors: ['#ffa500'],
        stroke: {
          curve: 'smooth',
          width: 3
        },
        markers: {
          size: 5,
          strokeWidth: 0,
          hover: {
            size: 7
          }
        },
        xaxis: {
          categories: @json($dashboardData['chartData']['categories']),
        },
        yaxis: {
          title: {
            text: 'Jumlah Konsultasi'
          }
        },
        dataLabels: {
          enabled: false
        }
      };
      const chart = new ApexCharts(chartElement, options);
      chart.render();
    }
  });
</script>
@endif
@endsection