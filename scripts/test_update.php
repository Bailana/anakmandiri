<?php
// scripts/test_update.php
// Bootstrap Laravel and call TerapisPatientController::update() directly for testing
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Bootstrap the framework
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

// helpers
function logline($msg)
{
  echo "[TEST] $msg\n";
}

// Find an assignment id to test (use 7 if exists)
$assignmentId = 7;
$assignment = App\Models\GuruAnakDidik::find($assignmentId);
if (!$assignment) {
  logline("Assignment id $assignmentId not found");
  exit(1);
}

// Find a user to act as - prefer admin, otherwise the assignment's user
$user = App\Models\User::where('role', 'admin')->first();
if (!$user) $user = App\Models\User::find($assignment->user_id);
if (!$user) {
  logline("No suitable user found to authenticate");
  exit(1);
}

// Set current user in auth guard
$app->make('auth')->setUser($user);
logline("Acting as user id={$user->id}, role={$user->role}");

// Prepare payload
$payload = [
  'user_id' => $user->id,
  'anak_didik_id' => $assignment->anak_didik_id,
  'status' => 'tidak aktif',
  'tanggal_mulai' => $assignment->tanggal_mulai ? $assignment->tanggal_mulai->format('Y-m-d') : null,
  'jam_mulai' => '13:00',
  'jenis_terapi' => $assignment->jenis_terapi ?? null,
  'terapis_nama' => $assignment->terapis_nama ?? null,
  'schedules' => [
    [
      'tanggal_mulai' => date('Y-m-d', strtotime('+1 day')),
      'jam_mulai' => '13:00',
    ],
  ],
];

logline('Payload: ' . json_encode($payload));

// Create request and call controller
$request = Request::create('/terapis/pasien/' . $assignmentId, 'PUT', $payload);
// set session/store if needed
$app->make('session')->driver()->start();
$request->setLaravelSession($app->make('session')->driver());

$controller = new App\Http\Controllers\TerapisPatientController();
try {
  $response = $controller->update($request, $assignmentId);
  logline('Controller returned: ' . (is_object($response) ? get_class($response) : (string)$response));
} catch (Throwable $ex) {
  logline('Exception: ' . $ex->getMessage());
  echo $ex->getTraceAsString();
}

// Show current DB state for assignment and schedules
$assignmentFresh = App\Models\GuruAnakDidik::with('schedules')->find($assignmentId);
logline('Assignment after update: ' . json_encode($assignmentFresh->toArray()));

$schedules = App\Models\GuruAnakDidikSchedule::where('guru_anak_didik_id', $assignmentId)->get()->toArray();
logline('Schedules after update: ' . json_encode($schedules));

logline('Finished');
