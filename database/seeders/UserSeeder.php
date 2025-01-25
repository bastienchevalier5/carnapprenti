<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = new User;
        $admin->nom = "Responsable";
        $admin->prenom = "Pédagogique";
        $admin->email = "admin@admin.fr";
        $admin->password = Crypt::encryptString('admin');
        $admin->save();

        Bouncer::assign('admin')->to($admin);

        $qualite = new User;
        $qualite->nom = "Qualité";
        $qualite->prenom = "Qualité";
        $qualite->email = "qualite@qualite.fr";
        $qualite->password = Crypt::encryptString('qualite');
        $qualite->save();

        Bouncer::assign('qualite')->to($qualite);

        $referent = new User;
        $referent->nom = "Référent";
        $referent->prenom = "Référent";
        $referent->email = "referent@referent.fr";
        $referent->groupe_id = 1;
        $referent->password = Crypt::encryptString('referent');
        $referent->save();

        Bouncer::assign('referent')->to($referent);

        $apprenant = new User;
        $apprenant->nom = "Apprenant";
        $apprenant->prenom = "Apprenant";
        $apprenant->email = "apprenant@apprenant.fr";
        $apprenant->groupe_id = 1;
        $apprenant->password = Crypt::encryptString('apprenant');
        $apprenant->save();

        Bouncer::assign('apprenant')->to($apprenant);

        $tuteur = new User;
        $tuteur->nom = "Tuteur";
        $tuteur->prenom = "Tuteur";
        $tuteur->email = "tuteur@tuteur.fr";
        $tuteur->password = Crypt::encryptString('tuteur');
        $tuteur->apprenant_id = 4;
        $tuteur->save();

        Bouncer::assign('tuteur')->to($tuteur);
    }
}
