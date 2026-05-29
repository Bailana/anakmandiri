<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();
$periodStart = Carbon\Carbon::create(2026, 1, 1)->toDateString();
$periodEnd = Carbon\Carbon::create(2026, 6, 30)->toDateString();
$rows = App\Models\GuruAnakDidikSchedule::with('assignment')->whereBetween('tanggal_mulai', [$periodStart, $periodEnd])->get();
echo 'Count: ' . count($rows) . "\n";
foreach ($rows as $r) {
    echo $r->id . ' child=' . $r->assignment->anak_didik_id . ' type=' . $r->jenis_terapi . ' date=' . $r->tanggal_mulai . ' therapist=' . $r->terapis_nama . "\n";
}
