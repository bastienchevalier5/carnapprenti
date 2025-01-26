@extends('layouts.app')
@section('title', 'Observations globales')
@section('content')
<h1>Observations globales</h1>
<x-form method="PUT" action="{{route('livret.observations',$livret->id)}}">
    <x-textarea label="Observations de l'apprenti :" name="observation_apprenti_global" :value="$livret->observation_apprenti_global" />
    <x-textarea label="Observations de l'admin :" name="observation_admin" :value="$livret->observation_admin" />
</x-form>
@endsection
