<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update avatar untuk beberapa user
$users = [
  1 => 'avatars/1.svg',
  2 => 'avatars/2.svg',
  3 => 'avatars/3.svg',
  4 => 'avatars/4.svg',
];

foreach ($users as $userId => $avatarPath) {
  $user = App\Models\User::find($userId);
  if ($user) {
    $user->avatar = $avatarPath;
    $user->save();
    echo "Avatar updated for {$user->name}: {$avatarPath}\n";
  }
}

echo "\nDone!\n";
