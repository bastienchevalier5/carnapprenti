<?php

namespace Tests\Feature;

use App\Models\Livret;
use App\Models\Modele;
use App\Models\User;
use App\Services\PdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Storage;
use Tests\TestCase;

class LivretControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de l'index pour les différents rôles.
     */
    public function testIndexRedirectsToLoginIfNotAuthenticated()
    {
        $response = $this->get(route('livret.index'));
        $response->assertRedirect(route('login'));
    }

    public function testIndexForReferent()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);
        $apprenant = User::factory()->create(['groupe_id' => $referent->groupe_id]);

        Livret::factory()->create(['user_id' => $apprenant->id]);

        $this->actingAs($referent);

        $response = $this->get(route('livret.index'));

        $response->assertStatus(200);
        $response->assertViewHas('livrets');
    }

    public function testIndexForApprenant()
    {
        $apprenant = User::factory()->create();
        Bouncer::assign('apprenant')->to($apprenant);
        Livret::factory()->create(['user_id' => $apprenant->id]);

        $this->actingAs($apprenant);

        $response = $this->get(route('livret.index'));

        $response->assertStatus(200);
        $response->assertViewHas('livrets');
    }

    public function testTuteurCanViewLivretsOfApprenant()
{
    // Créer un utilisateur de type tuteur avec un apprenant associé
    $tuteur = User::factory()->create();
    Bouncer::assign('tuteur')->to($tuteur);

    // Créer un utilisateur de type apprenant
    $apprenant = User::factory()->create();

    // Associer l'apprenant à un tuteur (en supposant que `apprenant_id` est une relation sur le modèle User)
    $tuteur->apprenant_id = $apprenant->id;
    $tuteur->save();

    // Créer des livrets pour l'apprenant
    $livret1 = Livret::factory()->create(['user_id' => $apprenant->id]);
    $livret2 = Livret::factory()->create(['user_id' => $apprenant->id]);

    // Effectuer la requête en simulant une connexion en tant que tuteur
    $response = $this->actingAs($tuteur)->get(route('livret.index'));

    // Vérifier que les livrets associés à l'apprenant sont présents dans la réponse
    $response->assertSee($livret1->id);
    $response->assertSee($livret2->id);

    // Vérifier que la vue correcte a été rendue
    $response->assertViewIs('livrets.index');
    $response->assertViewHas('livrets');
}


    public function testIndexForUnauthorizedRole()
    {
        $user = User::factory()->create();

        Bouncer::assign('admin')->to($user);

        $this->actingAs($user);

        $response = $this->get(route('livret.index'));

        $response->assertStatus(200);
        $response->assertViewHas('livrets', collect());
    }

    /**
     * Test de la méthode create.
     */
    public function testCreateRedirectsToLoginIfNotAuthenticated()
    {
        $response = $this->get(route('livret.create'));
        $response->assertRedirect(route('login'));
    }

    public function testCreateForReferent()
    {
        // Créer un référent avec un groupe
        $referent = User::factory()->create(['groupe_id' => 1]);
        Bouncer::assign('referent')->to($referent);

        // Créer des modèles et des apprenants pour le groupe
        Modele::factory()->count(3)->create();
        User::factory()->count(3)->create(['groupe_id' => $referent->groupe_id]);

        // Simuler l'authentification
        $this->actingAs($referent);

        // Appeler la route
        $response = $this->get(route('livret.create'));

        // Assertions
        $response->assertStatus(200);
        $response->assertViewIs('livrets.form');
        $response->assertViewHasAll(['livret', 'modeles', 'apprenants']);
    }


    /**
     * Test de la méthode store.
     */
    // public function testStoreValidatesInput()
    // {
    //     $referent = User::factory()->create();
    //     Bouncer::assign('referent')->to($referent);
    //     Modele::factory()->create();
    //     User::factory()->create(['groupe_id' => $referent->groupe_id]);

    //     $this->actingAs($referent);

    //     $response = $this->post(route('livret.store'), [
    //         'modele_id' => null,
    //         'apprenant_id' => null,
    //     ]);

    //     $response->assertSessionHasErrors(['modele_id', 'apprenant_id']);
    // }

    public function testStoreSavesLivret()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);
        $modele = Modele::factory()->create();
        $apprenant = User::factory()->create(['groupe_id' => $referent->groupe_id]);

        $this->actingAs($referent);

        $response = $this->post(route('livret.store'), [
            'modele_id' => $modele->id,
            'apprenant_id' => $apprenant->id,
        ]);

        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('livrets', [
            'modele_id' => $modele->id,
            'user_id' => $apprenant->id,
        ]);
    }

    /**
     * Test de la méthode edit.
     */
    public function testEditRedirectsToLoginIfNotAuthenticated()
    {
        $livret = Livret::factory()->create();

        $response = $this->get(route('livret.edit', $livret));
        $response->assertRedirect(route('login'));
    }

    public function testEditForReferent()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);
        $livret = Livret::factory()->create();
        Modele::factory()->count(3)->create();

        $this->actingAs($referent);

        $response = $this->get(route('livret.edit', $livret));

        $response->assertStatus(200);
        $response->assertViewHas(['livret', 'modeles', 'apprenants']);
    }

    /**
     * Test de la méthode update.
     */
    // public function testUpdateValidatesInput()
    // {
    //     $referent = User::factory()->create();
    //     Bouncer::assign('referent')->to($referent);
    //     $livret = Livret::factory()->create();

    //     $this->actingAs($referent);

    //     $response = $this->put(route('livret.update', $livret), [
    //         'modele_id' => null,
    //         'apprenant_id' => null,
    //     ]);

    //     $response->assertSessionHasErrors(['modele_id', 'apprenant_id']);
    // }

    public function testUpdateSavesChanges()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);
        $livret = Livret::factory()->create();
        $modele = Modele::factory()->create();
        $apprenant = User::factory()->create(['groupe_id' => $referent->groupe_id]);

        $this->actingAs($referent);

        $response = $this->put(route('livret.update', $livret), [
            'modele_id' => $modele->id,
            'apprenant_id' => $apprenant->id,
        ]);

        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseHas('livrets', [
            'id' => $livret->id,
            'modele_id' => $modele->id,
            'user_id' => $apprenant->id,
        ]);
    }

    /**
     * Test de la méthode destroy.
     */
    public function testDestroyDeletesLivret()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);
        $livret = Livret::factory()->create();

        $this->actingAs($referent);

        $response = $this->delete(route('livret.destroy', $livret));

        $response->assertRedirect(route('livret.index'));
        $this->assertDatabaseMissing('livrets', ['id' => $livret->id]);
    }

    public function test_it_redirects_if_referent_cannot_create_livret()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);

        $this->actingAs($referent);

        $response = $this->get(route('livret.create'));

        $response->assertRedirect(route('livret.index'))
                 ->assertSessionHas('error', 'Vous ne pouvez pas créer un livret.');
    }

    public function test_it_redirects_back_with_error_if_invalid_identifiers_are_provided_in_store()
    {
        $referent = User::factory()->create();
        Bouncer::assign('referent')->to($referent);

        $this->actingAs($referent);

        $response = $this->post(route('livret.store'), [
            'modele_id' => 'invalid',
            'apprenant_id' => 'invalid',
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('error', 'Les identifiants fournis ne sont pas valides.');
    }

    public function test_it_redirects_if_user_cannot_modify_livret()
    {
        $referent = User::factory()->create([
            'groupe_id' => 2
        ]);
        $anotherReferent = User::factory()->create([
            'groupe_id' => 1
        ]);
        Bouncer::assign('referent')->to($referent);
        Bouncer::assign('referent')->to($anotherReferent);
        $apprenant = User::factory()->create([
            'groupe_id' => 1
        ]);

        Bouncer::assign('apprenant')->to($apprenant);

        $livret = Livret::factory()->create([
            'user_id' => $apprenant->id,
        ]);

        $this->actingAs($referent);

        $response = $this->get(route('livret.edit', $livret));

        $response->assertRedirect(route('livret.index'))
                 ->assertSessionHas('error', 'Vous ne pouvez pas modifier ce livret.');
    }

    public function test_it_handles_invalid_identifiers_in_update()
{
    $livret = Livret::factory()->create();
    $response = $this->actingAs($livret->user)
        ->put(route('livret.update', $livret->id), [
            'modele_id' => 0,
            'apprenant_id' => 0,
        ]);

    $response->assertRedirect(route('livret.index'))
             ->assertSessionHas('error', 'Les identifiants sont invalides.');
}


    public function test_it_displays_observations_form_for_valid_livret()
    {
        $livret = Livret::factory()->create();

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('livret.observations_form', $livret));

        $response->assertOk()
                 ->assertViewIs('livrets.observations')
                 ->assertViewHas('livret');
    }

    public function test_observations_updates_successfully()
{
    // Créer un utilisateur et se connecter
    $user = User::factory()->create();

    // Créer un modèle et un livret associé avec des observations initiales
    $modele = Modele::factory()->create();
    $livret = Livret::factory()->create([
        'modele_id' => $modele->id,
        'user_id' => $user->id,
        'observation_apprenti_global' => 'Ancienne observation pour l\'apprenti.',
        'observation_admin' => 'Ancienne observation pour l\'administration.'
    ]);

    // Test 1: Effectuer une mise à jour avec de nouvelles valeurs pour les observations
    $formData = [
        'observation_apprenti_global' => 'Ceci est une observation pour l\'apprenti.',
        'observation_admin' => 'Ceci est une observation pour l\'administration.',
    ];

    $response = $this->actingAs($user)->put(route('livret.observations', $livret), $formData);
    $livret->refresh();

    $this->assertEquals('Ceci est une observation pour l\'apprenti.', $livret->observation_apprenti_global);
    $this->assertEquals('Ceci est une observation pour l\'administration.', $livret->observation_admin);
    $response->assertRedirect(route('livret.index'));
    $response->assertSessionHas('success', 'Les observations ont bien été enregistrées.');

    // Test 2: Effectuer une mise à jour où les observations sont envoyées comme null (absentes)
    $formData = [
        'observation_apprenti_global' => null,  // Simuler une absence d'observation
        'observation_admin' => null,  // Simuler une absence d'observation
    ];

    $response = $this->actingAs($user)->put(route('livret.observations', $livret), $formData);
    $livret->refresh();

    // Vérifier que les observations ont été enregistrées comme null
    $this->assertNull($livret->observation_apprenti_global);
    $this->assertNull($livret->observation_admin);
    $response->assertRedirect(route('livret.index'));
    $response->assertSessionHas('success', 'Les observations ont bien été enregistrées.');
}





    public function test_it_redirects_if_modele_is_missing_when_generating_pdf()
    {
        $livret = Livret::factory()->create(['modele_id' => null]); // Livret sans modèle

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('livrets.pdf', $livret));

        $response->assertRedirect(route('livret.index'))
                 ->assertSessionHas('error', 'Le modèle associé est introuvable.');
    }

    public function test_pdf_generation_successfully()
{
    // Créer un utilisateur et se connecter
    $user = User::factory()->create();

    // Créer un modèle et un livret associé
    $modele = Modele::factory()->create();
    $livret = Livret::factory()->create([
        'modele_id' => $modele->id,
        'user_id' => $user->id
    ]);

    // S'assurer que l'utilisateur est authentifié avant de tester la génération du PDF
    $this->actingAs($user);

    // Simuler la génération du PDF
    Storage::fake('public');  // Simuler le système de fichiers public

    // Effectuer la requête pour générer le PDF
    $response = $this->get(route('livrets.pdf', $livret->id));

    // Vérifier que la réponse n'a pas effectué de redirection (code 302)

    // Vérifier que le fichier PDF a été généré
    $filePath = 'livrets/livret-' . $livret->id . '.pdf';
    Storage::disk('public')->assertExists($filePath);  // Vérifier si le fichier existe dans le répertoire simulé
}


}
