<x-filament-panels::page>
    <div class="kt-central-settings space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-stone-950 via-stone-900 to-orange-950 p-6 text-white sm:p-8" aria-labelledby="central-settings-heading">
            <div class="pointer-events-none absolute -top-16 -right-12 size-60 rounded-full border border-orange-400/15" aria-hidden="true"></div>
            <div class="pointer-events-none absolute top-0 -right-24 size-80 rounded-full border border-orange-400/10" aria-hidden="true"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-xl"><span class="text-[10px] font-semibold tracking-[0.2em] text-orange-300">LE CENTRE DE VOTRE PLATEFORME</span><h2 id="central-settings-heading" class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Une plateforme, une identité.</h2><p class="mt-3 text-sm leading-7 text-stone-300">Personnalisez votre marque, harmonisez vos documents et définissez les repères de vos tontiniers.</p></div>
                <span class="inline-flex items-center gap-2 rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-2 text-xs text-orange-200"><x-filament::icon icon="heroicon-o-globe-alt" class="size-4" />Configuration centrale</span>
            </div>
            <div class="relative mt-6 grid gap-3 border-t border-white/10 pt-5 text-xs text-stone-300 sm:grid-cols-3"><span class="flex items-center gap-2"><x-filament::icon icon="heroicon-o-swatch" class="size-4 text-orange-300" />Identité & coordonnées</span><span class="flex items-center gap-2"><x-filament::icon icon="heroicon-o-document-duplicate" class="size-4 text-orange-300" />Documents & filigrane</span><span class="flex items-center gap-2"><x-filament::icon icon="heroicon-o-adjustments-horizontal" class="size-4 text-orange-300" />Préférences de suivi</span></div>
        </section>
        <div class="grid items-start gap-6 xl:grid-cols-4">
            <aside class="rounded-2xl border border-gray-200 bg-white p-5 xl:sticky xl:top-24 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white"><x-filament::icon icon="heroicon-o-information-circle" class="size-5 text-orange-600 dark:text-orange-400" />La portée de vos réglages</div>
                <p class="text-xs leading-6 text-gray-500 dark:text-gray-400">Aucun tontinier n’est à sélectionner. Cette page définit la configuration commune de Ktontine.</p>
                <dl class="mt-5 space-y-5 border-t border-gray-100 pt-5 text-xs dark:border-white/10">
                    <div><dt class="mb-1.5 font-semibold text-gray-800 dark:text-gray-200">Marque partagée</dt><dd class="leading-6 text-gray-500 dark:text-gray-400">Le logo apparaît dans le site, le panel, les favoris et les tickets.</dd></div>
                    <div><dt class="mb-1.5 font-semibold text-gray-800 dark:text-gray-200">Filigrane commun</dt><dd class="leading-6 text-gray-500 dark:text-gray-400">Son affichage s’applique à toutes les planches, y compris celles déjà préparées.</dd></div>
                    <div><dt class="mb-1.5 font-semibold text-gray-800 dark:text-gray-200">Valeurs de départ</dt><dd class="leading-6 text-gray-500 dark:text-gray-400">Les autres préférences proposent les réglages initiaux des nouveaux espaces et documents.</dd></div>
                </dl>
                <div class="mt-5 rounded-xl bg-orange-50 p-3 text-xs leading-6 text-orange-900 dark:bg-orange-500/10 dark:text-orange-200">Les coordonnées propres aux tontiniers restent dans leur espace.</div>
            </aside>
            <form wire:submit="save" class="min-w-0 space-y-5 xl:col-span-3">
                {{ $this->form }}
                <div class="rounded-xl border border-dashed border-gray-200 px-4 py-3 text-xs leading-6 text-gray-500 dark:border-white/15 dark:text-gray-400"><strong class="font-medium text-gray-700 dark:text-gray-300">Besoin de repartir des valeurs proposées ?</strong> « Préférences par défaut » prépare les réglages des documents et du suivi. Enregistrez ensuite pour les appliquer.</div>
                <div class="sticky bottom-3 z-20 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-orange-200 bg-white/95 p-4 shadow-lg backdrop-blur-md dark:border-orange-500/20 dark:bg-gray-900/95">
                    <div class="text-xs text-gray-500 dark:text-gray-400"><span wire:dirty wire:target="data" class="items-center gap-2 text-orange-700 dark:text-orange-300"><span aria-hidden="true">●</span> Modifications non enregistrées</span><span wire:dirty.remove wire:target="data">Vos réglages, au même endroit.</span><span wire:loading wire:target="save" class="mt-1 block" role="status">Enregistrement en cours…</span></div>
                    <div class="flex flex-wrap items-center gap-2"><x-filament::button type="button" color="gray" size="sm" wire:click="defaultPreferences" wire:loading.attr="disabled">Préférences par défaut</x-filament::button><x-filament::button type="button" color="gray" size="sm" wire:click="cancelChanges" wire:loading.attr="disabled">Annuler</x-filament::button><x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled">Enregistrer les paramètres centraux</x-filament::button></div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
