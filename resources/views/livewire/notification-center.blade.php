<div wire:poll.15s x-data="{ opened: false, dismissalKey: null, clearing: false }">
    <button type="button" class="kt-notification-trigger" x-on:click="opened = true" aria-haspopup="dialog" aria-controls="notifications-modal" :aria-expanded="opened" aria-label="{{ $unreadCount > 0 ? 'Notifications : '.$unreadCount.' non lues' : 'Notifications' }}" title="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        @if ($unreadCount > 0)
            <span class="kt-notification-counter" aria-hidden="true">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>
    <dialog id="notifications-modal" class="kt-notifications-dialog" aria-labelledby="notifications-title" x-effect="if (opened && !$el.open) $el.showModal(); else if (!opened && $el.open) $el.close()" x-on:close="opened = false" x-on:cancel="opened = false" x-on:click="if ($event.target === $el) opened = false">
        <header class="kt-notifications-header">
            <div><span class="kt-notifications-eyebrow">VOTRE ACTIVITÉ EN DIRECT</span><h2 id="notifications-title">Notifications <span>{{ $unreadCount }}</span></h2><p>Retards, échéances et alertes importantes.</p></div>
            <button class="kt-notifications-close" type="button" x-on:click="opened = false" aria-label="Fermer les notifications">×</button>
        </header>
        <div class="kt-notifications-toolbar"><span>Actualisation toutes les 15 secondes</span><button type="button" class="kt-notifications-read" wire:click="markAllRead" wire:loading.attr="disabled" @disabled($unreadCount === 0)>Tout marquer comme lu</button></div>
        <div class="kt-notifications-toolbar">
            <span>Gérez vos notifications</span>
            <button type="button" class="kt-notifications-read" x-on:click="dismissalKey = null; $refs.clearNotificationsDialog.showModal()" wire:loading.attr="disabled" @disabled($items->isEmpty())>Tout effacer</button>
        </div>
        <div class="kt-notifications-content">
            <ul class="kt-notifications-list" aria-label="Votre activité">
                @forelse ($items as $item)
                    <li wire:key="{{ $item['key'] }}" class="kt-notification-item {{ $item['unread'] ? 'is-unread' : '' }}">
                        <span class="kt-notification-icon {{ $item['type'] === 'warning' ? 'kt-notification-warning' : '' }}" aria-hidden="true">{{ $item['type'] === 'warning' ? '!' : '✓' }}</span>
                        <div class="min-w-0"><div class="kt-notification-title"><strong>{{ $item['title'] }}</strong>@if ($item['unread'])<span class="kt-notification-unread" aria-label="Non lue"></span>@endif</div><p>{{ $item['message'] }}</p><a href="{{ $item['url'] }}" class="kt-notification-meta underline underline-offset-4">Consulter →</a></div>
                        <button type="button" class="kt-notifications-close" style="flex-shrink:0;margin-left:auto;width:32px;height:32px;font-size:18px" x-on:click="dismissalKey = @js($item['key']); $refs.clearNotificationsDialog.showModal()" wire:loading.attr="disabled" aria-label="Effacer cette notification" title="Effacer cette notification">×</button>
                    </li>
                @empty
                    <li class="kt-notifications-empty"><h3>Vous êtes à jour.</h3><p>Aucune alerte en cours.</p></li>
                @endforelse
            </ul>
        </div>
        <footer class="kt-notifications-footer"><span>Les retards disparaissent après régularisation.</span><button type="button" x-on:click="opened = false">Fermer</button></footer>
    </dialog>
    <dialog id="clear-notifications-modal" x-ref="clearNotificationsDialog" class="tontine-delete-dialog" wire:ignore.self
        aria-labelledby="clear-notifications-title" aria-describedby="clear-notifications-description"
        x-on:cancel.prevent="if (!clearing) $el.close()"
        x-on:click="if ($event.target === $el && !clearing) $el.close()">
        <form class="tontine-delete-content" x-on:submit.prevent="clearing = true; (dismissalKey === null ? $wire.dismissAll() : $wire.dismiss(dismissalKey)).then(() => $refs.clearNotificationsDialog.close()).finally(() => clearing = false)">
            <button type="button" class="tontine-delete-close" x-on:click="$refs.clearNotificationsDialog.close()" :disabled="clearing" aria-label="Fermer la confirmation">×</button>
            <span class="tontine-delete-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></svg></span>
            <h2 id="clear-notifications-title" x-text="dismissalKey === null ? 'Effacer toutes les notifications ?' : 'Effacer cette notification ?'"></h2>
            <p id="clear-notifications-description">Les notifications seront retirées de ce panneau. Vos clients, tontines et cotisations seront conservés. Les nouvelles alertes continueront à apparaître.</p>
            <div class="tontine-delete-buttons">
                <button type="button" class="tontine-delete-cancel" x-on:click="$refs.clearNotificationsDialog.close()" :disabled="clearing" autofocus>Annuler</button>
                <button type="submit" class="tontine-delete-confirm" :disabled="clearing" x-text="clearing ? 'Effacement…' : 'Confirmer l’effacement'"></button>
            </div>
        </form>
    </dialog>
</div>
