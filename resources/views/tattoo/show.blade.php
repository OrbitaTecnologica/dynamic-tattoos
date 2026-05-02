<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    {{-- Prevent search engines from indexing individual QR pages --}}
    <meta name="robots" content="noindex, nofollow" />
    <meta name="theme-color" content="#030712" />
    <title>Tatuaje Dinámico</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-950 antialiased">

    {{--
        The TattooViewer Livewire component handles both Gallery (interactive
        carousel) and Video (iframe embed) content types reactively.
        For Link content, the controller already issued a 302 before reaching
        this view, so TattooViewer will never receive a Link content row.
    --}}
    <livewire:tattoo-viewer
        :content="$content"
        :short-code="$shortCode"
    />

    @livewireScripts
</body>
</html>
