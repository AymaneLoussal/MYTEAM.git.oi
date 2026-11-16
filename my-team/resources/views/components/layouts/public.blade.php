<!DOCTYPE html><html lang="en" class="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>FTMBALL</title>@include('components.assets')<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .glass-panel {
            background: rgba(40, 42, 41, 0.7);
            backdrop-filter: blur(24px);
        }
    </style></head><body class="bg-background text-on-surface">@include('components.public-navigation'){{ $slot }}</body></html>