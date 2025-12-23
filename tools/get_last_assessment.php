<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;

$last = Assessment::with('anakDidik', 'konsultan')->orderBy('id', 'desc')->first();
if (!$last) {
  echo "No assessments found.\n";
  exit(0);
}

echo "ID: {$last->id}\n";
echo "Anak: " . ($last->anakDidik->nama ?? $last->anak_didik_id) . "\n";
echo "Kategori: {$last->kategori}\n";
echo "Perkembangan: " . ($last->perkembangan ?? '-') . "\n";
echo "Tanggal: " . ($last->tanggal_assessment ? $last->tanggal_assessment->format('Y-m-d') : '-') . "\n";
echo "Program ID: " . ($last->program_id ?? 'null') . "\n";
echo "Created At: {$last->created_at}\n";
