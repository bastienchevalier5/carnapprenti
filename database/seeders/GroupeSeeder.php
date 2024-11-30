<?php

namespace Database\Seeders;

use App\Models\Groupe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $btsslam = new Groupe;
        $btsslam->nom = "BTS SIO SLAM";
        $btsslam->save();

        $btssisr = new Groupe;
        $btssisr->nom = "BTS SIO SISR";
        $btssisr->save();
    }
}
