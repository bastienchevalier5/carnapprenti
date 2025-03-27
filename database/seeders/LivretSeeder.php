<?php

namespace Database\Seeders;

use App\Models\Livret;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LivretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $livret = new Livret;
        $livret->user_id = 4;
        $livret->modele_id = 1;
        $livret->save();
    }
}
