<div class="h-svh overflow-hidden bg-[#fffaf5] dark:bg-slate-950">
    <div class="grid h-full lg:grid-cols-[1.08fr_.92fr]">
        <section
            class="relative hidden overflow-hidden bg-[#151311] p-8 text-white lg:flex lg:flex-col lg:justify-between xl:p-10">
            <div class="absolute -left-24 top-1/3 size-80 rounded-full border border-white/10"></div>
            <div class="absolute -right-32 -top-32 size-96 rounded-full border border-white/10"></div>
            <div
                class="absolute bottom-0 right-0 h-2/3 w-2/3 bg-[radial-gradient(circle_at_bottom_right,rgba(198,75,8,.32),transparent_68%)]">
            </div>

            <a href="/"
                class="relative flex w-fit items-center gap-3 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#ffab61] focus:ring-offset-4 focus:ring-offset-[#151311]">
                <span
                    class="flex size-12 items-center justify-center rounded-2xl bg-[#c64b08] text-xl font-black shadow-xl shadow-black/20">K</span>
                <span>
                    <span class="block text-xl font-bold tracking-tight">Ktontine</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[.24em] text-[#ffab61]">Kankirady
                        Tontine</span>
                </span>
            </a>

            <div class="relative max-w-xl py-4 xl:py-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-[#ffbd88] backdrop-blur">
                    <span class="size-1.5 rounded-full bg-[#ffab61]"></span>
                    Espace d’administration sécurisé
                </span>

                <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight xl:text-5xl">
                    Pilotez la plateforme<br>
                    <span class="text-[#ffab61]">avec clarté.</span>
                </h1>
                <p class="mt-3 max-w-lg text-base leading-6 text-slate-300 xl:text-lg">
                    Gérez les tontiniers, leurs équipes et leurs abonnements depuis un espace unique conçu pour votre
                    activité.
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                        <x-filament::icon icon="heroicon-o-shield-check" class="size-6 text-[#ffab61]" />
                        <p class="mt-2 text-sm font-semibold">Accès sécurisé</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="size-6 text-[#ffab61]" />
                        <p class="mt-2 text-sm font-semibold">Suivi en direct</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3 backdrop-blur">
                        <x-filament::icon icon="heroicon-o-bolt" class="size-6 text-[#ffab61]" />
                        <p class="mt-2 text-sm font-semibold">Actions rapides</p>
                    </div>
                </div>
            </div>

            <div class="relative flex items-center justify-between gap-4 text-xs text-slate-400">
                <span>© {{ now()->year }} Ktontine</span>
                <span>Ensemble aujourd’hui, plus forts demain.</span>
            </div>
        </section>

        <main class="relative flex h-full items-center justify-center overflow-hidden px-5 py-4 sm:px-8 sm:py-6 lg:px-10 xl:px-12">
            <div
                class="absolute inset-x-0 top-0 h-48 bg-gradient-to-b from-orange-100/60 to-transparent dark:from-orange-500/5 lg:hidden">
            </div>

            <div class="relative w-full max-w-md">
                <a href="/" class="mb-5 flex w-fit items-center gap-3 lg:hidden">
                    <span
                        class="flex size-11 items-center justify-center rounded-2xl bg-[#c64b08] text-lg font-black text-white shadow-lg shadow-orange-900/20">K</span>
                    <span>
                        <span class="block text-lg font-bold text-[#151311] dark:text-white">Ktontine</span>
                        <span class="block text-[9px] font-semibold uppercase tracking-[.22em] text-[#c64b08]">Kankirady
                            Tontine</span>
                    </span>
                </a>

                <div class="mb-5">
                    <p class="text-sm font-semibold text-[#c64b08]">Heureux de vous revoir</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-[#151311] dark:text-white">Connectez-vous
                    </h2>
                    <p class="mt-2 text-sm leading-5 text-slate-500 dark:text-slate-400">
                        Utilisez votre compte administrateur pour accéder au panneau Ktontine.
                    </p>
                </div>

                <div
                    class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl shadow-slate-900/5 dark:border-white/10 dark:bg-slate-900 sm:p-6">
                    {{ $this->content }}
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="size-4" />
                    Connexion protégée et limitée contre les tentatives abusives
                </div>
            </div>
        </main>
    </div>
</div>
