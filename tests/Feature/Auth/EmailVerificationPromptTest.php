<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_to_accueil_if_user_is_logged_in_and_email_is_verified(): void
    {
        // Crée un utilisateur avec un email vérifié
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Simule l'authentification
        $this->actingAs($user);

        // Envoie une requête GET vers la route
        $response = $this->get(route('verification.notice'));

        // Vérifie la redirection vers la route 'accueil'
        $response->assertRedirect(route('accueil'));
    }

    public function test_it_shows_verify_email_view_if_user_is_logged_in_and_email_is_not_verified(): void
    {
        // Crée un utilisateur sans email vérifié
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Simule l'authentification
        $this->actingAs($user);

        // Envoie une requête GET vers la route
        $response = $this->get(route('verification.notice'));

        // Vérifie que la vue "auth.verify-email" est retournée
        $response->assertViewIs('auth.verify-email');
    }

    public function test_it_redirects_to_login_if_no_user_is_logged_in(): void
    {
        // Envoie une requête GET sans utilisateur authentifié
        $response = $this->get(route('verification.notice'));

        // Vérifie la redirection vers la page de connexion
        $response->assertRedirect(route('login'));
    }
}
