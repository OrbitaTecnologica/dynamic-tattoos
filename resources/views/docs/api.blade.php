<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Dynamic Tattoos API Docs</title>
    <style>
        :root {
            color-scheme: light;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #f8fafc;
            color: #111827;
        }

        redoc {
            display: block;
            min-height: 100vh;
        }
    </style>
</head>
<body>
<redoc spec-url="{{ route('docs.api.spec') }}"></redoc>
<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
</body>
</html>
