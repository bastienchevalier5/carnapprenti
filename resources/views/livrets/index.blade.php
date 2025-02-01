@extends('layouts.app')
@section('title', Auth::user()->isAn('apprenant') ? 'Mon livret' : 'Liste des livrets')
@section('content')

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<h1 class="m-5">{{Auth::user()->isAn('apprenant') ? 'Mon livret' : 'Liste des livrets'}}</h1>
@if (Auth::user()->isAn('referent'))
    <a class="btn btn-secondary mb-5" href="{{route('livret.create')}}">Ajouter un livret</a>
@endif
<table class="table table-bordered table-striped table-hover text-center mx-auto w-75">
    <tr>
        <td>Modèle</td>
        @if (!Auth::user()->isAn('apprenant'))
            <td>Nom</td>
            <td>Prénom</td>
        @endif
        <td>Compte-Rendu</td>
        <td>Observations Globales</td>
        <td>PDF</td>
        @if (Auth::user()->isAn('referent'))
            <td>Actions</td>
        @endif
    </tr>
    @foreach ($livrets as $livret)
    <tr>
        <td>{{$livret->modele->nom}}</td>
        @if (!Auth::user()->isAn('apprenant'))
            <td>{{$livret->user->nom}}</td>
            <td>{{$livret->user->prenom}}</td>
        @endif
        <td><a class="btn btn-secondary" href="{{route('compte_rendu.show',$livret->id)}}">Compte-Rendu</a></td>
        <td><a class="btn btn-secondary" href="{{route('livret.observations_form',$livret->id)}}">Observations globales</a></td>
        <td><a href="{{ route('livrets.pdf', $livret) }}" class="btn btn-primary">Télécharger le PDF</a>
        </td>
        @if (Auth::user()->isAn('referent'))
            <td><a class="btn btn-secondary m-1" href="{{route('livret.edit',$livret->id)}}">Modifier</a>
                <form method="POST" action="{{route('livret.destroy',$livret->id)}}">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livret?')" class="btn btn-danger m-1" type="submit">Supprimer</button>
                </form>
            </td>
        @endif
    </tr>
    @endforeach
</table>
@endsection
