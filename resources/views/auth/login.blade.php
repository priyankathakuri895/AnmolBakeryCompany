<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login — Anmol</title>
    @vite(['resources/css/admin.css'])
</head>
<body class="login-body">

<div class="login-card">

    <div class="login-brand">
        <span class="brand-main">ANMOL</span>
        <span class="brand-sub">ADMIN PANEL</span>
    </div>

    <p class="login-title">Owner access only</p>

    @if (session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    @error('email')
        <div class="alert error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password">
        </div>

        <div class="checkbox-row" style="margin-top:14px">
            <input id="remember" type="checkbox" name="remember" value="1">
            <label for="remember">Keep me signed in</label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </div>
    </form>

    <p class="login-back"><a href="{{ route('home') }}">← Back to website</a></p>

</div>

</body>
</html>
