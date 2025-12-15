<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Detail Observasi/Evaluasi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      color: #222;
      margin: 0;
      padding: 24px;
    }

    .header {
      text-align: center;
      margin-bottom: 24px;
    }

    .header h2 {
      margin: 0;
      color: #1e3a8a;
    }

    .section {
      margin-bottom: 18px;
    }

    .label {
      font-weight: bold;
      color: #1e3a8a;
    }

    .value {
      margin-bottom: 8px;
    }

    .box {
      background: #f5f5f5;
      border-radius: 6px;
      padding: 12px 16px;
      margin-bottom: 12px;
    }

    .footer {
      text-align: center;
      color: #888;
      font-size: 12px;
      margin-top: 32px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h2>Detail Observasi/Evaluasi</h2>
    <div style="font-size:13px; color:#666;">Dicetak pada: {{ now()->format('d F Y H:i') }}</div>
  </div>
  <div class="section">
    <div class="label">Anak Didik:</div>
    <div class="box">{{ $assessment->anakDidik->nama ?? '-' }}</div>
    <div class="label">Konsultan:</div>
    <div class="box">{{ $assessment->konsultan->nama ?? '-' }}</div>
    <div class="label">Kategori:</div>
    <div class="box">{{ ucfirst($assessment->kategori) }}</div>
    <div class="label">Tanggal Penilaian:</div>
    <div class="box">{{ $assessment->tanggal_assessment ? \Carbon\Carbon::parse($assessment->tanggal_assessment)->format('d F Y') : '-' }}</div>
  </div>
  <div class="section">
    <div class="label">Hasil Penilaian:</div>
    <div class="box">{{ $assessment->hasil_penilaian ?? '-' }}</div>
    <div class="label">Rekomendasi:</div>
    <div class="box">{{ $assessment->rekomendasi ?? '-' }}</div>
    <div class="label">Saran:</div>
    <div class="box">{{ $assessment->saran ?? '-' }}</div>
  </div>
  <div class="footer">
    Laporan ini dihasilkan otomatis oleh sistem.
  </div>
</body>

</html>