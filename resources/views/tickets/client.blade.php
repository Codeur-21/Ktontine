<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Carte tontine {{ $customer->reference }} | Ktontine</title>
    <style>
        :root{color-scheme:light;font-family:Arial,Helvetica,sans-serif;color:#241c16;background:#f3f4f6;font-size:14px}*{box-sizing:border-box}body{margin:0;padding:24px 14px}.toolbar,.screen-info,.details{max-width:760px;margin:0 auto 20px}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.toolbar p,.muted{font-size:12px;color:#78695c;line-height:1.6}.actions{display:flex;gap:8px;flex-wrap:wrap}.button{padding:11px 15px;border:1px solid #ded5cd;border-radius:10px;background:#fff;color:#33261c;text-decoration:none;cursor:pointer;font:inherit;font-size:13px;font-weight:bold}.primary{background:#c64b08;border-color:#c64b08;color:#fff}a:focus-visible,button:focus-visible,summary:focus-visible{outline:3px solid #f97316;outline-offset:3px}.screen-info{padding:16px 20px;border:1px solid #e4dcd4;border-radius:12px;background:#fff}.screen-info h1{font-size:18px;margin:0 0 6px}.screen-info p{margin:4px 0}.cards{display:grid;gap:16px;justify-items:center;max-width:760px;margin:auto}.tontine-card{width:100mm;max-width:100%;padding:10px 11px 8px;border:1px solid #38291e;border-top:3px solid #c64b08;background:#fffaf2;break-inside:avoid;overflow-wrap:anywhere;font-size:9px;line-height:1.25}.card-header{text-align:center;border-bottom:1px solid #d4bda5;padding-bottom:5px}.business-name{margin:0;font-size:16px;line-height:1.1;font-weight:900;text-transform:uppercase;letter-spacing:-.4px}.card-subline{display:flex;justify-content:center;align-items:center;gap:8px;margin:3px 0}.card-subline>strong{font-size:9px}.card-title{margin:0;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:#b34007}.contact{font-size:9px;line-height:1.3;margin:2px 0}.card-reference{margin:3px 0 0;font-size:8px;font-weight:bold}.card-status{font-weight:normal;font-size:8px}.card-fields{display:grid;grid-template-columns:1fr 1fr;gap:2px 8px;margin:4px 0;font-size:9px;line-height:1.3}.field{border-bottom:1px dotted #bda58d;padding-bottom:2px;min-width:0}.field.full{grid-column:1/-1}.field strong{font-weight:700}.card-calendar{border-collapse:collapse;width:100%;table-layout:fixed;margin:6px 0 3px}.card-calendar caption{text-align:left;font-size:8px;font-weight:bold;margin-bottom:3px}.card-calendar td{border:1px solid #6a4e37;text-align:center;height:17px;padding:1px 0;font-size:10px;font-weight:bold}.day-mark{display:inline;font-size:9px;margin-left:2px}.state-paid{background:#bbf7d0;color:#14532d;print-color-adjust:exact;-webkit-print-color-adjust:exact}.state-paid .day-mark{display:inline-flex;align-items:center;justify-content:center;width:12px;height:12px;margin-left:1px;vertical-align:middle;border-radius:3px;background:#15803d;color:#fff;print-color-adjust:exact;-webkit-print-color-adjust:exact}.paid-check{display:block;width:10px;height:10px}.state-cancelled{background:#f0e9e1;color:#755a46}.state-overdue{background:#ffe5d4;color:#923406}.state-outside,.state-unavailable{color:#a69b90;background:#f3efe9}.state-unavailable .day-number{text-decoration:line-through}.is-today{outline:1px solid #c64b08;outline-offset:-1px}.legend{font-size:7px;line-height:1.3;margin:3px 0 5px;color:#67513f}.card-totals{display:grid;grid-template-columns:1fr 1fr;gap:4px 8px;font-size:8px;margin-top:5px}.card-totals div{border-bottom:1px dotted #bda58d;padding-bottom:2px}.card-totals strong{font-size:9px;white-space:nowrap}.card-commission{grid-column:1/-1;font-size:7px}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:7px;font-size:8px}.signature{border-top:1px dotted #8d735b;padding-top:3px;min-height:12px}.card-note{font-size:7px;line-height:1.3;margin:5px 0 0;white-space:pre-line}.card-footer{border-top:1px solid #d4bda5;margin-top:5px;padding-top:4px;font-size:7px;line-height:1.3}.card-footer strong{color:#a43a05}.card-footer p{margin:2px 0}.details{margin-top:24px;background:white;border:1px solid #e4dcd4;border-radius:12px;padding:18px}.details summary{font-weight:bold;cursor:pointer}.detail-cycle{margin-top:20px}.detail-cycle h2{font-size:16px}.table-wrap{overflow-x:auto}table.history{border-collapse:collapse;width:100%;font-size:11px}.history th,.history td{padding:9px 7px;text-align:left;border-bottom:1px solid #eee3d9}.history th{background:#fff4e7}.cancelled{color:#9a7560}.empty{text-align:center;background:white;padding:24px;border:1px dashed #d8c9bb;border-radius:12px}.print-help{text-align:center;font-size:11px;line-height:1.6;color:#78695c;max-width:680px;margin:18px auto}.totals-overview{font-size:12px;line-height:1.9}.totals-overview span{display:block}
        @page{size:{{ $settings->ticket_paper === 'A5' ? 'A5' : 'A4' }};margin:{{ $settings->ticket_paper === 'A5' ? '8' : '14' }}mm}
        @media print{html,body{background:#fff!important}body{padding:0}.toolbar,.screen-info,.details,.print-help{display:none!important}.cards{display:block;max-width:none}.tontine-card{width:100mm;max-width:100mm;margin:0 auto 4mm;padding:2.5mm;border-width:1px;border-top-width:3px;box-shadow:none;break-inside:avoid;page-break-inside:avoid;print-color-adjust:exact;-webkit-print-color-adjust:exact}.card-calendar td{height:4.5mm}}
    </style>
</head>
<body @class(['paper-a5' => $settings->ticket_paper === 'A5'])>
<nav class="toolbar" aria-label="Actions du ticket">
    <div><strong>Carte mensuelle de tontine</strong><p>Un format compact à imprimer et à conserver.</p></div>
    <div class="actions"><a class="button" href="{{ route('clients') }}">Retour aux clients</a><button class="button primary" type="button" onclick="window.print()">Imprimer / PDF</button></div>
</nav>
<section class="screen-info">
    <h1>{{ $customer->name }} · {{ ucfirst($currentMonth) }}</h1>
    <p class="muted">{{ $customer->address ?: 'Adresse non renseignée' }} · {{ $cycles->count() }} tontine(s) · Édité par {{ $operator }} le {{ $generatedAt->format('d/m/Y à H:i') }}</p>
    <p class="muted">{{ $settings->address }} @if($settings->contact_email) · {{ $settings->contact_email }}@endif</p>
    <form method="GET" action="{{ route('clients.ticket', ['customer' => $customer->id]) }}" class="actions">
        <label for="ticket-month">Mois des cotisations</label>
        <input id="ticket-month" name="month" type="month" value="{{ $selectedMonth }}" required>
        <button class="button" type="submit">Afficher</button>
    </form>
    <p class="muted">Les cartes portent sur les journées cotisées du mois affiché. Les montants du cycle complet figurent sur chaque carte. Le dernier mois cotisé est affiché par défaut ; vous pouvez choisir un autre mois.</p>
</section>
<main class="cards">
    @forelse ($cycles as $cycle)
        <article class="tontine-card" aria-label="Carte mensuelle {{ $cycle['tontine']->reference }}">
            <header class="card-header">
                <h2 class="business-name">{{ $tenant->name }}</h2>
                <div class="card-subline"><h3 class="card-title">Carte tontine</h3><strong>{{ $customer->reference }} / {{ $cycle['tontine']->id }}</strong>@if($cycle['tontine']->status === 'closed')<span class="card-status">Clôturée</span>@endif</div>
                <p class="contact">@if($settings->contact_phone)Tél. : {{ $settings->contact_phone }}@endif</p>

            </header>
            <section class="card-fields" aria-label="Identification du client">
                <div class="field full">Nom et prénom(s) : <strong>{{ $customer->name }}</strong></div>
                <div class="field">Tél. : <strong>{{ $customer->phone ?: 'Non renseigné' }}</strong></div>
                <div class="field">Date d’enregistrement : <strong>{{ $customer->created_at?->format('d/m/Y') ?? 'Non renseignée' }}</strong></div>
                <div class="field">Mois affiché : <strong>{{ $currentMonth }}</strong></div>
                <div class="field">Versement par jour : <strong>{{ number_format($cycle['tontine']->daily_amount, 0, ',', ' ') }} GNF</strong></div>
            </section>
            <p class="card-reference">{{ $cycle['tontine']->reference }} · Cycle du {{ $cycle['tontine']->start_date->format('d/m/Y') }} au {{ $cycle['tontine']->end_date->format('d/m/Y') }}</p>
            <table class="card-calendar" aria-label="Calendrier des cotisations de {{ $currentMonth }}">
                <tbody>
                    @foreach ($cycle['cardCalendar'] as $row)
                        <tr>
                            @foreach ($row as $day)
                                <td @class(['state-'.$day['state'], 'is-today' => $day['today']]) title="{{ $day['day'] }} : {{ match($day['state']) { 'paid' => 'Payé', 'cancelled' => 'Annulé', 'overdue' => 'En retard', 'due' => 'À payer', 'unpaid' => 'Non réglé', default => 'Hors cycle ou jour inexistant' } }}">
                                    <span class="day-number">{{ $day['day'] }}</span>
                                    <span class="day-mark" aria-hidden="true">@if($day['state'] === 'paid')<svg class="paid-check" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 6 2.5 2.5L10 3"/></svg>@else{{ match($day['state']) { 'cancelled' => '×', 'overdue' => '!', default => '' } }}@endif</span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="legend">✓ Payé · × Annulé · ! Retard · Gris : hors cycle / inexistant.</p>
            <section class="card-totals" aria-label="Montants du mois">
                <div>Montant total versé : <strong>{{ number_format($cycle['monthlyPaid'], 0, ',', ' ') }} GNF</strong></div>
                <div>Reste à cotiser : <strong>{{ number_format($cycle['monthlyRemaining'], 0, ',', ' ') }} GNF</strong></div>
                @if($settings->show_commission)<div class="card-commission">Commission prévue, séparée : {{ number_format($cycle['tontine']->commission_amount, 0, ',', ' ') }} GNF</div>@endif
            </section>
            <section class="card-totals" aria-label="Total du cycle">
                <div>Total du cycle versé : <strong>{{ number_format($cycle['paid'], 0, ',', ' ') }} GNF</strong></div>
                <div>Solde du cycle : <strong>{{ number_format($cycle['remaining'], 0, ',', ' ') }} GNF</strong></div>
                <div>Journées réglées : <strong>{{ $cycle['paidDays'] }} / {{ $cycle['totalDays'] }}</strong></div>
                <div>Montant en retard : <strong>{{ number_format($cycle['overdueAmount'], 0, ',', ' ') }} GNF</strong></div>
            </section>
            @if($settings->show_signatures)<section class="signatures" aria-label="Signatures"><div class="signature">Signature du client</div><div class="signature">Signature / cachet du gestionnaire</div></section>@endif
            @if($settings->ticket_footer)<p class="card-note">{{ $settings->ticket_footer }}</p>@endif
            <footer class="card-footer"><strong>Votre suivi avec Ktontine</strong> · {{ $generatedAt->format('d/m/Y') }}<p>Conservez cette carte. Versements annulés exclus du total.</p></footer>
        </article>
    @empty
        <div class="empty">Aucune tontine rattachée à ce client pour le moment.<p class="muted">{{ $tenant->name }} · {{ $settings->address }} · {{ $settings->contact_phone }} · {{ $settings->contact_email }}</p></div>
    @endforelse
</main>
<details class="details" open>
    <summary>Consulter les totaux complets et l’historique</summary>
    <div class="totals-overview"><span>Cotisations prévues, cycles complets : {{ number_format($totals['expected'], 0, ',', ' ') }} GNF</span><span>Total encaissé : {{ number_format($totals['paid'], 0, ',', ' ') }} GNF</span><span>Solde des cotisations : {{ number_format($totals['remaining'], 0, ',', ' ') }} GNF</span><span>Dont montant en retard : {{ number_format($totals['overdue'], 0, ',', ' ') }} GNF</span></div>
    @foreach ($cycles as $cycle)
        <section class="detail-cycle"><h2>{{ $cycle['tontine']->reference }} · Historique des versements</h2><p class="muted">Cycle du {{ $cycle['tontine']->start_date->format('d/m/Y') }} au {{ $cycle['tontine']->end_date->format('d/m/Y') }} · Montant prévu ce mois : {{ number_format($cycle['monthlyExpected'], 0, ',', ' ') }} GNF.</p>
            <p class="muted">{{ $cycle['paidDays'] }} / {{ $cycle['totalDays'] }} journées réglées · {{ $cycle['overdueDays'] }} en retard. Solde du cycle : {{ number_format($cycle['remaining'], 0, ',', ' ') }} GNF.</p>
            @if($cycle['overpayment'] > 0)<p class="muted">Excédent de versement : {{ number_format($cycle['overpayment'], 0, ',', ' ') }} GNF.</p>@endif
            <div class="table-wrap"><table class="history"><thead><tr><th>Référence</th><th>Journée cotisée</th><th>Versement reçu le</th><th>Montant</th><th>Statut</th></tr></thead><tbody>
                @forelse($cycle['payments'] as $row)
                    <tr @class(['cancelled' => $row['payment']->voided_at !== null])><td>CT-{{ str_pad((string) $row['payment']->id, 6, '0', STR_PAD_LEFT) }}</td><td>{{ $row['dueDate']->format('d/m/Y') }}</td><td>{{ $row['payment']->payment_date->format('d/m/Y') }}</td><td>{{ number_format($row['payment']->amount, 0, ',', ' ') }} GNF</td><td>{{ $row['payment']->voided_at ? 'Annulé' : 'Enregistré' }}</td></tr>
                @empty<tr><td colspan="5">Aucun versement enregistré pour cette tontine.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    @endforeach
</details>
<p class="print-help">Carte compacte de 10 cm de large · Papier {{ $settings->ticket_paper }}. Imprimez à 100 % (taille réelle), puis découpez autour de la carte. Seules les cartes sont imprimées. Pour un PDF, choisissez « Enregistrer au format PDF » ; désactivez les en-têtes et pieds de page du navigateur.</p>
</body>
</html>
