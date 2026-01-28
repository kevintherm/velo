<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use App\Models\AuthSession;
use App\Models\AuthOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSystemCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superusers_page_renders_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('pages::manage-system-collection', ['collection' => 'superusers'])
            ->assertStatus(200)
            ->assertSee('Superusers');
    }

    public function test_can_create_superuser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('pages::manage-system-collection', ['collection' => 'superusers'])
            ->set('data.name', 'New Admin')
            ->set('data.email', 'admin@example.com')
            ->set('data.password', 'password123')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
        ]);
    }

    public function test_can_update_superuser()
    {
        $user = User::factory()->create();
        $target = User::factory()->create(['name' => 'Old Name']);
        $this->actingAs($user);

        Livewire::test('pages::manage-system-collection', ['collection' => 'superusers'])
            ->call('showRecord', $target->id)
            ->set('data.name', 'New Name')
            ->call('saveRecord')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'New Name',
        ]);
    }
    
    // Auth Sessions Tests
    public function test_sessions_page_renders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Livewire::test('pages::manage-system-collection', ['collection' => 'sessions'])
             ->assertStatus(200)
             ->assertSee('Auth Sessions');
    }

    // OTPs Tests
    public function test_otps_page_renders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Livewire::test('pages::manage-system-collection', ['collection' => 'otps'])
             ->assertStatus(200)
             ->assertSee('OTPs');
    }
}
