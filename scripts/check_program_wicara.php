<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProgramWicara;
use Illuminate\Support\Facades\DB;

$userId = 47;
$sixMonthsAgo = now()->subMonths(6);

$countByUser = ProgramWicara::where('user_id', $userId)->where('created_at','>=',$sixMonthsAgo)->count();
$countAllRecent = ProgramWicara::where('created_at','>=',$sixMonthsAgo)->count();
$sample = ProgramWicara::where('created_at','>=',$sixMonthsAgo)->limit(10)->get()->toArray();

echo "Count program_wicara for user_id={$userId} (last 6 months): {$countByUser}\n";
echo "Total program_wicara (last 6 months): {$countAllRecent}\n";
echo "Sample rows (last 10):\n";
print_r($sample);

// Show distinct user_id values in table
$distinctUsers = DB::table('program_wicara')->distinct()->pluck('user_id')->toArray();
echo "Distinct user_id in program_wicara: ";
print_r($distinctUsers);
