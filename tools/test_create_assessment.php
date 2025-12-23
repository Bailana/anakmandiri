<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\AssessmentController;
use App\Models\AnakDidik;

$anak = $argv[1] ?? null;
if (!$anak) {
  echo "Usage: php tools/test_create_assessment.php <anakId> [kategori]\n";
  exit(1);
}
$kategori = $argv[2] ?? 'akademik';

$payload = [
  'anak_didik_id' => $anak,
  'kategori' => $kategori,
  'program_id' => null,
  'konsultan_id' => null,
  'perkembangan' => 2,
  'hasil_penilaian' => 'Test hasil penilaian',
  'rekomendasi' => 'Test rekomendasi',
  'saran' => 'Test saran',
  'tanggal_assessment' => date('Y-m-d'),
  'kemampuan' => [],
];

$req = Request::create('/assessment', 'POST', $payload);
$controller = new AssessmentController();
$res = $controller->store($req);

// $res is a RedirectResponse
echo "Status: " . (method_exists($res, 'getStatusCode') ? $res->getStatusCode() : 'redirect') . "\n";
echo "Headers: \n";
print_r($res->headers->all());
