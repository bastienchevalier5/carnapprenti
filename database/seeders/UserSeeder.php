<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $referent = new User;
        $referent->nom = "Référent";
        $referent->prenom = "Référent";
        $referent->email = "referent@referent.fr";
        $referent->groupe_id = 1;
        $referent->password = Hash::make('referent');
        $referent->save();

        Bouncer::assign('referent')->to($referent);

        $apprenant = new User;
        $apprenant->nom = "Apprenant";
        $apprenant->prenom = "Apprenant";
        $apprenant->email = "apprenant@apprenant.fr";
        $apprenant->groupe_id = 1;
        $apprenant->password = Hash::make('apprenant');
        $apprenant->save();

        Bouncer::assign('apprenant')->to($apprenant);

        $tuteur = new User;
        $tuteur->nom = "Tuteur";
        $tuteur->prenom = "Tuteur";
        $tuteur->email = "tuteur@tuteur.fr";
        $tuteur->password = Hash::make('tuteur');
        $tuteur->apprenant_id = 2;
        $tuteur->save();

        Bouncer::assign('tuteur')->to($tuteur);
    }
}
