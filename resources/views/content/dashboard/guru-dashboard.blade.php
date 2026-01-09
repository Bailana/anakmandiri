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

  <!-- Jumlah Anak Didik Card -->
  <div class="col-lg-3 col-sm-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <p class="text-muted small mb-1">Anak Didik Anda</p>
            <h4 class="mb-0 text-primary">{{ number_format($anakCount ?? 0) }}</h4>
          </div>
          <div class="avatar">
            <div class="avatar-initial bg-primary rounded">
              <i class="icon-base ri ri-group-line icon-24px"></i>
            </div>
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
        <div class="mt-3">
          <div class="row g-2">
            <div class="col-sm-4">
              <select id="selectAnak" class="form-select">
                <option value="">Pilih Anak Didik</option>
                @foreach($anakList as $ad)
                <option value="{{ $ad->id }}">{{ $ad->nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-4">
              <select id="selectKategori" class="form-select">
                <option value="">Pilih Kategori</option>
                <option>Bina Diri</option>
                <option>Akademik</option>
                <option>Motorik</option>
                <option>Basic Learning</option>
                <option>Vokasi</option>
              </select>
            </div>
            <div class="col-sm-4">
              <select id="selectProgram" class="form-select">
                <option value="">Pilih Program</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="guruPerformanceFiltersOnly" style="display:block"></div>
        <div id="guruPerformanceChart" style="display:none"></div>
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
      // Initialize interactive filters + chart rendering
      const selectAnak = document.getElementById('selectAnak');
      const selectKategori = document.getElementById('selectKategori');
      const selectProgram = document.getElementById('selectProgram');
      let guruChart = null;

      function resetProgramOptions() {
        selectProgram.innerHTML = '<option value="">Pilih Program</option>';
      }

      // Load programs for selected anak + kategori (populate selectProgram)
      function loadProgramsForSelection() {
        resetProgramOptions();
        const anakId = selectAnak.value;
        const kategori = selectKategori.value;
        if (!anakId || !kategori) {
          tryRenderChart();
          return;
        }
        // Map displayed kategori labels back to API keys expected by backend
        const kategoriMap = {
          'Bina Diri': 'bina_diri',
          'Akademik': 'akademik',
          'Motorik': 'motorik',
          'Basic Learning': 'perilaku',
          'Perilaku': 'perilaku',
          'Vokasi': 'vokasi'
        };
        const kategoriKey = kategoriMap[kategori] || kategori;
        // Use assessment controller PPI programs endpoint to get programs filtered by kategori key
        const url = '/assessment/ppi-programs?anak_didik_id=' + encodeURIComponent(anakId) + '&kategori=' + encodeURIComponent(kategoriKey);
        fetch(url, {
            credentials: 'same-origin'
          })
          .then(r => r.json())
          .then(json => {
            if (json && json.success && Array.isArray(json.programs) && json.programs.length) {
              json.programs.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nama_program || p.nama || '';
                selectProgram.appendChild(opt);
              });
            } else {
              // show single disabled hint option when no programs available
              const opt = document.createElement('option');
              opt.value = '';
              opt.disabled = true;
              opt.selected = true;
              opt.textContent = 'Tidak ada program pada kategori ini';
              selectProgram.appendChild(opt);
            }
          })
          .catch(() => {
            const opt = document.createElement('option');
            opt.value = '';
            opt.disabled = true;
            opt.selected = true;
            opt.textContent = 'Gagal memuat program';
            selectProgram.appendChild(opt);
          })
          .finally(() => tryRenderChart());
      }

      selectAnak.addEventListener('change', function() {
        loadProgramsForSelection();
      });

      selectKategori.addEventListener('change', function() {
        loadProgramsForSelection();
      });

      // program selection triggers chart render
      selectProgram.addEventListener('change', tryRenderChart);

      function tryRenderChart() {
        const anakId = selectAnak.value;
        const kategori = selectKategori.value;
        const programId = selectProgram.value;
        const chartDiv = document.getElementById('guruPerformanceChart');
        const filtersOnly = document.getElementById('guruPerformanceFiltersOnly');
        if (!anakId || !kategori || !programId) {
          // hide chart
          if (guruChart) {
            try {
              guruChart.destroy();
            } catch (e) {}
            guruChart = null;
          }
          chartDiv.style.display = 'none';
          filtersOnly.style.display = 'block';
          filtersOnly.innerHTML = '';
          return;
        }

        // show loading
        filtersOnly.style.display = 'none';
        chartDiv.style.display = 'block';
        chartDiv.innerHTML = '<div class="text-center text-muted">Memuat data...</div>';

        // correct route helper: dashboard-guru.chart-data
        const url = new URL('{{ route('dashboard-guru.chart-data') }}');
        url.searchParams.set('anak_id', anakId);
        // map displayed kategori label to backend key
        const kategoriMapForChart = {
          'Bina Diri': 'bina_diri',
          'Akademik': 'akademik',
          'Motorik': 'motorik',
          'Basic Learning': 'perilaku',
          'Perilaku': 'perilaku',
          'Vokasi': 'vokasi'
        };
        const kategoriKeyForChart = kategoriMapForChart[kategori] || kategori;
        url.searchParams.set('kategori', kategoriKeyForChart);
        url.searchParams.set('program_id', programId);

        fetch(url.toString()).then(r => r.json()).then(json => {
          if (!json.success) {
            chartDiv.innerHTML = '<div class="text-center text-danger">Tidak ada data penilaian.</div>';
            return;
          }
          let labels = json.labels || [];
          let data = Array.isArray(json.data) ? json.data.slice() : [];
          if (!labels.length) {
            chartDiv.innerHTML = '<div class="text-center text-muted">Tidak ada data penilaian.</div>';
            return;
          }

          // If backend returned ISO date keys (YYYY-MM-DD), expand to a full daily range
          // and fill missing days with zero so days with 0 scores are still displayed.
          try {
            const isoRe = /^\d{4}-\d{2}-\d{2}$/;
            const allIso = labels.every(l => isoRe.test(String(l)));
            if (allIso && labels.length) {
              // build map from ISO date -> value
              const valMap = {};
              labels.forEach((k, i) => {
                valMap[String(k)] = data[i];
              });

              // compute min/max dates from the provided labels
              const parsed = labels.map(l => new Date(l));
              const minTs = Math.min.apply(null, parsed.map(d => d.getTime()));
              const maxTs = Math.max.apply(null, parsed.map(d => d.getTime()));
              const minDate = new Date(minTs);
              const maxDate = new Date(maxTs);

              const outLabels = [];
              const outData = [];
              for (let dt = new Date(minDate); dt <= maxDate; dt.setDate(dt.getDate() + 1)) {
                const key = dt.toISOString().slice(0, 10);
                outLabels.push(new Date(key).toLocaleDateString('id-ID', {
                  day: '2-digit',
                  month: 'short'
                }));
                const v = Object.prototype.hasOwnProperty.call(valMap, key) ? valMap[key] : 0;
                outData.push((v === null || typeof v === 'undefined' || v === '') ? 0 : Number(v));
              }
              labels = outLabels;
              data = outData;
            }
          } catch (e) {
            console.debug('Date expansion skipped', e);
          }

          const options = {
            series: [{
              name: kategori,
              data: data
            }],
            chart: {
              type: 'line',
              height: 320,
              parentHeightOffset: 0,
              toolbar: {
                show: true
              },
              zoom: {
                enabled: true
              }
            },
            stroke: {
              curve: 'smooth',
              width: 3
            },
            markers: {
              size: 5
            },
            dataLabels: {
              enabled: true
            },
            legend: {
              show: false
            },
            grid: {
              borderColor: '#f1f5f9'
            },
            xaxis: {
              categories: labels,
              labels: {
                rotate: -45
              }
            },
            yaxis: {
              min: 0,
              max: 4,
              tickAmount: 4
            },
            colors: ['#10b981'],
            tooltip: {
              y: {
                formatter: function(val) {
                  return val;
                }
              }
            }
          };

          if (guruChart) {
            try {
              guruChart.destroy();
            } catch (e) {}
            guruChart = null;
          }
          // clear any loading placeholder added earlier
          try {
            chartElement.innerHTML = '';
          } catch (e) {}
          guruChart = new ApexCharts(chartElement, options);
          guruChart.render();
        }).catch(() => {
          chartDiv.innerHTML = '<div class="text-center text-danger">Gagal memuat data.</div>';
        });
      }
    }
  });
</script>
@endif
@endsection