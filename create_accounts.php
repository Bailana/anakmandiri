<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up Eloquent
$container = new Container();
$manager = new Capsule($container);

// Create accounts
$accounts = [
  [
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => 'password123',
    'role' => 'admin',
  ],
  [
    'name' => 'Karyawan 1',
    'email' => 'karyawan1@example.com',
    'password' => 'password123',
    'role' => 'guru',  // Karyawan di sini adalah guru
  ],
  [
    'name' => 'Karyawan 2',
    'email' => 'karyawan2@example.com',
    'password' => 'password123',
    'role' => 'guru',
  ],
  [
    'name' => 'Konsultan 1',
    'email' => 'konsultan1@example.com',
    'password' => 'password123',
    'role' => 'konsultan',
  ],
  [
    'name' => 'Konsultan 2',
    'email' => 'konsultan2@example.com',
    'password' => 'password123',
    'role' => 'konsultan',
  ],
  [
    'name' => 'Terapis 1',
    'email' => 'terapis1@example.com',
    'password' => 'password123',
    'role' => 'terapis',
  ],
  [
    'name' => 'Terapis 2',
    'email' => 'terapis2@example.com',
    'password' => 'password123',
    'role' => 'terapis',
  ],
];

foreach ($accounts as $account) {
  // Check if user already exists
  $existing = User::where('email', $account['email'])->first();

  if ($existing) {
    echo "⚠️  User {$account['email']} sudah ada di database.\n";
  } else {
    try {
      User::create([
        'name' => $account['name'],
        'email' => $account['email'],
        'password' => Hash::make($account['password']),
        'role' => $account['role'],
        'email_verified_at' => now(),
      ]);
      echo "✅ User {$account['name']} ({$account['email']}) berhasil dibuat dengan role: {$account['role']}\n";
    } catch (\Exception $e) {
      echo "❌ Gagal membuat user {$account['email']}: {$e->getMessage()}\n";
    }
  }
}

echo "\n✅ Proses pembuatan akun selesai!\n";
echo "\nDaftar akun yang telah dibuat:\n";
echo "================================\n";
echo "📋 ADMIN:\n";
echo "   Email: admin@example.com\n";
echo "   Password: password123\n\n";
echo "📋 KARYAWAN (Guru):\n";
echo "   Email: karyawan1@example.com | karyawan2@example.com\n";
echo "   Password: password123\n\n";
echo "📋 KONSULTAN:\n";
echo "   Email: konsultan1@example.com | konsultan2@example.com\n";
echo "   Password: password123\n\n";
echo "📋 TERAPIS:\n";
echo "   Email: terapis1@example.com | terapis2@example.com\n";
echo "   Password: password123\n";
echo "================================\n";
