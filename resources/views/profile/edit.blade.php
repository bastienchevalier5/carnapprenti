@extends('layouts.app')

@section('title','Mon profil')

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
    <div class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-semibold text-center text-gray-800 mb-5">{{ __('Mon Profil') }}</h1>

        <div class="max-w-4xl mx-auto">
            <!-- Affichage des informations du profil -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <h3 class="text-xl font-medium text-gray-900">Informations du Profil</h3>
                <div class="mt-4 text-gray-700">
                    <p><strong>Nom :</strong> {{ $user->nom }}</p>
                    <p><strong>Prénom :</strong> {{ $user->prenom }}</p>
                    <p><strong>Email :</strong> {{ $user->email }}</p>
                </div>
            </div>

            <!-- Formulaire de mise à jour des informations -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-medium text-gray-900 mb-6">Modifier mes informations</h3>
                <x-form method="PUT" action="{{ route('profile.update') }}">

                    @if (!Auth::user()->isAn('apprenant'))
                        <x-input label="Nom :" name="nom" :value="$user->nom" />
                        @error('nom')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror

                        <x-input label="Prénom :" name="prenom" :value="$user->prenom" />
                        @error('prenom')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror

                        <x-input label="Email :" type="email" name="email" :value="$user->email" />
                        @error('email')

                        @enderror
                    @endif
                    <x-input label="Nouveau mot de passe :" type="password" name="mdp" />
                    @error('mdp')

                    @enderror
                    <x-input label="Confirmation du mot de passe :" type="password" name="mdpConfirm" />

                </x-form>
            </div>

            <!-- Bouton de déconnexion -->
            <div class="bg-white shadow-lg rounded-lg p-6 mt-6">
                <div class="text-center">
                    <a href="{{ route('logout') }}" class="btn btn-danger">
                        <span class="font-medium">{{ __('Se déconnecter') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
