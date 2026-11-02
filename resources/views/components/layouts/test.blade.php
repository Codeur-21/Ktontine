<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/ktontine-brand.css') }}">
</head>

@livewireStyles()

<body class="kt-legacy"
    class="min-h-screen bg-gradient-to-br from-orange-500 via-purple-500 to-pink-500 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10">

            <!-- En-tête -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Bon retour !</h1>
                <p class="text-gray-500 mt-2 text-sm">Connectez-vous à votre compte</p>
            </div>

            {{ $slot }}

            <!-- Lien inscription -->
            <p class="text-center text-sm text-gray-600 mt-6">
                Pas encore de compte ?
                <a href="#" class="text-orange-600 font-medium hover:text-orange-800 hover:underline">
                    Inscrivez-vous
                </a>
            </p>

        </div>
    </div>

    @livewireScripts()

</body>

</html>
