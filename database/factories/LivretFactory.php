<?php

namespace Database\Factories;

use App\Models\Modele;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Livret>
 */
class LivretFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'observation_apprenti_global' => $this->faker->sentence,
            'observation_admin' => $this->faker->sentence,
            'user_id' => User::factory(),
            'modele_id' => Modele::factory()
        ];
    }

    public function withObservations(string $apprentiObservation, string $adminObservation): self
    {
        return $this->state(function (array $attributes) use ($apprentiObservation, $adminObservation) {
            return [
                'observation_apprenti_global' => $apprentiObservation,
                'observation_admin' => $adminObservation,
            ];
        });
    }

}
