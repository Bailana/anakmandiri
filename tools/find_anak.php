<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AnakDidik;

$argv = $_SERVER['argv'];
$q = $argv[1] ?? null;
if (!$q) {
  echo "Usage: php tools/find_anak.php <name-substring>\n";
  exit(1);
}
$rows = AnakDidik::where('nama', 'like', "%{$q}%")->take(20)->get()->map(function ($a) {
  return ['id' => $a->id, 'nama' => $a->nama, 'nis' => $a->nis];
});
echo json_encode($rows->values()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
