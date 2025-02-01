@extends('layouts.app')
@section('title', 'Observations globales')
@section('content')
<a class="btn btn-secondary m-3" href="{{route('livret.index')}}">← Retour</a>

<h1>Observations globales</h1>
<x-form method="PUT" action="{{route('livret.observations',$livret->id)}}">
    <x-textarea label="Observations de l'apprenti :" name="observation_apprenti_global" :value="$livret->observation_apprenti_global" />
</x-form>
@endsection
