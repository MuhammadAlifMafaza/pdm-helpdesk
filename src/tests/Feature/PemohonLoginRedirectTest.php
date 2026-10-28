<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemohonLoginRedirectTest extends TestCase
{
    private User $pemohon;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'helpdesk_dev',
            'queue.default' => 'sync',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        foreach (['admin', 'pemohon'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->pemohon = User::create([
            'name' => 'Pemohon Uji Login',
            'email' => 'pemohon@uji-login.test',
            'password' => bcrypt('password'),
        ]);
        $this->pemohon->syncRoles(['pemohon']);
    }

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', '%@uji-login.test')->pluck('id');

        DB::table('model_has_roles')
            ->whereIn('model_id', $userIds)
            ->where('model_type', User::class)
            ->delete();

        User::whereIn('id', $userIds)->forceDelete();

        parent::tearDown();
    }

    public function test_tamu_membuka_panel_pemohon_dialihkan_ke_login_custom(): void
    {
        $this->get('/pemohon')->assertRedirect('/login');
    }

    public function test_login_bawaan_panel_pemohon_tidak_terdaftar(): void
    {
        $this->get('/pemohon/login')->assertNotFound();
    }

    public function test_login_custom_mengembalikan_tujuan_awal_ke_panel_pemohon(): void
    {
        $this->withSession(['url.intended' => '/pemohon'])
            ->post('/login', [
                'email' => $this->pemohon->email,
                'password' => 'password',
            ])
            ->assertRedirect('/pemohon');
    }
}
