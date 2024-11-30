<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivretRequest;
use App\Models\Livret;
use App\Models\Modele;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class LivretController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user(); // Récupérer l'utilisateur connecté

        if ($user->isAn('referent')) {
            // Récupérer les utilisateurs du même groupe
            $apprenants = User::where('groupe_id', $user->groupe_id)->pluck('id');

            // Récupérer les livrets des apprenants
            $livrets = Livret::whereIn('user_id', $apprenants)->get();
        } elseif (Auth::user()->isAn('tuteur')) {
            $id = Auth::user()->apprenant_id;
            $livrets = Livret::where('user_id',$id)->get();
        } elseif ($user->isAn('apprenant')) {
            $livrets = Livret::where('user_id', $user->id)->get();
        } else {
            $livrets = collect(); // Retourne une collection vide par défaut
        }

        return view('livrets.index', compact('livrets'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->isAn('referent')) {
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
            return redirect()->route('livret.index')->with('error','Vous ne pouvez pas créer un livret.');
        }

    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(LivretRequest $request)
    {
        $livret = new Livret;
        $livret->modele_id = $request['modele_id'];
        $livret->user_id =$request['apprenant_id'];
        $livret->save();
        return redirect()->route('livret.index')->with('success','Le livret a bien été créé.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Livret $livret)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livret $livret)
    {
        $user = Auth::user();
        if ($user->isAn('referent') && $livret->user->groupe_id === $user->groupe_id) {
            $modeles = Modele::all();

            $apprenants = User::where('groupe_id', $user->groupe_id)
                ->where('id', '!=', $user->id) // Exclure l'utilisateur connecté
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->id => $item->prenom . ' ' . $item->nom];
                });


            return view('livrets.form', compact('livret', 'modeles', 'apprenants'));
        } else {
            return redirect()->route('livret.index')->with('error','Vous ne pouvez pas modifier ce livret.');
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LivretRequest $request, Livret $livret)
    {
        $livret->modele_id = $request['modele_id'];
        $livret->user_id = $request['apprenant_id'];
        $livret->save();
        return redirect()->route('livret.index')->with('success','Le livret a bien été modifié.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livret $livret)
    {
        $livret->delete();
        return redirect()->route('livret.index')->with('success','Le livret a bien été supprimé.');
    }

    public function observations_form(Livret $livret) {
        return view('livrets.observations',compact('livret'));
    }
    public function observations(LivretRequest $request, Livret $livret) {

        $livret->observation_apprenti_global = $request['observation_apprenti_global'];
        $livret->observation_admin = $request['observation_admin'];
        $livret->save();
        return redirect()->route('livret.index')->with('success','Les observations ont bien été enregistrés.');
    }
}
