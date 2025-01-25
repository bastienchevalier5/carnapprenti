<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivretRequest;
use App\Models\Livret;
use App\Models\Modele;
use App\Models\User;
use App\Services\PdfGenerator;
use Auth;
use Illuminate\Http\Request;

class LivretController extends Controller
{
    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user(); // Récupérer l'utilisateur connecté

        // Vérifier si l'utilisateur est bien authentifié et une instance de User
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user instanceof User) {
            if ($user->isAn('referent')) {
                // Récupérer les utilisateurs du même groupe
                $apprenants = User::where('groupe_id', $user->groupe_id)->pluck('id');

                // Récupérer les livrets des apprenants
                $livrets = Livret::whereIn('user_id', $apprenants)->get();
            } elseif ($user->isAn('tuteur')) {
                // Vérifier que 'apprenant_id' existe
                $id = $user->apprenant_id;
                $livrets = Livret::where('user_id', $id)->get();
            } elseif ($user->isAn('apprenant')) {
                $livrets = Livret::where('user_id', $user->id)->get();
            } else {
                $livrets = collect(); // Retourne une collection vide par défaut
            }

            return view('livrets.index', compact('livrets'));
        } else {
            return redirect()->route('login')->with('error', 'Utilisateur non valide.');
        }
    }



    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
{
    $user = Auth::user();

    if (!$user) {
        // Si l'utilisateur n'est pas authentifié, rediriger vers la page de connexion
        return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }

    // Vérifier que l'utilisateur est bien une instance de User et a un groupe
    if ($user instanceof User && $user->isAn('referent') && $user->groupe_id) {
        $livret = new Livret;
        $modeles = Modele::all();

        $apprenants = User::where('groupe_id', $user->groupe_id)
            ->where('id', '!=', $user->id) // Exclure l'utilisateur connecté
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->prenom . ' ' . $item->nom];
            });

        return view('livrets.form', compact('livret', 'modeles', 'apprenants'));
    } else {
        return redirect()->route('livret.index')->with('error', 'Vous ne pouvez pas créer un livret.');
    }
}







    /**
     * Summary of store
     * @param \App\Http\Requests\LivretRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LivretRequest $request)
{
    // Validate and sanitize the input values
    $modeleId = filter_var($request->input('modele_id'), FILTER_VALIDATE_INT);
    $apprenantId = filter_var($request->input('apprenant_id'), FILTER_VALIDATE_INT);

    // Check if the values are valid integers
    if ($modeleId === false || $apprenantId === false) {
        return redirect()->back()->with('error', 'Les identifiants fournis ne sont pas valides.')->withInput();
    }

    $livret = new Livret;
    $livret->modele_id = $modeleId;
    $livret->user_id = $apprenantId;
    $livret->save();

    return redirect()->route('livret.index')->with('success', 'Le livret a bien été créé.');
}



    /**
     * Summary of edit
     * @param \App\Models\Livret $livret
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Livret $livret)
    {
        // Vérifier si l'utilisateur est authentifié
        $user = auth()->user();

        if (!$user) {
            // Si l'utilisateur n'est pas authentifié, rediriger vers la page de connexion
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérifier les permissions de l'utilisateur
        if ($user->isAn('referent') && $livret->user && $livret->user->groupe_id === $user->groupe_id) {
            $modeles = Modele::all();

            $apprenants = User::where('groupe_id', $user->groupe_id)
                ->where('id', '!=', $user->id) // Exclure l'utilisateur connecté
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->id => $item->prenom . ' ' . $item->nom];
                });

            // Retourner la vue avec les données
            return view('livrets.form', compact('livret', 'modeles', 'apprenants'));
        } else {
            // Si l'utilisateur n'a pas les bonnes permissions
            return redirect()->route('livret.index')->with('error', 'Vous ne pouvez pas modifier ce livret.');
        }
    }






    /**
     * Summary of update
     * @param \App\Http\Requests\LivretRequest $request
     * @param \App\Models\Livret $livret
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(LivretRequest $request, Livret $livret)
    {
        // Récupérer les valeurs
        $modeleId = $request->input('modele_id');
        $apprenantId = $request->input('apprenant_id');

        // Vérifier si les valeurs sont bien des chaînes ou des entiers et les convertir en entiers
        if (is_numeric($modeleId)) {
            $modeleId = intval($modeleId);
        } else {
            $modeleId = 0; // ou autre valeur par défaut ou gestion d'erreur
        }

        if (is_numeric($apprenantId)) {
            $apprenantId = intval($apprenantId);
        } else {
            $apprenantId = 0; // ou autre valeur par défaut ou gestion d'erreur
        }

        // Assurez-vous que les IDs sont valides
        if ($modeleId <= 0 || $apprenantId <= 0) {
            return redirect()->route('livret.index')->with('error', 'Les identifiants sont invalides.');
        }

        // Assignez les valeurs au modèle
        $livret->modele_id = $modeleId;
        $livret->user_id = $apprenantId;

        // Sauvegardez les modifications
        $livret->save();

        // Retourne à la liste avec un message de succès
        return redirect()->route('livret.index')->with('success', 'Le livret a bien été modifié.');
    }




    /**
     * Summary of destroy
     * @param \App\Models\Livret $livret
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Livret $livret)
    {
        $livret->delete();
        return redirect()->route('livret.index')->with('success','Le livret a bien été supprimé.');
    }

    /**
     * Summary of observations_form
     * @param \App\Models\Livret $livret
     * @return \Illuminate\Contracts\View\View
     */
    public function observations_form(Livret $livret) {
        return view('livrets.observations',compact('livret'));
    }

    /**
     * Summary of observations
     * @param \App\Http\Requests\LivretRequest $request
     * @param \App\Models\Livret $livret
     * @return \Illuminate\Http\RedirectResponse
     */
    public function observations(LivretRequest $request, Livret $livret)
    {
        // Vérifie si les valeurs sont valides avant de les convertir
        $livret->observation_apprenti_global = is_scalar($request->input('observation_apprenti_global'))
            ? strval($request->input('observation_apprenti_global'))
            : null;

        $livret->observation_admin = is_scalar($request->input('observation_admin'))
            ? strval($request->input('observation_admin'))
            : null;

        // Sauvegarde les modifications
        $livret->save();

        // Retourne la réponse après avoir enregistré les observations
        return redirect()->route('livret.index')->with('success', 'Les observations ont bien été enregistrées.');
    }



    /**
     * Summary of generatePdf
     * @param \App\Models\Livret $livret
     * @return mixed|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generatePdf(Livret $livret)
{
    // Vérifiez si le modèle associé existe
    $modele = $livret->modele;

    if (!$modele) {
        // Si le modèle est introuvable, rediriger avec un message d'erreur
        return redirect()->route('livret.index')->with('error', 'Le modèle associé est introuvable.');
    }

    // Créez une instance de PdfGenerator avec le livret et le modèle
    $pdfGenerator = new PdfGenerator($livret, $modele);

    // Générez le PDF
    $pdfGenerator->generate();

    // Définir le chemin du fichier PDF généré
    $filePath = storage_path('app/public/livrets/livret-' . $livret->id . '.pdf');

    // Vérifier si le fichier a bien été généré
    if (!file_exists($filePath)) {
        // Si le fichier n'existe pas, rediriger avec un message d'erreur
        return redirect()->route('livret.index')->with('error', 'Le fichier PDF n\'a pas pu être généré.');
    }

    // Si le fichier existe, le télécharger
    return response()->download($filePath);
}


}
