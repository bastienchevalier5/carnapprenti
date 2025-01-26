@extends('layouts.app')

@section('title', 'Compte-rendu')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <nav class="bg-light p-3 rounded">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('compte_rendu.show', ['id_livret' => $livret->id, 'periode' => now()->translatedFormat('F Y') . ' - ' . now()->addMonth()->translatedFormat('F Y') ]) }}">
                                {{ now()->translatedFormat('F Y') . ' - ' . now()->addMonth()->translatedFormat('F Y') }}
                            </a>

                        </li>
                        @foreach($periodes as $p)
                            @if($p->periode !== Carbon\Carbon::now()->translatedFormat('F Y') . ' - ' . Carbon\Carbon::now()->addMonth()->translatedFormat('F Y'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('compte_rendu.show', ['id_livret' => $livret->id, 'periode' => $p->periode]) }}">
                                        {{ $p->periode }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>

            <div class="col-md-9">
                <a href="{{ route('livret.index') }}" class="btn btn-primary mb-3">Retour</a>
                <div class="card">
                    <div class="card-header">
                        <h3>Compte-rendu pour la période : {{ $periode }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ $compte_rendu ? route('compte_rendus.update', $compte_rendu) : route('compte_rendus.store', ['id_livret' => $livret->id, 'periode' => $periode]) }}" method="POST">
                            @csrf
                            @if($compte_rendu)
                                @method('PUT') <!-- Si on met à jour, on utilise PUT -->
                            @endif

                            <!-- Activités professionnelles -->
                            <div class="form-group">
                                <label for="activites_pros">Activités professionnelles</label>
                                <textarea name="activites_pros" id="activites_pros" class="form-control" rows="5">{{ old('activites_pros', $compte_rendu->activites_pro ?? '') }}</textarea>
                            </div>

                            <!-- Observations de l'apprenti -->
                            <div class="form-group">
                                <label for="observations_apprenti">Observations de l'apprenti</label>
                                <textarea name="observations_apprenti" id="observations_apprenti" class="form-control" rows="5">{{ old('observations_apprenti', $compte_rendu->observations_apprenti ?? '') }}</textarea>
                            </div>

                            <!-- Observations du tuteur -->
                            @if (Auth::user()->isAn('tuteur') | Auth::user()->isAn('referent'))
                                <div class="form-group">
                                    <label for="observations_tuteur">Observations du tuteur</label>
                                    <textarea name="observations_tuteur" id="observations_tuteur" class="form-control" rows="5">{{ old('observations_tuteur', $compte_rendu->observations_tuteur ?? '') }}</textarea>
                                </div>
                            @endif


                            <!-- Observations du référent -->
                            @if (Auth::user()->isAn('referent'))
                                <div class="form-group">
                                    <label for="observations_referent">Observations du référent</label>
                                    <textarea name="observations_referent" id="observations_referent" class="form-control" rows="5">{{ old('observations_referent', $compte_rendu->observations_referent ?? '') }}</textarea>
                                </div>
                            @endif


                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary">{{ $compte_rendu ? 'Mettre à jour' : 'Enregistrer' }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
