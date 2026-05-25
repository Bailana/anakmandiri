<?php

namespace Tests\Feature;

use App\Models\Konsultan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaporAnakAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_rapor_anak(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->get('/rapor-anak')
            ->assertOk();
    }

    public function test_guru_can_access_rapor_anak(): void
    {
        $user = User::factory()->create([
            'role' => 'guru',
        ]);

        $this->actingAs($user)
            ->get('/rapor-anak')
            ->assertOk();
    }

    public function test_konsultan_spesialisasi_pendidikan_can_access_rapor_anak(): void
    {
        $user = User::factory()->create([
            'role' => 'konsultan',
        ]);

        Konsultan::create([
            'user_id' => $user->id,
            'nama' => 'Konsultan Pendidikan',
            'email' => $user->email,
            'spesialisasi' => 'Pendidikan',
        ]);

        $this->actingAs($user)
            ->get('/rapor-anak')
            ->assertOk();
    }

    public function test_non_pendidikan_konsultan_cannot_access_rapor_anak(): void
    {
        $user = User::factory()->create([
            'role' => 'konsultan',
        ]);

        Konsultan::create([
            'user_id' => $user->id,
            'nama' => 'Konsultan Psikologi',
            'email' => $user->email,
            'spesialisasi' => 'Psikologi',
        ]);

        $this->actingAs($user)
            ->get('/rapor-anak')
            ->assertForbidden();
    }
}
