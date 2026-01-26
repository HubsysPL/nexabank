<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Nexa Bank - Onboarding</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            console.log('Dark mode script started.');
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            console.log('localStorage.theme:', storedTheme, 'prefers-color-scheme dark:', prefersDark);

            if (storedTheme === 'dark' || (!('theme' in localStorage) && prefersDark)) {
                document.documentElement.classList.add('dark');
                console.log('Dark mode condition met. Added dark class.');
            } else {
                document.documentElement.classList.remove('dark');
                console.log('Light mode condition met. Removed dark class.');
            }
            console.log('HTML classList after script:', document.documentElement.classList.value);

            // MutationObserver to track changes to the html element's classList
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        console.log('MutationObserver: html classList changed to:', document.documentElement.classList.value);
                    }
                }
            });

            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            console.log('MutationObserver started for html classList.');
        </script>
    </head>
    <body class="font-sans antialiased bg-background text-text-primary">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-background text-text-primary">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg bg-surface">
                <livewire:onboarding.create-account />
            </div>
        </div>

        @livewireScripts
    </body>
</html>
