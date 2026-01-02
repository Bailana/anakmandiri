<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Konsultan;

$name = 'Yulidar';
$users = User::where('name', 'like', "%{$name}%")->get(['id','name','email'])->toArray();
print_r(['users_found' => $users]);

if(count($users) > 0) {
    foreach($users as $u) {
        $k = Konsultan::where('user_id', $u['id'])->first();
        echo "\nUser id: {$u['id']} -> ";
        if($k) {
            print_r(['konsultan_id' => $k->id, 'user_id' => $k->user_id, 'spesialisasi' => $k->spesialisasi]);
        } else {
            echo "no konsultan linked\n";
        }
    }
} else {
    echo "No user matching '{$name}' found. Listing konsultan with user_id not null:\n";
    $rows = Konsultan::whereNotNull('user_id')->get(['id','user_id','spesialisasi'])->toArray();
    print_r($rows);
}
