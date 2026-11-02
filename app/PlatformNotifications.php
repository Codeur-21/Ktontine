<?php

namespace App;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Scopes\TenantScope;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PlatformNotifications
{
    public function ownerRegistered(User $user): void
    {
        $user = $user->fresh();
        if ($user === null || $user->tenant_id === null || $user->role !== 'owner' || $user->tenant === null) {
            return;
        }
        $this->emit('registration:'.$user->tenant_id.':'.$user->id, Notification::make()
            ->title('Nouvelle inscription tontinier')->success()->icon('heroicon-o-user-plus')
            ->body(e($user->name).' a créé son accès à l’espace '.e($user->tenant->name).'.')
            ->actions([$this->viewAction(TenantResource::getUrl('view', ['record' => $user->tenant_id], panel: 'workspace'))]));
    }

    public function subscriptionChanged(Subscription $subscription, bool $created): void
    {
        $subscription = Subscription::withoutGlobalScope(TenantScope::class)->with('tenant')->find($subscription->id);
        if ($subscription?->tenant === null) {
            return;
        }
        $label = match ($subscription->status) {
            'active' => 'Actif', 'trialing' => 'Période d’essai', 'past_due' => 'Paiement en retard',
            'cancelled' => 'Annulé', 'expired' => 'Expiré', default => $subscription->status,
        };
        $notification = Notification::make()->title($created ? 'Nouvel abonnement' : 'Abonnement mis à jour')
            ->icon('heroicon-o-credit-card')
            ->body(e($subscription->tenant->name).' · '.e($label).' · '.number_format($subscription->amount, 0, ',', ' ').' '.e($subscription->currency).' / an.')
            ->actions([$this->viewAction(SubscriptionResource::getUrl('view', ['record' => $subscription->id], panel: 'workspace'))]);
        if (in_array($subscription->status, ['past_due', 'expired', 'cancelled'], true)) {
            $notification->warning();
        } else {
            $notification->success();
        }
        $fingerprint = json_encode($subscription->only(['status', 'amount', 'currency', 'starts_at', 'ends_at', 'trial_ends_at', 'updated_at']), JSON_THROW_ON_ERROR);
        $this->emit('subscription:'.$subscription->id.':'.($created ? 'created' : 'updated').':'.$fingerprint, $notification);
    }

    public function checkExpirations(): int
    {
        $count = 0;
        Tenant::query()->where('is_active', true)->whereHas('owner')->with([
            'subscriptions' => function ($query): void {
                $query->withoutGlobalScope(TenantScope::class)->latest('id')->limit(1);
            },
        ])->chunkById(100, function ($tenants) use (&$count): void {
            foreach ($tenants as $tenant) {
                if ($this->checkTenantExpiration($tenant)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    private function checkTenantExpiration(Tenant $tenant): bool
    {
        $subscription = $tenant->subscriptions->first();
        if ($subscription !== null && ! in_array($subscription->status, ['trialing', 'active', 'expired', 'past_due'], true)) {
            return false;
        }
        $isTrial = $subscription === null || $subscription->status === 'trialing';
        $deadline = $isTrial
            ? ($subscription?->trial_ends_at ?? CarbonImmutable::instance($tenant->created_at)->addMonthNoOverflow())
            : $subscription->ends_at;
        if ($deadline === null || $deadline->gt(now()->addDays(7))) {
            return false;
        }
        $expired = $deadline->lte(now());
        $title = match (true) {
            $isTrial && $expired => 'Essai gratuit terminé',
            $isTrial => 'Un essai gratuit arrive à échéance',
            $expired => 'Abonnement expiré',
            default => 'Un abonnement arrive à échéance',
        };
        $notification = Notification::make()->title($title)->icon('heroicon-o-calendar-days')
            ->body(e($tenant->name).' · '.($isTrial ? 'Fin de l’essai' : 'Fin de l’abonnement').' : '.$deadline->format('d/m/Y à H:i').'.')
            ->actions([$this->viewAction($subscription === null
                ? TenantResource::getUrl('view', ['record' => $tenant->id], panel: 'workspace')
                : SubscriptionResource::getUrl('view', ['record' => $subscription->id], panel: 'workspace'))]);
        $expired ? $notification->danger() : $notification->warning();

        return $this->emit('expiry:'.$tenant->id.':'.($subscription?->id ?? 'trial').':'.$deadline->format('Y-m-d H:i:s').':'.($expired ? 'expired' : 'soon'), $notification);
    }

    private function viewAction(string $url): Action
    {
        return Action::make('view')->label('Consulter')->button()->url($url)->markAsRead();
    }

    private function emit(string $eventKey, Notification $notification): bool
    {
        return DB::transaction(function () use ($eventKey, $notification): bool {
            $admins = User::query()->whereNull('tenant_id')->where('role', 'owner')->where('is_active', true);
            if (! (clone $admins)->exists()) {
                return false;
            }
            $inserted = DB::table('platform_notification_events')->insertOrIgnore([
                'event_key' => hash('sha256', $eventKey), 'created_at' => now(),
            ]);
            if ($inserted === 0) {
                return false;
            }
            $admins->chunkById(100, function ($users) use ($notification): void {
                foreach ($users as $admin) {
                    $admin->notifyNow($notification->toDatabase());
                }
            });

            return true;
        });
    }
}
