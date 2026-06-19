<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
            }

            50% {
                box-shadow: 0 0 30px rgba(255, 255, 255, 0.15);
            }
        }

        .animate-fade {
            animation: fadeInOut 3s ease-in-out infinite;
        }

        .animate-glow {
            animation: glow 4s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-screen bg-[#f4f4f4] flex items-center justify-center p-6 overflow-hidden">

    <!-- Background Blur -->
    <div class="absolute w-72 h-72 bg-black/5 rounded-full blur-3xl top-10 left-10 animate-pulse">
    </div>

    <div class="absolute w-72 h-72 bg-blue-500/10 rounded-full blur-3xl bottom-10 right-10 animate-pulse">
    </div>

    <main class="relative w-full max-w-3xl  backdrop-blur-md">
        <div class="space-y-8 text-center">
            <div class="space-y-3 animate-fade">
                <img class="object-scale-down h-32 mx-auto mb-8"
                    src="https://smb.telkomuniversity.ac.id/wp-content/uploads/2023/03/Logo-Utama-Telkom-University.png" />
                <h1 class="text-3xl md:text-5xl font-bold tracking-tight">
                    Rent Contract Service
                </h1>

                <p class="text-zinc-800 text-sm md:text-base leading-relaxed">
                    Service ini dibuat untuk memenuhi tugas mata kuliah
                    <span class="font-semibold ">
                        Integrasi Aplikasi Enterprise
                    </span>
                    di Telkom University
                </p>
                <p>
                    Buka dokumentasi di
                    <span class="text-zinc-800 text-sm md:text-base leading-relaxed">
                        <a href="https://github.com/EsGoreng/102022400056_rent-contract" target="_blank"
                            class="underline">
                            https://github.com/EsGoreng/102022400056_rent-contract
                        </a>
                    </span>
                </p>
            </div>
            <div class="space-y-2 animate-fade" style="animation-delay: 1s;">
                <p class="text-zinc-500 text-xs uppercase tracking-[0.2em]">
                    Dibuat Oleh
                </p>

                <h2 class="text-sm font-semibold text-black">
                    Itsna Akhdan Fadhil (102022400056)
                </h2>
            </div>

        </div>

    </main>

</body>

</html>
