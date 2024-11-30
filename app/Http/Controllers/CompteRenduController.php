<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteRenduRequest;
use App\Models\CompteRendu;
use App\Models\Notification;
use App\Models\Livret;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompteRenduController extends Controller
{
    public function show($id_livret, $periode = null)
    {
        // Récupérer le livret et la période, par défaut utiliser la période actuelle
        $livret = Livret::findOrFail($id_livret);

        // Définir la période si elle n'est pas fournie
        if (is_null($periode)) {
            $periode = Carbon::now()->format('F Y') . ' - ' . Carbon::now()->addMonth()->format('F Y');
        }

        // Récupérer le compte rendu pour cette période
        $compte_rendu = CompteRendu::where('livret_id', $id_livret)->where('periode', $periode)->first();

        // Récupérer les périodes existantes pour ce livret
        $periodes = CompteRendu::where('livret_id', $id_livret)->orderBy('id', 'desc')->get();

        return view('compte_rendu.show', compact('livret', 'compte_rendu', 'periode', 'periodes'));
    }

    public function store(CompteRenduRequest $request, $id_livret, $periode)
    {
        // Création d'un nouveau compte rendu
        $compteRendu = new CompteRendu();
        $compteRendu->livret_id = $id_livret;
        $compteRendu->periode = $periode;
        $compteRendu->activites_pro = $request->input('activites_pros');
        $compteRendu->observations_apprenti = $request->input('observations_apprenti');

        if (Auth::user()->isAn('tuteur')) {
            $compteRendu->observations_tuteur = $request->input('observations_tuteur');
        }

        if (Auth::user()->isAn('referent')) {
            $compteRendu->observations_referent = $request->input('observations_referent');
        }

        $compteRendu->save();

        return redirect()->route('livret.index')->with('success', 'Le compte-rendu a été ajouté avec succès !');
    }

    // Pour mettre à jour un compte rendu existant
    public function update(CompteRenduRequest $request, CompteRendu $compteRendu)
    {
        // Mise à jour des champs
        $compteRendu->activites_pro = $request->input('activites_pros');
        $compteRendu->observations_apprenti = $request->input('observations_apprenti');

        if (Auth::user()->isAn('tuteur')) {
            $compteRendu->observations_tuteur = $request->input('observations_tuteur');
        }

        if (Auth::user()->isAn('referent')) {
            $compteRendu->observations_referent = $request->input('observations_referent');
        }

        $compteRendu->save();

        return redirect()->route('livret.index')->with('success', 'Le compte-rendu a bien été modifié.');
    }

}
