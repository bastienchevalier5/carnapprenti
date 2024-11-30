@props(['method' => 'POST', 'action' => '#'])

<form method="{{ strtolower($method) === 'get' ? 'GET' : 'POST' }}" action="{{ $action }}">
    @csrf

    @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    {{ $slot }}

    <button class="btn btn-secondary" type="submit">Envoyer</button>
</form>
