@props(['title' => 'FTMBALL'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @include('components.assets')
</head>
<body class="min-h-screen bg-background text-on-surface">
    @include('components.navigation')

    <main id="main-content" tabindex="-1" class="mx-auto max-w-6xl px-4 py-10">
        @include('components.feedback')
        {{ $slot }}
    </main>
</body>
</html>
