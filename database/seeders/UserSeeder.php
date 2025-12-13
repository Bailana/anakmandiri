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
      'name' => 'Konsultan Pendidikan',
      'email' => 'konsultan.pendidikan@example.com',
      'password' => Hash::make('password'),
      'role' => 'konsultan',
      'email_verified_at' => now(),
    ]);

    // Terapis Sensori Integrasi
    User::create([
      'name' => 'Terapis SI',
      'email' => 'terapis.si@example.com',
      'password' => Hash::make('password'),
      'role' => 'terapis',
      'email_verified_at' => now(),
    ]);

    // Terapis Wicara
    User::create([
      'name' => 'Terapis Wicara',
      'email' => 'terapis.wicara@example.com',
      'password' => Hash::make('password'),
      'role' => 'terapis',
      'email_verified_at' => now(),
    ]);

    // Terapis Perilaku
    User::create([
      'name' => 'Terapis Perilaku',
      'email' => 'terapis.perilaku@example.com',
      'password' => Hash::make('password'),
      'role' => 'terapis',
      'email_verified_at' => now(),
    ]);
  }
}
