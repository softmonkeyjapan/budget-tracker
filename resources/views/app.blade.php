<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script>
            (function () {
                var theme;
                try {
                    theme = localStorage.getItem('budget-tracker-theme');
                } catch (e) {
                    theme = null;
                }
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.dataset.theme = theme;
            })();
        </script>

        <script>
            (function () {
                var privacy;
                try {
                    privacy = localStorage.getItem('budget-tracker-privacy');
                } catch (e) {
                    privacy = null;
                }
                if (privacy === 'hidden') {
                    document.documentElement.dataset.privacy = 'hidden';
                }
            })();
        </script>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito-sans:400,500,600,700,800|poppins:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @php
            $componentSegments = explode('/', $page['component']);
            $componentDomain = array_shift($componentSegments);
            $componentPath = implode('/', $componentSegments);
        @endphp
        @vite(['resources/js/app.js', "resources/js/Domains/{$componentDomain}/Pages/{$componentPath}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
