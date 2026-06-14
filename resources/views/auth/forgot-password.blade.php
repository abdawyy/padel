<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 420px; margin: 3rem auto; padding: 0 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-weight: 600; }
        input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
        button { background: #111827; color: #fff; border: 0; padding: 0.6rem 1rem; cursor: pointer; }
        .status { color: #065f46; margin-bottom: 1rem; }
        .error { color: #b91c1c; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Forgot Password</h1>

    @if (session('status'))
        <p class="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
