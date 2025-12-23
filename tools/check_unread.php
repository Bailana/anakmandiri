<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$rows = User::whereIn('role', ['konsultan', 'terapis'])->get()->map(function ($u) {
  return [
    'id' => $u->id,
    'name' => $u->name,
    'role' => $u->role,
    'unread' => $u->unreadNotifications()->count(),
  ];
});

echo json_encode($rows->values()->all());
