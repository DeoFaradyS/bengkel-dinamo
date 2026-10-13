<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Servis</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 28rem; margin: 2rem auto; padding: 0 1rem; }
        label { display: block; margin-top: 1rem; }
        input, textarea { display: block; width: 100%; box-sizing: border-box; padding: .5rem; }
        small { color: #b91c1c; display: block; }
        button { margin-top: 1rem; padding: .6rem 1rem; }
    </style>
    @livewireStyles
</head>
<body>
    <h1>Booking Servis</h1>
    {{ $slot }}
    @livewireScripts
</body>
</html>