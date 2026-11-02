<div class="kt-page kt-dashboard">
    <style>
.db-commission{margin:24px 0}.db-commission-head{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:22px}.db-commission-head h2{margin:6px 0 0;font-size:21px;letter-spacing:-.4px}.db-commission-filter{display:flex;gap:10px;align-items:end;flex-wrap:wrap}.db-commission-filter label{font-size:11px;color:#796251;display:block;margin-bottom:7px;font-weight:600}.db-commission-input{position:relative}.db-commission-input svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:17px;height:17px;color:#ad531c;pointer-events:none}.db-commission-input input{border:1px solid #e5d7cc;border-radius:9px;padding:10px 12px 10px 38px;background:#fffaf6;color:#403025;font:inherit;font-size:13px;min-width:180px}.db-commission-filter button{border:1px solid #e5d7cc;background:white;color:#845331;border-radius:9px;padding:11px 14px;font:inherit;font-size:12px;cursor:pointer}.db-commission-filter :focus-visible{outline:2px solid #c64b08;outline-offset:3px}.db-commission-body{display:grid;grid-template-columns:minmax(220px,.8fr) minmax(0,1.7fr);gap:24px;align-items:start}.db-commission-summary{background:linear-gradient(135deg,#fff2e5,#fffaf5);border:1px solid #f1d6c0;border-radius:16px;padding:24px}.db-commission-month{color:#9f460e;font-size:12px;text-transform:capitalize}.db-commission-value{display:block;font-size:32px;line-height:1.2;letter-spacing:-1px;color:#903a09;margin:12px 0}.db-commission-value small{font-size:13px;letter-spacing:0}.db-commission-caption{font-size:12px;color:#76533b;margin:0 0 20px;line-height:1.7}.db-commission-counts{display:flex;gap:24px;padding-top:16px;border-top:1px solid #f0d7c3}.db-commission-counts strong{display:block;font-size:21px;color:#4e3522}.db-commission-counts span{font-size:11px;color:#856c58}.db-commission-rule{font-size:11px;color:#8a7564;line-height:1.8;margin:18px 0 0}.db-commission-table{width:100%;border-collapse:collapse;text-align:left;font-size:12px}.db-commission-table th{font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#957b65;border-bottom:1px solid #eee4db;padding:0 10px 12px}.db-commission-table td{padding:13px 10px;border-bottom:1px solid #f2ece7;color:#725b49}.db-commission-table td:last-child,.db-commission-table th:last-child{text-align:right;white-space:nowrap}.db-commission-table td:last-child{font-weight:bold;color:#a7460c}.db-commission-table strong{display:block;color:#423022;font-size:12px}.db-commission-table small{display:block;color:#99816f;font-size:10px;margin-top:4px}.db-commission-empty{text-align:center!important;padding:35px 15px!important;color:#927760!important;font-weight:normal!important;white-space:normal!important}.db-commission-error{color:#b42318;font-size:12px;margin:0 0 15px}.db-commission-pager{margin-top:15px}.db-commission-loading{font-size:11px;color:#927760;margin-top:12px}@media(max-width:850px){.db-commission-body{grid-template-columns:1fr}.db-commission-summary{padding:20px}.db-commission-value{font-size:29px}}@media(max-width:480px){.db-commission-filter{width:100%}.db-commission-filter>div{flex:1}.db-commission-input input{width:100%;min-width:0}.db-commission-table th,.db-commission-table td{padding-left:5px;padding-right:5px}.db-commission-head h2{font-size:19px}}
.db-commission-portfolio{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:22px 24px;margin-bottom:24px;border-radius:15px;background:#2e2118;color:#fff}.db-commission-portfolio span{display:block;font-size:12px;color:#e3b492;margin-bottom:9px}.db-commission-portfolio strong{display:block;font-size:33px;letter-spacing:-.8px}.db-commission-portfolio small{font-size:13px;letter-spacing:0;color:#f6b783}.db-commission-portfolio p{font-size:12px!important;line-height:1.8;color:#d6c0af!important;margin:0!important}@media(max-width:520px){.db-commission-portfolio{padding:19px}.db-commission-portfolio strong{font-size:29px}}
    .kt-workspace .kt-dashboard .db-history-toggle{padding:0;margin:0;border:0;border-radius:0;background:transparent;box-shadow:none}.db-history-toggle>summary{display:flex;align-items:center;justify-content:space-between;gap:14px;list-style:none;cursor:pointer;padding:10px 0;color:#796251;font-size:12px}.db-history-toggle>summary::-webkit-details-marker{display:none}.db-history-open,.db-history-close{padding:10px 14px;border:1px solid #e5d7cc;border-radius:9px;background:#fffaf6;color:#a7460c;font-weight:600}.db-history-close{display:none}.db-history-toggle[open]>summary .db-history-open{display:none}.db-history-toggle[open]>summary .db-history-close{display:inline-flex}.db-history-toggle>summary:focus-visible{outline:2px solid #c64b08;outline-offset:4px}.db-history-toggle>.db-panel{margin-top:12px}@media(max-width:480px){.db-history-toggle>summary{flex-wrap:wrap}}
</style>
    <section class="db-welcome" aria-labelledby="dashboard-title">
        <div class="db-welcome-copy"><span class="db-eyebrow">MON ACTIVITÉ · KTONTINE</span>
            <h1 id="dashboard-title">Une vue claire.<br><span>Une journée bien suivie.</span></h1>
            <p>Vos clients, vos cotisations et vos priorités, au même endroit.</p>
            <div class="db-welcome-actions"><a class="db-button" href="{{ route('cotisations') }}">Gérer les
                    cotisations <span aria-hidden="true">↗</span></a><a class="db-quiet-link"
                    href="{{ route('clients') }}">Voir mes clients →</a></div>
        </div>
        <div class="db-preview-card"><span class="db-preview-dot" aria-hidden="true"></span><strong>Votre tableau de
                bord</strong>
            <p>Les chiffres de votre espace, actualisés à chaque consultation.</p><span class="db-demo-label">Données de votre espace</span>
        </div>
    </section>
    <section class="db-metrics" aria-label="Indicateurs de votre activité">
        <a class="db-metric" href="{{ route('clients') }}">
            <div class="db-metric-top"><span>Clients actifs</span><span class="db-metric-icon"
                    aria-hidden="true">♧</span></div><strong>{{ $nberClient }}</strong>
            <div class="db-metric-foot"><span>Votre portefeuille</span><span aria-hidden="true">↗</span>
            </div>
        </a>
        <a class="db-metric db-metric-featured" href="{{ route('cotisations') }}">
            <div class="db-metric-top"><span>Cotisations du jour</span><span class="db-metric-icon"
                    aria-hidden="true">↗</span></div><strong>{{ number_format($dailyTotal, 0, ',', ' ') }} <small>GNF</small></strong>
            <div class="db-metric-foot"><span>Versements non annulés</span><span aria-hidden="true">↗</span></div>
        </a>
        <a class="db-metric" href="{{ route('tontines') }}">
            <div class="db-metric-top"><span>Tontines en cours</span><span class="db-metric-icon"
                    aria-hidden="true">◎</span></div><strong>{{ $nberTontine }}</strong>
            <div class="db-metric-foot"><span>Cycles actifs</span><span aria-hidden="true">↗</span></div>
        </a>
        <a class="db-metric" href="{{ route('retards') }}">
            <div class="db-metric-top"><span>Clients en retard</span><span class="db-metric-icon db-icon-alert"
                    aria-hidden="true">◷</span></div><strong>{{ $overdueCustomers }}</strong>
            <div class="db-metric-foot"><span>À suivre avec attention</span><span aria-hidden="true">↗</span></div>
        </a>
    </section>


    <section class="db-panel db-commission" aria-labelledby="commission-title">
        <div class="db-commission-head"><div><span class="db-overline">VOS REVENUS PRÉVISIONNELS</span><h2 id="commission-title">Mes commissions</h2></div>
            <div class="db-commission-filter"><div><label for="commissionMonth">Choisir un mois</label><div class="db-commission-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6M17 2v6M3 11h18"/></svg><input id="commissionMonth" type="month" wire:model.live="commissionMonth" aria-describedby="commission-rule" @error('commissionMonth') aria-invalid="true" @enderror></div></div><button type="button" wire:click="currentCommissionMonth" wire:loading.attr="disabled">Ce mois-ci</button></div>
        </div>
        <div class="db-commission-portfolio"><div><span>Total prévu · tontines en cours</span><strong>{{ number_format($portfolioCommissionTotal, 0, ',', ' ') }} <small>GNF</small></strong></div><p>Pour {{ $portfolioCommissionCustomerCount }} client(s), tous mois d’échéance confondus.<br>Le filtre ci-dessous détaille les commissions du mois choisi.</p></div>
        @error('commissionMonth')<p class="db-commission-error" role="alert">{{ $message }}</p>@enderror
        <div class="db-commission-body" wire:loading.class="opacity-60" wire:target="commissionMonth,currentCommissionMonth">
            <div><div class="db-commission-summary"><span class="db-commission-month">{{ $commissionMonthLabel }}</span><strong class="db-commission-value">{{ number_format($commissionTotal, 0, ',', ' ') }} <small>GNF</small></strong><p class="db-commission-caption">Commission prévue à l’échéance des tontines de ce mois.</p><div class="db-commission-counts"><div><strong>{{ $commissionCustomerCount }}</strong><span>client(s) concerné(s)</span></div><div><strong>{{ $commissionCycleCount }}</strong><span>tontine(s)</span></div></div></div><p class="db-commission-rule" id="commission-rule">Une commission correspond à une mise journalière par tontine dont la date de fin tombe dans le mois choisi. Si un client a plusieurs tontines, leurs commissions sont additionnées. Ce montant est prévisionnel ; il ne confirme pas un encaissement.</p></div>
            <div><div class="db-table-wrap"><table class="db-commission-table"><thead><tr><th scope="col">Client</th><th scope="col">Tontines</th><th scope="col">Commission prévue</th></tr></thead><tbody>@forelse($commissionCustomers as $commissionCustomer)<tr wire:key="commission-client-{{ $commissionCustomer->customer_id }}"><td><strong>{{ $commissionCustomer->customer->name }}</strong><small>{{ $commissionCustomer->customer->reference }}</small></td><td>{{ $commissionCustomer->cycles_count }}</td><td>{{ number_format((int) $commissionCustomer->monthly_commission, 0, ',', ' ') }} GNF</td></tr>@empty<tr><td colspan="3" class="db-commission-empty">Aucune tontine n’arrive à échéance ce mois-ci.</td></tr>@endforelse</tbody></table></div>@if($commissionCustomers->hasPages())<div class="db-commission-pager">{{ $commissionCustomers->onEachSide(1)->links('components.table-pagination') }}</div>@endif</div>
        </div><div wire:loading wire:target="commissionMonth,currentCommissionMonth" class="db-commission-loading" role="status">Calcul des commissions…</div>
    </section>

    <details class="db-history-toggle" wire:ignore.self>
        <summary><span>Mes gains mois par mois</span><span class="db-history-open">Voir mon historique <span aria-hidden="true">↗</span></span><span class="db-history-close">Masquer l’historique <span aria-hidden="true">↑</span></span></summary>
    <section class="db-panel" aria-labelledby="commission-history-title">
        <div class="db-commission-head">
            <div><span class="db-overline">VOS GAINS MOIS PAR MOIS</span><h2 id="commission-history-title">Historique des commissions · {{ $historyYear }}</h2></div>
            <div><span class="db-commission-month">Total acquis sur l’année</span><strong class="db-commission-value">{{ number_format($annualEarnedCommission, 0, ',', ' ') }} <small>GNF</small></strong></div>
        </div>
        <p class="db-commission-caption">Une mise journalière par cycle terminé et entièrement payé. Les paiements annulés sont exclus. Utilisez le sélecteur « Choisir un mois » ci-dessous pour changer d’année.</p>
        <div class="db-table-wrap"><table class="db-commission-table">
            <thead><tr><th scope="col">Mois</th><th scope="col">Cycles soldés</th><th scope="col">Commission prévue</th><th scope="col">Commission acquise</th><th scope="col">Détail</th></tr></thead>
            <tbody>
            @foreach ($commissionHistory as $history)
                <tr wire:key="commission-history-{{ $history['month'] }}">
                    <td><strong>{{ $history['label'] }}</strong>@if ($history['month'] === $commissionPeriod)<small>Mois sélectionné</small>@endif</td>
                    <td>{{ $history['settled'] }} / {{ $history['cycles'] }}</td>
                    <td>{{ number_format($history['expected'], 0, ',', ' ') }} GNF</td>
                    <td><strong>{{ number_format($history['earned'], 0, ',', ' ') }} GNF</strong></td>
                    <td><button class="btn secondary" type="button" wire:click="$set('commissionMonth', '{{ $history['month'] }}')" wire:loading.attr="disabled">Voir les clients</button></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr><th scope="row" colspan="2">Total {{ $historyYear }}</th><td>{{ number_format($annualExpectedCommission, 0, ',', ' ') }} GNF</td><td colspan="2">{{ number_format($annualEarnedCommission, 0, ',', ' ') }} GNF</td></tr></tfoot>
        </table></div>
        <p class="db-commission-rule">Les gains sont rattachés au mois de fin du cycle. Une régularisation ou une annulation recalcule cet historique ; il ne constitue pas un registre de versement de commissions.</p>
    </section>
    </details>
    <div class="db-main-grid">
        <section class="db-panel db-chart-panel" aria-labelledby="activity-title">
            <div class="db-panel-heading">
                <div><span class="db-overline">VOTRE ACTIVITÉ</span>
                    <h2 id="activity-title">Les cotisations de la semaine</h2>
                </div><span class="db-period">{{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m/Y') }}</span>
            </div>
            <div class="db-chart-summary"><strong>{{ number_format($weeklyTotal, 0, ',', ' ') }} <small>GNF</small></strong><span>Versements non annulés de la semaine</span></div>
            <div class="db-chart" role="img" aria-label="{{ $chartDescription }}">
                <div class="db-chart-axis" aria-hidden="true">
                    @foreach ([1, 0.75, 0.5, 0.25, 0] as $step)
                        <span>{{ number_format($chartMaximum === 1 ? 0 : $chartMaximum * $step, 0, ',', ' ') }}</span>
                    @endforeach
                </div>
                <div class="db-chart-columns" aria-hidden="true">
                    @foreach ($weekDays as $day)
                        <div class="db-chart-column {{ $day['amount'] > 0 && $day['amount'] === $chartMaximum ? 'db-chart-peak' : '' }}" title="{{ $day['date'] }} : {{ number_format($day['amount'], 0, ',', ' ') }} GNF">
                            <span class="db-bar-value">{{ $day['shortAmount'] }}</span>
                            <div class="db-bar" style="--value:{{ round($day['amount'] / $chartMaximum * 100, 2) }}%"></div>
                            <span class="db-bar-day">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @if ($weeklyTotal === 0)
                <p class="hint">Aucun versement enregistré cette semaine.</p>
            @endif
            <div class="db-chart-footer"><span><i aria-hidden="true"></i>Cotisations enregistrées ·
                    GNF</span><a href="{{ route('rapports') }}">Voir les rapports ↗</a></div>
        </section>
        <aside class="db-panel db-priorities" aria-labelledby="priorities-title">
            <div class="db-panel-heading">
                <div><span class="db-overline">GARDER LE FIL</span>
                    <h2 id="priorities-title">À surveiller</h2>
                </div><span class="db-count">{{ $priorityCount }}</span>
            </div><a class="db-priority" href="{{ route('retards') }}"><span class="db-priority-icon"
                    aria-hidden="true">◷</span>
                <div><strong>{{ $overdueCustomers }} client(s) en retard</strong>
                    <p>Consultez les versements à suivre et préparez vos relances.</p><span>Voir les retards
                        →</span>
                </div>
            </a><a class="db-priority" href="{{ route('calendrier') }}"><span
                    class="db-priority-icon db-priority-dark" aria-hidden="true">▦</span>
                <div><strong>{{ $upcomingEnds }} échéance(s) à venir</strong>
                    <p>Tontines actives arrivant à leur terme dans les sept prochains jours.</p><span>Ouvrir le
                        calendrier →</span>
                </div>
            </a>
            <div class="db-coach"><img src="{{ asset('ktontine-mascotte-v1.png') }}" alt="" width="60"
                    height="85">
                <div><strong>Un jour, une bonne habitude.</strong>
                    <p>Vérifiez vos cotisations avant de clôturer votre journée.</p>
                </div>
            </div>
        </aside>
    </div>
    <section class="db-shortcuts" aria-label="Accès rapides"><span>Votre prochain geste</span><a
            href="{{ route('clients') }}"><span aria-hidden="true">＋</span>Gérer les clients <span
                aria-hidden="true">↗</span></a><a href="{{ route('tontines') }}"><span
                aria-hidden="true">◎</span>Voir les tontines <span aria-hidden="true">↗</span></a><a
            href="{{ route('calendrier') }}"><span aria-hidden="true">▦</span>Consulter le calendrier
            <span aria-hidden="true">↗</span></a></section>
    <section class="db-panel db-transactions" aria-labelledby="transactions-title">
        <div class="db-panel-heading">
            <div><span class="db-overline">LES DERNIERS MOUVEMENTS</span>
                <h2 id="transactions-title">Dernières cotisations</h2>
            </div><a class="db-panel-link" href="{{ route('cotisations') }}">Tout consulter ↗</a>
        </div>

        <div class="db-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Référence</th>
                        <th scope="col">Date du versement</th>
                        <th scope="col">Montant</th>
                        <th scope="col">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lastCotisations as $lastCotisation)
                        <tr>
                            <td><div class="db-person"><span aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lastCotisation->tontine->customer->name, 0, 2)) }}</span><strong>{{ $lastCotisation->tontine->customer->name }}</strong></div></td>
                            <td>{{ $lastCotisation->tontine->reference }}</td>
                            <td>{{ $lastCotisation->payment_date->format('d/m/Y') }}</td>
                            <td class="db-money">{{ number_format($lastCotisation->amount, 0, ',', ' ') }} <small>GNF</small></td>
                            <td><span class="db-status {{ $lastCotisation->voided_at !== null ? 'db-status-cancelled' : '' }}">● {{ $lastCotisation->voided_at !== null ? 'Annulé' : 'Enregistré' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Aucune cotisation enregistrée pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $lastCotisations->onEachSide(1)->links('components.table-pagination') }}
    </section>
</div>
