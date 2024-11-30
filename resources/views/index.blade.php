@extends('layouts.app')
@section('title','Bienvenue')
@section('content')
<h1 class="m-5">Bienvenue {{Auth::user()->prenom}} {{Auth::user()->nom}}</h1>

<a class="btn btn-secondary" href="{{route('livret.index')}}">{{Auth::user()->isAn('apprenant') ? "Mon livret" : "Liste des livrets"}}</a>

@endsection
