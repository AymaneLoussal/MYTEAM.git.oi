@if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')) || app()->environment('production'))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    {{-- Local development fallback; production requires npm install && npm run build. --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>tailwind.config = {{ Illuminate\Support\Js::from(json_decode(file_get_contents(resource_path('theme.json')), true)) }};</script>
@endif
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;family=Inter:wght@100..900&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
