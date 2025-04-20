<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="PwanChampion - Premium real estate platform for land and plot purchases with verified titles">

        <title>{{ $title ?? 'PwanChampion - Premium Real Estate' }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

        <!-- Phosphor Icons -->
        <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'primary': '#010069',
                            'primary-light': '#a285a0',
                            'primary-dark': '#0d2541',
                            'secondary': '#f50506',
                            'neutral-cream': '#f8f7f2',
                        },
                        fontFamily: {
                            sans: ['DM Sans', 'sans-serif'],
                        },
                    }
                }
            }
        </script>

        <!-- Custom Styles -->
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f8f7f2;
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb {
                background: #c1e1c5;
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #4b8b5a;
            }
        </style>

        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <!-- Navigation/Header -->
        <livewire:components.header />
        {{ $slot }}
       
        @livewireScripts
    </body>
</html>
