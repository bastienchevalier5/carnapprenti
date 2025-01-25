<?php

namespace Database\Factories;

use App\Models\Groupe;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modele>
 */
class ModeleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->name,
            'groupe_id' => Groupe::factory(),
            'site_id' => Site::factory()
        ];
    }
}
