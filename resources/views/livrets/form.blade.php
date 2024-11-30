@extends('layouts.app')
@section('title', $livret->exists ? 'Modification du livret de '.$livret->user->prenom." ".$livret->user->nom : "Ajout d'un livret")
@section('content')
    <x-form method="{{ $livret->exists ? 'PUT' : 'POST' }}"
            action="{{ $livret->exists ? route('livret.update', $livret->id) : route('livret.store') }}">
        <h1>{{ $livret->exists ? "Modification du livret de ".$livret->user->prenom." ".$livret->user->nom : "Ajout d'un livret" }}</h1>
        <x-select label="Modèle du livret :"
                  name="modele_id"
                  :options="$modeles->pluck('nom', 'id') ?? []"
                  :selected="$livret->modele_id" />
        <x-select label="Apprenant :"
                  name="apprenant_id"
                  :options="$apprenants"
                  :selected="$livret->user_id" />
    </x-form>
@endsection
