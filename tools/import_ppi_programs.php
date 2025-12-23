<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\PpiItem;
use App\Models\Program;

// Ensure programs table exists
if (!Schema::hasTable('programs')) {
  echo "The 'programs' table does not exist. Run migrations first: php artisan migrate\n";
  exit(1);
}

$items = PpiItem::with('ppi')->whereNotNull('nama_program')->get();

$created = 0;
$skipped = 0;
$seen = [];

foreach ($items as $it) {
  if (!$it->ppi) continue;
  $anakId = $it->ppi->anak_didik_id;
  $name = trim($it->nama_program);
  if ($name === '') continue;

  // Normalize kategori to enum-friendly value if possible
  $rawKategori = trim($it->kategori ?? '');
  $kategori = $rawKategori === '' ? null : str_replace(' ', '_', strtolower($rawKategori));

  // de-duplicate by anak_id + name + kategori
  $key = $anakId . '||' . $name . '||' . ($kategori ?? '');
  if (isset($seen[$key])) {
    $skipped++;
    continue;
  }
  $seen[$key] = true;

  $prog = Program::firstOrCreate(
    [
      'anak_didik_id' => $anakId,
      'nama_program' => $name,
      'kategori' => $kategori,
    ],
    [
      'konsultan_id' => null,
      'deskripsi' => null,
    ]
  );

  if ($prog->wasRecentlyCreated) {
    $created++;
  } else {
    $skipped++;
  }
}

echo "Import finished. Created: {$created}. Skipped/Existing: {$skipped}.\n";
