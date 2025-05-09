<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Psy REPL') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

        <!-- Scripts -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div x-data class="h-screen min-w-screen w-full bg-white flex flex-col">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white">
                    <div class="max-w-full py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main
                class="flex-grow flex flex-col overflow-hidden"
                :class="$store.editor.minimalMode ? 'h-screen' : 'h-[100vh-40px]'"
            >
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScriptConfig

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('editor', {
                    minimalMode: false,

                    toggleMinimalMode() {
                        this.minimalMode = ! this.minimalMode;
                    }
                })
            });
        </script>
    </body>
</html>
