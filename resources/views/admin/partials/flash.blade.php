@if (session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif

@if ($errors->any() && ! $errors->has('email'))
    <div class="alert error">
        Please fix the following:
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
