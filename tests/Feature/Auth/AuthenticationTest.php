<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Crypt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
{
    // Crée un utilisateur avec un mot de passe chiffré
    $user = User::factory()->create([
        'password' => Crypt::encryptString('password'), // Mot de passe compatible avec authenticate()
    ]);

    // Simule une requête de connexion avec les bonnes informations
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password', // Mot de passe en clair envoyé par l'utilisateur
    ]);

    // Vérifie que l'utilisateur est authentifié
    $this->assertAuthenticated();

    // Vérifie que l'utilisateur est redirigé vers la bonne route
    $response->assertRedirect(route('accueil', absolute: false));
}




    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
