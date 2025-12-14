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
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
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
  <div class="col-12">
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

</div>

@if(isset($dashboardData['chartData']))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('konsultanTrendChart');
    if (chartElement) {
      const options = {
        series: @json($dashboardData['chartData']['series']),
        chart: {
          type: 'line',
          height: 350,
          toolbar: {
            show: true
          }
        },
        colors: ['#ff9f43'],
        stroke: {
          curve: 'smooth',
          width: 3
        },
        markers: {
          size: 6,
          strokeWidth: 2,
          hover: {
            size: 8
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
        },
        grid: {
          borderColor: '#f1f1f1',
          row: {
            colors: ['transparent', 'transparent'],
            opacity: 0.5
          }
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