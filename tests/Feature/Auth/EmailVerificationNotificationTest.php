<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_if_user_has_verified_email()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertRedirect(route('accueil'));
    }

    public function test_it_sends_verification_email_if_user_email_is_not_verified()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        Notification::fake();

        $response = $this->actingAs($user)->post(route('verification.send'));

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }
}
