<x-filament-panels::page>
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-3xl bg-[#151311] px-6 py-7 text-white shadow-xl shadow-slate-950/10 sm:px-8 sm:py-9">
            <div class="absolute -right-16 -top-20 size-64 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-24 right-24 size-52 rounded-full bg-[#c64b08]/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-11 items-center justify-center rounded-2xl bg-[#c64b08] text-lg font-black shadow-lg shadow-orange-950/30">
                            K
                        </div>
                        <div>
                            <p class="font-bold tracking-wide">Ktontine</p>
                            <p class="text-xs uppercase tracking-[0.22em] text-[#ffab61]">Administration</p>
                        </div>
                    </div>

                    <p class="text-sm font-medium text-[#ffbd88]">Bonjour {{ filament()->auth()->user()->name }},</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Votre plateforme en un coup d'œil.
                    </h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                        Suivez la croissance des tontiniers, les accès utilisateurs et la santé des abonnements.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $urls['createTenant'] }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#c64b08] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-950/20 transition hover:bg-[#a93e06] focus:outline-none focus:ring-2 focus:ring-[#ffab61]">
                        <x-filament::icon icon="heroicon-o-plus" class="size-5" />
                        Nouveau tontinier
                    </a>
                    <a href="{{ $urls['subscriptions'] }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/40">
                        <x-filament::icon icon="heroicon-o-credit-card" class="size-5" />
                        Voir les abonnements
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ $urls['tenants'] }}"
                class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-lg dark:border-white/10 dark:bg-gray-900 dark:hover:border-orange-500/40">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tontiniers</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($stats['tenants'], 0, ',', ' ') }}</p>
                        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ $stats['activeTenants'] }} comptes actifs</p>
                    </div>
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-orange-50 text-[#c64b08] transition group-hover:bg-[#c64b08] group-hover:text-white dark:bg-orange-500/10">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="size-6" />
                    </span>
                </div>
            </a>

            <a href="{{ $urls['users'] }}"
                class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg dark:border-white/10 dark:bg-gray-900 dark:hover:border-blue-500/40">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Utilisateurs</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($stats['users'], 0, ',', ' ') }}</p>
                        <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">Tous les comptes
                            enregistrés</p>
                    </div>
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-500/10 dark:text-blue-400">
                        <x-filament::icon icon="heroicon-o-users" class="size-6" />
                    </span>
                </div>
            </a>

            <a href="{{ $urls['subscriptions'] }}"
                class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg dark:border-white/10 dark:bg-gray-900 dark:hover:border-emerald-500/40">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Abonnements actifs</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($stats['activeSubscriptions'], 0, ',', ' ') }}</p>
                        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">Revenus récurrents
                        </p>
                    </div>
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="size-6" />
                    </span>
                </div>
            </a>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenu annuel prévu</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($stats['annualRevenue'], 0, ',', ' ') }} <span
                                class="text-sm font-semibold text-gray-400">GNF</span></p>
                        <p class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">Abonnements actifs</p>
                    </div>
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                        <x-filament::icon icon="heroicon-o-banknotes" class="size-6" />
                    </span>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
                <div class="flex items-center gap-4">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                        <x-filament::icon icon="heroicon-o-clock" class="size-6" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-amber-950 dark:text-amber-100">Essais à surveiller</p>
                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">{{ $stats['trialsEndingSoon'] }}
                            période(s) d’essai se terminent dans les 7 prochains jours.</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 dark:border-rose-500/20 dark:bg-rose-500/10">
                <div class="flex items-center gap-4">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="size-6" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-rose-950 dark:text-rose-100">Paiements en retard</p>
                        <p class="mt-1 text-sm text-rose-700 dark:text-rose-300">{{ $stats['pastDue'] }} abonnement(s)
                            nécessitent une intervention.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <div>
                        <h3 class="font-semibold text-gray-950 dark:text-white">Derniers tontiniers</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Les inscriptions les plus récentes.</p>
                    </div>
                    <a href="{{ $urls['tenants'] }}"
                        class="text-sm font-semibold text-[#c64b08] hover:text-[#a93e06]">Tout voir</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($recentTenants as $tenant)
                        <a href="{{ \App\Filament\Resources\Tenants\TenantResource::getUrl('view', ['record' => $tenant]) }}"
                            class="flex items-center gap-4 px-5 py-4 transition hover:bg-orange-50/50 dark:hover:bg-white/5">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#151311] text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-gray-950 dark:text-white">{{ $tenant->name }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $tenant->users_count }}
                                    utilisateur(s) · inscrit {{ $tenant->created_at->diffForHumans() }}</p>
                            </div>
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' =>
                                    $tenant->is_active,
                                'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' => !$tenant->is_active,
                            ])>
                                {{ $tenant->is_active ? 'Actif' : 'Suspendu' }}
                            </span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Aucun tontinier
                            enregistré.</div>
                    @endforelse
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h3 class="font-semibold text-gray-950 dark:text-white">Actions rapides</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Accédez aux tâches courantes.</p>
                <div class="mt-5 grid gap-3">
                    <a href="{{ $urls['createTenant'] }}"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:border-orange-200 hover:bg-orange-50 dark:border-white/10 dark:hover:border-orange-500/30 dark:hover:bg-orange-500/10">
                        <span
                            class="flex size-9 items-center justify-center rounded-lg bg-orange-50 text-[#c64b08] dark:bg-orange-500/10"><x-filament::icon
                                icon="heroicon-o-building-office-2" class="size-5" /></span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Ajouter un
                            tontinier</span>
                    </a>
                    <a href="{{ $urls['createUser'] }}"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:border-blue-200 hover:bg-blue-50 dark:border-white/10 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/10">
                        <span
                            class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"><x-filament::icon
                                icon="heroicon-o-user-plus" class="size-5" /></span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Créer un
                            utilisateur</span>
                    </a>
                    <a href="{{ $urls['createSubscription'] }}"
                        class="flex items-center gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:border-emerald-200 hover:bg-emerald-50 dark:border-white/10 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-500/10">
                        <span
                            class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"><x-filament::icon
                                icon="heroicon-o-credit-card" class="size-5" /></span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Créer un abonnement</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
