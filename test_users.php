<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Get all users
$users = User::all();

echo "Total users in database: " . $users->count() . "\n\n";

foreach ($users as $user) {
  echo "Name: {$user->name}\n";
  echo "Email: {$user->email}\n";
  echo "Role: {$user->role}\n";
  echo "---\n";
}
