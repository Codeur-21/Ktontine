<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Models\Tontine;
use App\Models\TontinePayment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationCenter extends Component
{
    public function markAllRead(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $user->activeTenantId();
        session()->put('notification_reads.'.$user->id, $this->items()->pluck('key')->all());
    }

    public function dismiss(string $key): void
    {
        abort_unless($this->items()->contains('key', $key), 404);
        $sessionKey = 'notification_dismissals.'.auth()->id();
        session()->put($sessionKey, array_values(array_unique([
            ...session()->get($sessionKey, []), $key,
        ])));
    }

    public function dismissAll(): void
    {
        $keys = $this->items()->pluck('key')->all();
        $sessionKey = 'notification_dismissals.'.auth()->id();
        session()->put($sessionKey, array_values(array_unique([
            ...session()->get($sessionKey, []), ...$keys,
        ])));
    }

    #[On('payment-updated')]
    public function refreshNotifications(): void {}

    private function items(): Collection
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $tenantId = $user->activeTenantId();
        $today = CarbonImmutable::today();
        $items = collect();
        $add = function (string $key, string $title, string $message, string $url, string $type = 'info') use ($items): void {
            $items->push(compact('key', 'title', 'message', 'url', 'type'));
        };

        Tontine::query()->where('tenant_id', $tenantId)->where('status', 'active')
            ->whereHas('customer', fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->with('customer:id,tenant_id,name')
            ->withCount(['days as paid_due_days' => fn (Builder $query) => $query
                ->whereRaw('DAYOFWEEK(due_date) <> 1')->whereColumn('due_date', '>=', 'tontines.start_date')
                ->whereColumn('due_date', '<=', 'tontines.end_date')->whereDate('due_date', '<', $today)
                ->whereHas('payments', fn (Builder $payment) => $payment->whereNull('voided_at')
                    ->whereColumn('tontine_payments.tontine_id', 'tontine_days.tontine_id'))])
            ->chunkById(100, function ($tontines) use ($today, $add): void {
                foreach ($tontines as $tontine) {
                    $start = CarbonImmutable::instance($tontine->start_date);
                    $end = CarbonImmutable::instance($tontine->end_date);
                    $lastDue = $end->min($today->subDay());
                    $expected = $lastDue->lt($start) ? 0 : Tontine::contributionDaysBetween($start, $lastDue);
                    $missing = max(0, $expected - (int) $tontine->paid_due_days);
                    if ($missing > 0) {
                        $add('late-'.$tontine->id.'-'.$today->toDateString().'-'.$missing, 'Paiements en retard',
                            $tontine->customer->name.' · '.$tontine->reference.' : '.$missing.' journée(s) à régulariser.', route('cotisations', ['tontine' => $tontine->id]), 'warning');
                    }
                    if ($end->lte($today->addDays(3))) {
                        $add('end-'.$tontine->id.'-'.$end->toDateString(), $end->lt($today) ? 'Cycle terminé à vérifier' : 'Fin de tontine proche',
                            $tontine->customer->name.' · '.$tontine->reference.' : fin le '.$end->format('d/m/Y').'. Vérifiez le solde et la commission.', route('tontines'), 'warning');
                    }
                }
            });

        foreach (TontinePayment::query()->where('tenant_id', $tenantId)->whereNotNull('voided_at')->where('updated_at', '>=', $today->subDays(7))
            ->whereHas('tontine.customer')->with('tontine.customer:id,tenant_id,name')->latest('updated_at')->limit(20)->get() as $payment) {
            $add('payment-'.$payment->id.'-'.sha1($payment->updated_at->toIso8601String().'|'.$payment->amount.'|'.$payment->voided_at?->toIso8601String()), 'Cotisation annulée',
                $payment->tontine->customer->name.' · '.number_format($payment->amount, 0, ',', ' ').' GNF · '.$payment->payment_date->format('d/m/Y'),
                route('cotisations', ['tontine' => $payment->tontine_id]), 'warning');
        }
        $subscription = Subscription::query()->where('tenant_id', $tenantId)->latest('id')->first();
        if ($subscription) {
            $end = $subscription->status === 'trialing' ? $subscription->trial_ends_at : $subscription->ends_at;
            if ($end && $end->lte($today->addDays(7))) {
                $add('subscription-'.$subscription->id.'-'.$end->toDateString(), 'Abonnement à vérifier',
                    'Votre période se termine le '.$end->format('d/m/Y').'. Pensez à vérifier votre abonnement.', route('abonnement'), 'warning');
            }
        }

        $read = session()->get('notification_reads.'.$user->id, []);

        $dismissed = session()->get('notification_dismissals.'.$user->id, []);

        return $items->reject(fn (array $item): bool => in_array($item['key'], $dismissed, true))->values()
            ->map(fn (array $item): array => [...$item, 'unread' => ! in_array($item['key'], $read, true)]);
    }

    public function render(): View
    {
        $items = $this->items();

        return view('livewire.notification-center', ['items' => $items, 'unreadCount' => $items->where('unread', true)->count()]);
    }
}
