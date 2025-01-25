<?php

namespace Tests\Unit\Models;

use App\Models\Groupe;
use App\Models\Matiere;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatiereTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_groupes()
    {
        // Crée une matière
        $matiere = Matiere::factory()->create();

        // Crée quelques groupes
        $groupe1 = Groupe::factory()->create();
        $groupe2 = Groupe::factory()->create();

        // Attache les groupes à la matière via la relation 'groupes'
        $matiere->groupes()->attach([$groupe1->id, $groupe2->id]);

        // Récupère les groupes associés à la matière
        $groupes = $matiere->groupes;

        // Vérifie si la matière a bien les groupes attachés
        $this->assertCount(2, $groupes);
        $this->assertTrue($groupes->contains($groupe1));
        $this->assertTrue($groupes->contains($groupe2));
    }
}
