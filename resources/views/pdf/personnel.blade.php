<!-- resources/views/pdf/personnel.blade.php -->

<style>
    .section-title {
        text-align: center;
        margin-bottom: 15px;
    }
    .personnel-info {
        margin-bottom: 15px;
    }
    .bold {
        font-weight: bold;
    }
    a {
        color: #0000FF;
        text-decoration: underline;
    }
</style>

<h1 class="section-title">INFORMATIONS</h1>

@foreach ($personnels as $personnel)
    <div class="personnel-info">
        <p><span class="bold">{{ $personnel->prenom }} {{ $personnel->nom }}</span> - {{ $personnel->description }}</p>
        <p>{{ $personnel->telephone }} - <a href="{{ $personnel->mail }}">{{ $personnel->mail }}</a></p>
    </div>
@endforeach

<h3>PLANNINGS, NOTES ET REFERENTIELS</h3>
<p><span class="bold">Net-Yparéo</span> : <a href="https://formations.mayenne.cci.fr">https://formations.mayenne.cci.fr</a></p>
<p>Portail web dédié au parcours de l'étudiant (accès planning, notes, référentiels, etc.)</p>
<p>Identifiants : communiqués par mail</p>
