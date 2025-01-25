<?php

namespace Tests\Unit\Models;

use App\Models\Groupe;
use App\Models\Matiere;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_matieres()
    {
        // Crée un groupe
        $groupe = Groupe::factory()->create();

        // Crée quelques matières
        $matiere1 = Matiere::factory()->create();
        $matiere2 = Matiere::factory()->create();

        // Attache les matières au groupe via la relation 'matieres'
        $groupe->matieres()->attach([$matiere1->id, $matiere2->id]);

        // Récupère les matières du groupe
        $matieres = $groupe->matieres;

        // Vérifie si le groupe a bien les matières attachées
        $this->assertCount(2, $matieres);
        $this->assertTrue($matieres->contains($matiere1));
        $this->assertTrue($matieres->contains($matiere2));
    }
}
