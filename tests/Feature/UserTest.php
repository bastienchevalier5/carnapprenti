<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Livret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_livrets()
    {
        // Crée un utilisateur
        $user = User::factory()->create();

        // Crée quelques livrets associés à l'utilisateur
        $livret1 = Livret::factory()->create(['user_id' => $user->id]);
        $livret2 = Livret::factory()->create(['user_id' => $user->id]);

        // Récupère les livrets associés à l'utilisateur
        $livrets = $user->livrets;

        // Vérifie si l'utilisateur a bien les livrets attachés
        $this->assertCount(2, $livrets);
        $this->assertTrue($livrets->contains($livret1));
        $this->assertTrue($livrets->contains($livret2));
    }
}
