<div style="color:#3c3feb;">
    <div style="text-align:center;">
        <h1 style="font-size:40;">{{ $modele->firstPage?->titre ?? 'Livret d\'apprentissage' }}</h1>
        <h2 style="font-size:25;">{{ $modele->groupe->nom }}</h2>
    </div>
    <div>
        <h3>Nom : {{ $livret->user->nom }}</h3>
        <h3>Prénom : {{ $livret->user->prenom }}</h3>
        <h3>Site : {{ $modele->site->nom }}</h3>
    </div>
</div>
