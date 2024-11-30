<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $saintnazaire = new Site;
        $saintnazaire->nom = "IIA Saint Nazaire";
        $saintnazaire->save();

        $laval = new Site;
        $laval->nom = "IIA Laval";
        $laval->save();
    }
}
