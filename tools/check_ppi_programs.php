<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PpiItem;
use App\Models\AnakDidik;

$argv = $_SERVER['argv'];
$child = $argv[1] ?? null; // can be id or name
$kategori = $argv[2] ?? null;

if (!$child || !$kategori) {
  echo "Usage: php tools/check_ppi_programs.php <anakId|name> <kategori>\n";
  exit(1);
}

// try numeric id first
if (is_numeric($child)) {
  $anakId = (int)$child;
} else {
  $anak = AnakDidik::where('nama', 'like', "%{$child}%")->first();
  if (!$anak) {
    echo "Child not found by name: {$child}\n";
    exit(1);
  }
  $anakId = $anak->id;
}

$normalized = str_replace('_', ' ', $kategori);

$items = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
  $q->where('anak_didik_id', $anakId);
})->whereRaw('LOWER(kategori) = ?', [strtolower($normalized)])->get();

if ($items->isEmpty()) {
  echo "No exact-category matches. Trying LIKE fallback...\n";
  $items = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
    $q->where('anak_didik_id', $anakId);
  })->where('kategori', 'like', "%{$normalized}%")->get();
}

$out = [];
foreach ($items as $it) {
  $out[] = [
    'id' => $it->id,
    'nama_program' => $it->nama_program,
    'kategori' => $it->kategori,
    'ppi_id' => $it->ppi_id
  ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
