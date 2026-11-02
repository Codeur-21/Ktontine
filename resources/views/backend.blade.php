<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/ktontine-brand.css') }}">
</head>

<body class="kt-legacy bg-gray-100 min-h-screen">

    <div class="flex h-screen overflow-hidden">

        <!-- ===== SIDEBAR ===== -->
        <aside class="w-64 bg-orange-700 text-white flex-shrink-0 hidden md:flex flex-col">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 h-16 border-b border-orange-600">
                <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="font-bold text-lg">Ktontine</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-orange-800 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Tableau de bord
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Statistiques
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Utilisateurs
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Rapports
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Paramètres
                </a>
            </nav>

            <!-- Profil bas -->
            <div class="p-4 border-t border-orange-600">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-pink-500 flex items-center justify-center font-semibold">
                        JD
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">Jean Dupont</p>
                        <p class="text-xs text-orange-200 truncate">jean@exemple.com</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ===== CONTENU PRINCIPAL ===== -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Tableau de bord</h2>
                    <p class="text-xs text-gray-500">Bienvenue, voici un aperçu de votre activité</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="relative p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <button class="flex items-center gap-2 p-1 pr-3 rounded-lg hover:bg-gray-100 transition">
                        <div
                            class="w-8 h-8 rounded-full bg-orange-600 text-white flex items-center justify-center text-sm font-semibold">
                            JD
                        </div>
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Contenu scrollable -->
            <main class="flex-1 overflow-y-auto p-6 space-y-6">

                <!-- Cartes stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- Carte 1 -->
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Revenus</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">12 340 €</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-green-600 mt-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +12,5% ce mois
                        </p>
                    </div>

                    <!-- Carte 2 -->
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Utilisateurs</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">1 429</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3 3 3 0 013 3z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-green-600 mt-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +8,2% ce mois
                        </p>
                    </div>

                    <!-- Carte 3 -->
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Commandes</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">342</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-red-500 mt-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            -2,1% ce mois
                        </p>
                    </div>

                    <!-- Carte 4 -->
                    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Taux conversion</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">4,7%</p>
                            </div>
                            <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-green-600 mt-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            +0,8% ce mois
                        </p>
                    </div>
                </div>

                <!-- Graphique + Activité -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Graphique -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-semibold text-gray-800">Ventes des 7 derniers jours</h3>
                                <p class="text-sm text-gray-500">Aperçu hebdomadaire</p>
                            </div>
                            <button class="text-sm text-orange-600 hover:text-orange-800 font-medium">
                                Voir plus
                            </button>
                        </div>

                        <!-- Graphique simulé en barres -->
                        <div class="flex items-end justify-between h-56 gap-3">
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 40%"></div>
                                <span class="text-xs text-gray-500">Lun</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 65%"></div>
                                <span class="text-xs text-gray-500">Mar</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 45%"></div>
                                <span class="text-xs text-gray-500">Mer</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-500 rounded-t-lg hover:bg-orange-600 transition"
                                    style="height: 85%"></div>
                                <span class="text-xs text-gray-500">Jeu</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 55%"></div>
                                <span class="text-xs text-gray-500">Ven</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 30%"></div>
                                <span class="text-xs text-gray-500">Sam</span>
                            </div>
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full bg-orange-200 rounded-t-lg hover:bg-orange-400 transition"
                                    style="height: 70%"></div>
                                <span class="text-xs text-gray-500">Dim</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activité récente -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-semibold text-gray-800 mb-5">Activité récente</h3>
                        <ul class="space-y-4">
                            <li class="flex gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-800">Nouvelle commande #1234</p>
                                    <p class="text-xs text-gray-500">Il y a 5 min</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-800">Nouvel utilisateur inscrit</p>
                                    <p class="text-xs text-gray-500">Il y a 20 min</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-800">Stock faible : Produit B</p>
                                    <p class="text-xs text-gray-500">Il y a 1 h</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-800">Nouveau message reçu</p>
                                    <p class="text-xs text-gray-500">Il y a 2 h</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Dernières commandes</h3>
                        <button class="text-sm text-orange-600 hover:text-orange-800 font-medium">
                            Tout voir
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium">Client</th>
                                    <th class="px-6 py-3 text-left font-medium">Produit</th>
                                    <th class="px-6 py-3 text-left font-medium">Montant</th>
                                    <th class="px-6 py-3 text-left font-medium">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">Marie Laurent</td>
                                    <td class="px-6 py-3 text-gray-600">Abonnement Pro</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">49,00 €</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2.5 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Payée</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">Paul Bernard</td>
                                    <td class="px-6 py-3 text-gray-600">Pack Starter</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">19,00 €</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2.5 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium">En
                                            attente</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">Sophie Martin</td>
                                    <td class="px-6 py-3 text-gray-600">Abonnement Pro</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">49,00 €</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2.5 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Payée</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">Lucas Petit</td>
                                    <td class="px-6 py-3 text-gray-600">Pack Entreprise</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">199,00 €</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2.5 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">Annulée</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>

</html>
