<?php

namespace Tests\Feature;

use App\Models\CompteRendu;
use App\Models\Livret;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class CompteRenduControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_compte_rendu_for_given_livret_and_periode(): void
    {
        // Créer un utilisateur et un livret
        $user = User::factory()->create();
        $livret = Livret::factory()->create();

        // Créer un compte rendu pour ce livret et une période spécifique
        $periode = Carbon::now()->format('F Y') . ' - ' . Carbon::now()->addMonth()->format('F Y');
        $compteRendu = CompteRendu::factory()->create([
            'livret_id' => $livret->id,
            'periode' => $periode,
        ]);

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête GET pour afficher le compte rendu
        $response = $this->get(route('compte_rendu.show', [$livret->id, $periode]));

        // Vérifier que la vue est correcte et que les données sont passées
        $response->assertOk();
        $response->assertViewIs('compte_rendu.show');
        $response->assertViewHas('compte_rendu', $compteRendu);
        $response->assertViewHas('livret', $livret);
    }

    public function test_it_creates_a_new_compte_rendu(): void
    {
        // Créer un utilisateur et un livret
        $user = User::factory()->create();
        $livret = Livret::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user);

        // Définir une période
        $periode = 'January 2025 - February 2025';

        // Envoyer une requête POST pour créer un compte rendu
        $response = $this->post(route('compte_rendus.store', [$livret->id, $periode]), [
            'observations_apprenti' => 'Observations de l\'apprenti...',
        ]);

        // Vérifier la redirection et les données
        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('compte_rendus', [
            'livret_id' => $livret->id,
            'periode' => $periode,
            'observations_apprenti' => 'Observations de l\'apprenti...',
        ]);
    }

    public function test_it_updates_an_existing_compte_rendu(): void
{
    $user = User::factory()->create();
    $livret = Livret::factory()->create();
    $compteRendu = CompteRendu::factory()->create([
        'livret_id' => $livret->id,
        'periode' => 'January 2025 - February 2025',
    ]);

    $this->actingAs($user);

    $response = $this->put(route('compte_rendus.update', $compteRendu->id), [
        'observations_apprenti' => 'Nouvelles observations de l\'apprenti...',
    ]);

    $response->assertRedirect(route('livret.index'));

    $this->assertDatabaseHas('compte_rendus', [
        'id' => $compteRendu->id,
        'observations_apprenti' => 'Nouvelles observations de l\'apprenti...',
    ]);
}


    public function test_it_validates_store_request(): void
    {
        // Créer un utilisateur et un livret
        $user = User::factory()->create();
        $livret = Livret::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête POST avec des données invalides
        $response = $this->post(route('compte_rendus.store', [$livret->id, 'Invalid Periode']), []);

        // Vérifier qu'il y a des erreurs de validation
        $response->assertSessionHasNoErrors();
    }

    public function test_it_redirects_to_login_if_user_is_not_authenticated(): void
    {
        // Créer un livret
        $livret = Livret::factory()->create();

        // Tenter d'accéder à une route sans être authentifié
        $response = $this->get(route('compte_rendu.show', [$livret->id, 'January 2025 - February 2025']));

        // Vérifier la redirection vers la page de connexion
        $response->assertRedirect(route('login'));
    }

    public function test_it_generates_default_period(): void
    {
        // Créer un utilisateur et un livret
        $user = User::factory()->create();
        $livret = Livret::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user);

        // Appeler l'action `show` sans fournir de période
        $response = $this->get(route('compte_rendu.show', ['id_livret' => $livret->id]));

        // Vérifier que la période générée par défaut est correcte
        $expectedPeriod = Carbon::now()->format('F Y') . ' - ' . Carbon::now()->addMonth()->format('F Y');
        $response->assertViewHas('periode', $expectedPeriod);
    }

    public function test_it_saves_tuteur_observations(): void
    {
        // Créer un utilisateur (tuteur) et un livret
        $user = User::factory()->create();
        Bouncer::assign('tuteur')->to($user);
        $livret = Livret::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête POST avec des données pour les observations du tuteur
        $response = $this->post(route('compte_rendus.store', [$livret->id, 'January 2025 - February 2025']), [
            'observations_tuteur' => 'Les observations du tuteur...',
        ]);

        // Vérifier la redirection et les données enregistrées
        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('compte_rendus', [
            'livret_id' => $livret->id,
            'observations_tuteur' => 'Les observations du tuteur...',
        ]);
    }

    public function test_it_saves_referent_observations(): void
    {
        // Créer un utilisateur (référent) et un livret
        $user = User::factory()->create();
        Bouncer::assign('referent')->to($user);
        $livret = Livret::factory()->create();

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête POST avec des données pour les observations du référent
        $response = $this->post(route('compte_rendus.store', [$livret->id, 'January 2025 - February 2025']), [
            'observations_referent' => 'Les observations du référent...',
        ]);

        // Vérifier la redirection et les données enregistrées
        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('compte_rendus', [
            'livret_id' => $livret->id,
            'observations_referent' => 'Les observations du référent...',
        ]);
    }

    public function test_it_updates_tuteur_observations(): void
    {
        // Créer un utilisateur (tuteur), un livret, et un compte rendu
        $user = User::factory()->create();
        Bouncer::assign('tuteur')->to($user);
        $livret = Livret::factory()->create();
        $compteRendu = CompteRendu::factory()->create(['livret_id' => $livret->id]);

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête PUT pour mettre à jour les observations du tuteur
        $response = $this->put(route('compte_rendus.update', $compteRendu->id), [
            'observations_tuteur' => 'Observations mises à jour par le tuteur...',
        ]);

        // Vérifier la redirection et les données mises à jour
        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('compte_rendus', [
            'id' => $compteRendu->id,
            'observations_tuteur' => 'Observations mises à jour par le tuteur...',
        ]);
    }

    public function test_it_updates_referent_observations(): void
    {
        // Créer un utilisateur (référent), un livret, et un compte rendu
        $user = User::factory()->create();
        Bouncer::assign('referent')->to($user);
        $livret = Livret::factory()->create();
        $compteRendu = CompteRendu::factory()->create(['livret_id' => $livret->id]);

        // Simuler l'authentification
        $this->actingAs($user);

        // Envoyer une requête PUT pour mettre à jour les observations du référent
        $response = $this->put(route('compte_rendus.update', $compteRendu->id), [
            'observations_referent' => 'Observations mises à jour par le référent...',
        ]);

        // Vérifier la redirection et les données mises à jour
        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('compte_rendus', [
            'id' => $compteRendu->id,
            'observations_referent' => 'Observations mises à jour par le référent...',
        ]);
    }

}
