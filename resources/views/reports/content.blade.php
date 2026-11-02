<div class="cards">
    <div class="stat"><small>Encaissements nets d’annulations</small><strong>{{ number_format($report['summary']['paid'], 0, ',', ' ') }} GNF</strong><span>{{ $report['summary']['payments'] }} versement(s) actif(s)</span></div>
    <div class="stat"><small>Versements actuellement annulés</small><strong>{{ number_format($report['summary']['cancelled'], 0, ',', ' ') }} GNF</strong><span>{{ $report['summary']['cancelledPayments'] }} versement(s) de la période</span></div>
    <div class="stat"><small>Cycles clôturés sur la période</small><strong>{{ $report['summary']['closedCycles'] }}</strong><span>{{ number_format($report['summary']['plannedCommission'], 0, ',', ' ') }} GNF de commissions prévues</span></div>
    <div class="stat"><small>Échéances de la période en retard</small><strong>{{ number_format($report['summary']['overdueAmount'], 0, ',', ' ') }} GNF</strong><span>{{ $report['summary']['overdueDays'] }} journée(s) encore impayée(s)</span></div>
</div>
<p class="hint">Les encaissements suivent la date réelle du versement. Les annulations et les retards reflètent l’état actuel des données, au {{ $report['generatedAt']->format('d/m/Y à H:i') }}. Les commissions sont prévues sur les cycles clôturés ; leur perception n’est pas enregistrée séparément.</p>
<section class="panel" aria-labelledby="report-clients-title">
    <h2 id="report-clients-title">Synthèse par client</h2>
    <p class="muted" style="margin-bottom:16px">{{ $report['summary']['clients'] }} client(s) concerné(s) par un versement, une clôture ou un retard.</p>
    <div class="table-wrap"><table><thead><tr><th scope="col">Client</th><th scope="col">Versements actifs</th><th scope="col">Encaissé</th><th scope="col">Annulé</th><th scope="col">Cycles clôturés</th><th scope="col">Commissions prévues</th><th scope="col">Retards actuels</th></tr></thead><tbody>
    @forelse($clients as $row)
        <tr wire:key="report-client-{{ $row['id'] }}"><td><strong>{{ $row['name'] }}</strong><br><small class="muted">{{ $row['reference'] }}</small></td><td>{{ $row['payments'] }}</td><td>{{ number_format($row['paid'], 0, ',', ' ') }} GNF</td><td>{{ number_format($row['cancelled'], 0, ',', ' ') }} GNF<br><small class="muted">{{ $row['cancelledPayments'] }} versement(s)</small></td><td>{{ $row['closedCycles'] }}</td><td>{{ number_format($row['plannedCommission'], 0, ',', ' ') }} GNF</td><td>{{ $row['overdueDays'] }} jour(s)<br>{{ number_format($row['overdueAmount'], 0, ',', ' ') }} GNF</td></tr>
    @empty<tr><td colspan="7"><div class="empty">Aucune activité sur cette période.</div></td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="panel" aria-labelledby="report-daily-title" style="margin-top:22px">
    <h2 id="report-daily-title">Versements par date de réception</h2>
    <div class="table-wrap"><table><thead><tr><th scope="col">Date</th><th scope="col">Versements actifs</th><th scope="col">Encaissé</th><th scope="col">Actuellement annulé</th></tr></thead><tbody>
    @forelse($report['daily'] as $day)
        <tr><td>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d/m/Y') }}</td><td>{{ $day['payments'] }}</td><td>{{ number_format($day['paid'], 0, ',', ' ') }} GNF</td><td>{{ number_format($day['cancelled'], 0, ',', ' ') }} GNF</td></tr>
    @empty<tr><td colspan="4"><div class="empty">Aucun versement reçu sur cette période.</div></td></tr>@endforelse
    </tbody></table></div>
</section>
