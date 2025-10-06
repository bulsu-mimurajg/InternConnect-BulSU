<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Enhanced script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';
                
                // Function to apply dark mode
                function applyDarkMode(isDark) {
                    document.documentElement.classList.toggle('dark', isDark);
                }
                
                // Function to detect system preference
                function getSystemPreference() {
                    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
                
                // Apply theme immediately based on appearance setting
                if (appearance === 'dark') {
                    applyDarkMode(true);
                } else if (appearance === 'light') {
                    applyDarkMode(false);
                } else if (appearance === 'system') {
                    // For system preference, check immediately and also listen for changes
                    applyDarkMode(getSystemPreference());
                    
                    // Listen for system theme changes
                    if (window.matchMedia) {
                        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                            applyDarkMode(e.matches);
                        });
                    }
                }
                
                // Also check localStorage for any saved preference
                try {
                    const savedAppearance = localStorage.getItem('appearance');
                    if (savedAppearance === 'dark') {
                        applyDarkMode(true);
                    } else if (savedAppearance === 'light') {
                        applyDarkMode(false);
                    } else if (savedAppearance === 'system') {
                        applyDarkMode(getSystemPreference());
                    }
                } catch (e) {
                    // Ignore localStorage errors
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color and ensure CSS variables are applied immediately --}}
        <style>
            html {
                background-color: oklch(1 0 0);
                --background: oklch(1 0 0);
                --foreground: oklch(0.145 0 0);
                --card: oklch(1 0 0);
                --card-foreground: oklch(0.145 0 0);
                --muted-foreground: oklch(0.556 0 0);
                --border: oklch(0.922 0 0);
                --destructive: oklch(0.577 0.245 27.325);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
                --background: oklch(0.145 0 0);
                --foreground: oklch(0.985 0 0);
                --card: oklch(0.145 0 0);
                --card-foreground: oklch(0.985 0 0);
                --muted-foreground: oklch(0.708 0 0);
                --border: oklch(0.269 0 0);
                --destructive: oklch(0.396 0.141 25.723);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.svg">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
