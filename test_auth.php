<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Test authentication
$credentials = [
  'email' => 'admin@example.com',
  'password' => 'password'
];

$result = Auth::attempt($credentials);

echo "Login attempt result: ";
var_dump($result);

if ($result) {
  $user = Auth::user();
  echo "\nLogged in as:\n";
  echo "Name: {$user->name}\n";
  echo "Email: {$user->email}\n";
  echo "Role: {$user->role}\n";
} else {
  echo "\nLogin failed - checking credentials...\n";

  $user = User::where('email', 'admin@example.com')->first();

  if ($user) {
    echo "User found: {$user->name}\n";
    echo "Checking password...\n";

    $passwordValid = Hash::check('password', $user->password);
    echo "Password valid: " . ($passwordValid ? "YES" : "NO") . "\n";
    echo "Hashed password: {$user->password}\n";
  } else {
    echo "User not found!\n";
  }
}
