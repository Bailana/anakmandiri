<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\AssessmentController;

$argv = $_SERVER['argv'];
$anak = $argv[1] ?? null;
$kategori = $argv[2] ?? null;
if (!$anak || !$kategori) {
  echo "Usage: php tools/call_ppi_programs.php <anakId|name> <kategori>\n";
  exit(1);
}

// build request
$req = Request::create('/assessment/ppi-programs', 'GET', ['anak_didik_id' => $anak, 'kategori' => $kategori]);
$controller = new AssessmentController();
$res = $controller->ppiPrograms($req);

// $res is a JsonResponse
echo (string)$res->getContent() . "\n";
