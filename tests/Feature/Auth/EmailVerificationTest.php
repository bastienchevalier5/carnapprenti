<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que si l'utilisateur a déjà vérifié son email, il est redirigé vers la page d'accueil avec un paramètre 'verified'.
     */
    public function test_already_verified_user_is_redirected(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user);

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('accueil', [], false).'?verified=1');
    }

    /**
     * Test que l'utilisateur non vérifié peut vérifier son email avec une URL valide.
     */
    public function test_user_can_verify_email(): void
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user);

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('accueil', [], false).'?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);

        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    /**
     * Test que la vérification échoue si l'URL n'est pas signée correctement.
     */
    public function test_verification_fails_with_invalid_signature(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $invalidVerificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email@example.com')]
        );

        $this->actingAs($user);

        $response = $this->get($invalidVerificationUrl);

        $response->assertStatus(403);
    }

    /**
     * Test que la vérification échoue si le hash de l'email ne correspond pas.
     */
    public function test_verification_fails_with_mismatched_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('another-email@example.com')]
        );

        $this->actingAs($user);

        $response = $this->get($verificationUrl);

        $response->assertStatus(403);
    }
}
