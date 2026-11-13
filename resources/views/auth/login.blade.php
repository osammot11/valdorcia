<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - {{ config('event.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/images/valdorcia-gravel/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/controls.css?v=1">
    <link rel="stylesheet" href="/css/admin.css?v=5">
</head>
<body class="login">

    <div class="card">
        <header>
            <img src="/images/valdorcia-gravel/logo.svg" alt="{{ config('event.name') }}" width="64" height="64">
            <h1>Area riservata</h1>
            <p class="muted">Inserisci le tue credenziali per accedere</p>
        </header>

        @if($errors->any())
            <div class="alert alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="field">
                <label class="label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label class="label" for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn--block">Accedi</button>
        </form>

        <p class="back"><a href="/">&larr; Torna al sito web</a></p>
    </div>

</body>
</html>
