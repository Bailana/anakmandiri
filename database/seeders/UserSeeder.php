<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Admin User
    User::create([
      'name' => 'Admin',
      'email' => 'admin@example.com',
      'password' => Hash::make('password'),
      'role' => 'admin',
      'email_verified_at' => now(),
    ]);

    // Guru User
    User::create([
      'name' => 'Guru',
      'email' => 'guru@example.com',
      'password' => Hash::make('password'),
      'role' => 'guru',
      'email_verified_at' => now(),
    ]);

    // Konsultan User
    User::create([
      'name' => 'Konsultan',
      'email' => 'konsultan@example.com',
      'password' => Hash::make('password'),
      'role' => 'konsultan',
      'email_verified_at' => now(),
    ]);

    // Terapis User
    User::create([
      'name' => 'Terapis',
      'email' => 'terapis@example.com',
      'password' => Hash::make('password'),
      'role' => 'terapis',
      'email_verified_at' => now(),
    ]);
  }
}
