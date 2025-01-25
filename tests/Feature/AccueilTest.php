<?php

namespace Tests\Features;

use App\Models\User;
use Tests\TestCase;

class AccueilTest extends TestCase
{
    public function test_acceuil_page_is_accessible()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->actingAs($user)->get(route('accueil'));

        $response->assertStatus(status: 200);
        $response->assertViewIs('index');
    }
}
