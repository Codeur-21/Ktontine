<x-filament-panels::page>
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-stone-950 via-stone-900 to-orange-950 p-6 text-white sm:p-8">
            <span class="text-[10px] font-semibold tracking-[0.2em] text-orange-300">GARDEZ LE LIEN AVEC VOS TONTINIERS</span>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Le bon message, aux bonnes personnes.</h2>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-300">Annoncez une nouveauté à plusieurs espaces ou échangez avec un tontinier en particulier. Relisez votre message avant de confirmer son envoi.</p>
            <div class="mt-6 flex flex-wrap gap-3 text-xs text-orange-100"><span class="rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-2">Mail marketing</span><span class="rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-2">Message individuel</span><span class="rounded-full border border-orange-300/20 bg-orange-300/10 px-3 py-2">Aperçu avant envoi</span></div>
        </section>
        <div class="grid items-start gap-6 xl:grid-cols-4">
            <aside class="space-y-4 xl:sticky xl:top-24">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Commencez avec un modèle</h3>
                    <p class="mt-2 text-xs leading-6 text-gray-500 dark:text-gray-400">Ces textes sont à compléter avant l’envoi.</p>
                    <div class="mt-4 grid gap-2">
                        {{ $this->newsTemplateAction }}
                        {{ $this->offerTemplateAction }}
                        {{ $this->personalTemplateAction }}
                    </div>
                </section>
                <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5 text-xs leading-6 text-orange-950 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-200"><strong>Une attention pour chaque espace.</strong><p class="mt-2">Chaque destinataire reçoit son propre mail, sans voir les adresses des autres. Les coordonnées proviennent du compte du propriétaire.</p><p class="mt-3">Les variables <code>{nom}</code> et <code>{espace}</code> sont remplacées automatiquement.</p></div>
            </aside>
            <form wire:submit="prepare" class="min-w-0 space-y-5 xl:col-span-3">
                {{ $this->form }}
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-orange-200 bg-white p-4 dark:border-orange-500/20 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">L’aperçu ne déclenche aucun envoi.</p>
                    <x-filament::button type="submit" icon="heroicon-o-eye" wire:loading.attr="disabled" wire:target="prepare,sendPrepared">Prévisualiser et vérifier</x-filament::button>
                </div>
            </form>
        </div>
        @if ($results !== [])
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900" aria-labelledby="communication-results-title" wire:poll.5s="refreshResults">
                <h3 id="communication-results-title" class="text-base font-semibold text-gray-950 dark:text-white">Bilan du dernier envoi de cette session</h3>
                <p class="mt-2 text-xs leading-6 text-gray-500 dark:text-gray-400">« Accepté » signifie que le serveur de messagerie a accepté le mail, sans confirmer sa réception en boîte de réception.</p>
                <div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs text-gray-500 dark:text-gray-400"><tr><th class="py-3 pr-4">Tontinier</th><th class="py-3 pr-4">Adresse</th><th class="py-3">Résultat</th></tr></thead><tbody>
                    @foreach ($results as $result)
                        <tr class="border-t border-gray-100 dark:border-white/10"><td class="py-3 pr-4 text-gray-900 dark:text-white">{{ $result['name'] }}</td><td class="py-3 pr-4 text-gray-500 dark:text-gray-400">{{ $result['email'] }}</td><td class="py-3"><x-filament::badge :color="match ($result['status']) { 'accepted' => 'success', 'failed' => 'danger', 'processing' => 'info', 'skipped' => 'gray', default => 'warning' }">{{ match ($result['status']) { 'accepted' => 'Accepté', 'failed' => 'Échec', 'processing' => 'En cours', 'skipped' => 'Annulé', default => 'En attente' } }}</x-filament::badge></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @endif
    </div>
    <dialog class="kt-communication-preview" wire:ignore.self x-data @communication-preview.window="$el.showModal()" @communication-finished.window="$el.close()" aria-labelledby="communication-preview-title">
        @if ($prepared)
            <div class="flex items-start justify-between gap-4"><div><span class="text-xs font-semibold uppercase tracking-wider text-orange-700 dark:text-orange-300">Vérifier avant d’envoyer</span><h2 id="communication-preview-title" class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ count($prepared['recipients']) }} destinataire(s)</h2></div><form method="dialog"><x-filament::button type="submit" color="gray" size="sm">Fermer</x-filament::button></form></div>
            <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-white/10"><p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Destinataires</p><ul class="mt-2 max-h-32 space-y-1 overflow-auto text-xs text-gray-500 dark:text-gray-400">@foreach ($prepared['recipients'] as $recipient)<li>{{ $recipient['businessName'] }} · {{ $recipient['name'] }} · {{ $recipient['email'] }}</li>@endforeach</ul></div>
            @php($sample = $prepared['recipients'][0])
            <div class="mt-5 overflow-hidden rounded-xl border border-orange-200 bg-white text-stone-800"><div class="bg-stone-950 px-5 py-4 font-semibold text-white">{{ $prepared['platformName'] }} · {{ $sample['businessName'] }}</div><div class="p-5"><h3 class="text-lg font-semibold">{{ strtr($prepared['subject'], ['{nom}' => $sample['name'], '{espace}' => $sample['businessName']]) }}</h3><p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-stone-600">{{ strtr($prepared['messageBody'], ['{nom}' => $sample['name'], '{espace}' => $sample['businessName']]) }}</p>@if ($prepared['buttonLabel'] && $prepared['buttonUrl'])<span class="mt-5 inline-block rounded-lg bg-orange-700 px-4 py-3 text-sm font-semibold text-white">{{ $prepared['buttonLabel'] }}</span><p class="mt-2 break-all text-xs text-stone-500">{{ $prepared['buttonUrl'] }}</p>@endif<p class="mt-6 text-sm text-stone-500">L’équipe {{ $prepared['platformName'] }}</p></div></div>
            <p class="mt-4 text-xs leading-6 text-gray-500 dark:text-gray-400">Aperçu personnalisé pour le premier destinataire. Chaque mail utilisera le nom et l’espace de son destinataire.</p>
            <div class="mt-5 flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-white/10"><form method="dialog"><x-filament::button type="submit" color="gray">Revenir au message</x-filament::button></form><x-filament::button icon="heroicon-o-paper-airplane" wire:click="sendPrepared" wire:loading.attr="disabled" wire:target="sendPrepared"><span wire:loading.remove wire:target="sendPrepared">Confirmer l’envoi</span><span wire:loading wire:target="sendPrepared">Envoi en cours…</span></x-filament::button></div>
        @endif
    </dialog>
    <style>.kt-communication-preview{box-sizing:border-box;width:min(760px,calc(100vw - 32px));max-height:calc(100dvh - 40px);overflow:auto;margin:auto;padding:26px;border:1px solid #e5d7cc;border-radius:20px;background:#fffdfb;box-shadow:0 24px 80px #21150e30}.kt-communication-preview::backdrop{background:#191512aa;backdrop-filter:blur(5px)}.dark .kt-communication-preview{background:#111827;border-color:#ffffff20}.kt-communication-preview form{margin:0}@media(max-width:600px){.kt-communication-preview{padding:18px}}</style>
</x-filament-panels::page>
