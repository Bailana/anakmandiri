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
      </div>
      <div class="card-body">
        <div id="guruPerformanceChart"></div>
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
            text: 'Nilai Perkembangan'
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