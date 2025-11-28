<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'E-commerce Template')</title>

    <!-- Google Font: Inter (REMOVED EXTERNAL LINK) -->
    
    <!-- Tailwind CSS CDN - USED FOR DEVELOPMENT/DEMO ONLY -->
    <script id="tailwind-cdn" src="https://cdn.tailwindcss.com"></script>
    <script>
        // NOTE: For Production, remove the CDN above and compile Tailwind using PostCSS or CLI.
        tailwind.config = {
            // CRITICAL: Enable dark mode based on parent class
            darkMode: 'class', 
            theme: {
                extend: {
                    fontFamily: {
                        // Will use system sans-serif if internet is down
                        sans: ['Inter', 'sans-serif'], 
                    },
                    colors: {
                        'primary': '#057A55', // A nice green for e-commerce primary color
                        'secondary': '#F9F9F9',
                    }
                }
            }
        }
    </script>
    
    <!-- FALLBACK STYLES for when CDN FAILS (Minimal embedded CSS) -->
    <style>
        body { font-family: sans-serif; }
        .no-internet-warning { 
            position: fixed; top: 0; left: 0; width: 100%; padding: 10px; 
            background: #f8d7da; color: #721c24; border-bottom: 1px solid #f5c6cb;
            z-index: 10000; text-align: center; font-size: 14px;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 antialiased">
    
    <!-- Dark Mode Initializer Script -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // INTERNET FAILURE DETECTOR (Checks if Tailwind CDN loaded)
        document.addEventListener('DOMContentLoaded', function() {
            const tailwindScript = document.getElementById('tailwind-cdn');
            
            // Check if the script exists and if it loaded successfully (hacky check for CDN failure)
            if (tailwindScript && !window.tailwind) {
                const warning = document.createElement('div');
                warning.className = 'no-internet-warning';
                warning.innerHTML = '⚠️ **WARNING:** Styles failed to load (No Internet/CDN Error). Display may be broken.';
                document.body.prepend(warning);
            }
        });
    </script>

    <!-- Header Component -->
    @include('components.header')

    <!-- Main Content Section -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer Component -->
    @include('components.footer')

    @stack('scripts')
</body>
</html>