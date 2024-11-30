<?php

namespace Database\Seeders;

use App\Models\Modele;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModeleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modele = new Modele;
        $modele->nom = "BTS SIO SLAM - IIA Saint Nazaire";
        $modele->groupe_id = 1;
        $modele->site_id = 1;
        $modele->save();
    }
}
