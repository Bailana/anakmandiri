<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Print')</title>
  <style>
    /* A4 paper size and page margins */
    @page {
      size: A4;
      margin: 12mm;

      @top-center {
        content: "LAPORAN HASIL BELAJAR";
        font-size: 12px;
        font-weight: bold;
        color: #000;
      }

      @bottom-right {
        content: "Halaman " counter(page);
        font-size: 10px;
        color: #666;
      }

      @bottom-center {
        content: "Klinik Terapi & Sekolah Khusus Anak Mandiri";
        font-size: 10px;
        color: #666;
      }
    }

    html,
    body {
      width: 210mm;
      height: 297mm;
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      color: #000;
    }

    .print-container {
      width: 100%;
      box-sizing: border-box;
    }

    .kop-surat {
      text-align: center;
      margin-bottom: 8px;
    }

    .kop-surat img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    /* Hide helper UI when printing */
    @media print {
      .d-print-none {
        display: none !important;
      }

      /* keep kop in normal flow so printed output matches preview */
      .kop-surat {
        position: static;
      }

      .content {
        margin-top: 0;
      }
    }
  </style>
  @stack('head')
</head>

<body>
  <div class="print-container">
    <div class="kop-surat">
      <img src="{{ asset('assets/img/kop_surat.png') }}" alt="Kop Surat">
    </div>

    {{-- print-meta section rendered here so it is outside .content and can be fixed/repeated on every page --}}
    @yield('print-meta')

    <div class="content">
      @yield('content')
    </div>
  </div>
  @stack('scripts')
</body>

</html>