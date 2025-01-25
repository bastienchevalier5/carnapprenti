<?php

namespace Database\Factories;

use App\Models\Livret;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompteRendu>
 */
class CompteRenduFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeThisYear(); // Obtenez une date aléatoire pour le début
        $endDate = (clone $startDate)->modify('+1 month'); // Ajoutez un mois pour la fin

        return [
            'livret_id' => Livret::factory(),
            'periode' => $startDate->format('F Y') . ' - ' . $endDate->format('F Y'), // Générer une période valide
            'activites_pro' => fake()->sentence(),
            'observations_apprenti' => fake()->sentence(),
            'observations_tuteur' => fake()->sentence(),
            'observations_referent' => fake()->sentence(),
        ];
    }
}
