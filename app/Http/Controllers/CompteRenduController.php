<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteRenduRequest;
use App\Models\CompteRendu;
use App\Models\Notification;
use App\Models\Livret;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
class CompteRenduController extends Controller
{
    /**
     * Summary of show
     * @param mixed $id_livret
     * @param mixed $periode
     * @return View
     */
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

    /**
     * Summary of store
     * @param CompteRenduRequest $request
     * @param mixed $id_livret
     * @param mixed $periode
     * @return RedirectResponse
     */
    public function store(CompteRenduRequest $request, $id_livret, $periode)
    {
        // Création d'un nouveau compte rendu
        $compteRendu = new CompteRendu();

        // Ensure that $id_livret is an integer or fallback to null if invalid
        $compteRendu->livret_id = is_numeric($id_livret) ? (int) $id_livret : 0; // Default to 0 if invalid

        // Ensure that $periode is a string or fallback to an empty string if invalid
        $compteRendu->periode = is_string($periode) ? $periode : '';

        // Ensure that activites_pro is a string or fallback to an empty string if invalid
        $activitesPro = $request->input('activites_pros');
        $compteRendu->activites_pro = is_string($activitesPro) ? $activitesPro : '';

        // Ensure that observations_apprenti is a string or fallback to an empty string if invalid
        $observationsApprenti = $request->input('observations_apprenti');
        $compteRendu->observations_apprenti = is_string($observationsApprenti) ? $observationsApprenti : '';

        // Handle the tuteur's observations if the user is a tuteur
        // First, assign the user to the variable
        $user = Auth::user();

        // Then, check if the user is a tuteur
        if ($user && $user->isAn('tuteur')) {
            $observationsTuteur = $request->input('observations_tuteur');
            $compteRendu->observations_tuteur = is_string($observationsTuteur) ? $observationsTuteur : '';
        }


        // Handle the referent's observations if the user is a referent
        if ($user && $user->isAn('referent')) {
            $observationsReferent = $request->input('observations_referent');
            $compteRendu->observations_referent = is_string($observationsReferent) ? $observationsReferent : '';
        }

        // Save the compte rendu
        $compteRendu->save();

        return redirect()->route('livret.index')->with('success', 'Le compte-rendu a été ajouté avec succès !');
    }

    /**
     * Summary of update
     * @param CompteRenduRequest $request
     * @param CompteRendu $compteRendu
     * @return RedirectResponse
     */
    public function update(CompteRenduRequest $request, CompteRendu $compteRendu)
    {
        // Ensure that activites_pro is a valid string or fallback to an empty string if null
        $activitesPro = $request->input('activites_pros');
        $compteRendu->activites_pro = is_string($activitesPro) ? $activitesPro : '';

        // Ensure that observations_apprenti is a valid string or fallback to an empty string if null
        $observationsApprenti = $request->input('observations_apprenti');
        $compteRendu->observations_apprenti = is_string($observationsApprenti) ? $observationsApprenti : '';

        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->isAn('tuteur')) {
            // Ensure that observations_tuteur is a valid string or fallback to an empty string if null
            $observationsTuteur = $request->input('observations_tuteur');
            $compteRendu->observations_tuteur = is_string($observationsTuteur) ? $observationsTuteur : '';
        }

        if ($user && $user->isAn('referent')) {
            // Ensure that observations_referent is a valid string or fallback to an empty string if null
            $observationsReferent = $request->input('observations_referent');
            $compteRendu->observations_referent = is_string($observationsReferent) ? $observationsReferent : '';
        }

        $compteRendu->save();

        return redirect()->route('livret.index')->with('success', 'Le compte-rendu a bien été modifié.');
    }




}
