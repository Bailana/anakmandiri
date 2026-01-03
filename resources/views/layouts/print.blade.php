<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Print')</title>
  <style>
    /* A4 paper size */
    @page {
      size: A4;
      margin: 20mm;
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
    }

    /* Hide helper UI when printing PDF via browser print */
    @media print {
      .d-print-none {
        display: none !important;
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

    <div class="content">
      @yield('content')
    </div>
  </div>
  @stack('scripts')
</body>

</html>