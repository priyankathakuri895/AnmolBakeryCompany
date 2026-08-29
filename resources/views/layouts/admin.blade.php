<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Anmol Admin</title>
    @vite(['resources/css/admin.css'])
</head>
<body class="admin-body">

<div class="admin-shell">

    @include('admin.partials.sidebar')

    <div class="admin-main">

        <header class="admin-topbar">
            <div>
                <h1>@yield('heading', 'Dashboard')</h1>
                <p class="subtitle">@yield('subtitle', '')</p>
            </div>

            <div class="topbar-right">
                @yield('topbar')

                <span class="topbar-user">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Log out</button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            @include('admin.partials.flash')
            @yield('content')
        </main>

    </div>
</div>

</body>
</html>
