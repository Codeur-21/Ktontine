<div class="kt-page">
<style>
        .kt-page {
            max-width: 1400px;
            margin: auto
        }

        .kt-page * {
            box-sizing: border-box
        }

        .kt-page .heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
            flex-wrap: wrap
        }

        .kt-page h1 {
            font-size: 28px;
            margin: 0 0 8px;
            letter-spacing: -.7px
        }

        .kt-page p {
            color: #786c62;
            line-height: 1.6;
            font-size: 14px;
            margin: 0
        }

        .kt-page .btn {
            display: inline-block;
            border: 0;
            border-radius: 10px;
            background: #c64b08;
            color: white;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 600
        }

        .kt-page .secondary {
            background: white;
            color: #29231f;
            border: 1px solid #ccd5de
        }

        .kt-page .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 22px 0
        }

        .kt-page .panel,
        .kt-page .stat {
            background: #ffffffcf;
            border: 1px solid #29231f18;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 8px 28px #62350b06
        }

        .kt-page .stat small {
            display: block;
            color: #786c62;
            font-size: 12px
        }

        .kt-page .stat strong {
            display: block;
            font-size: 25px;
            margin: 12px 0
        }

        .kt-page .stat span {
            font-size: 12px;
            color: #9a3c06
        }

        .kt-page h2 {
            font-size: 17px;
            margin: 0 0 20px
        }

        .kt-page .toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px
        }

        .kt-page input,
        .kt-page select,
        .kt-page textarea {
            border: 1px solid #cbd5df;
            border-radius: 10px;
            background: white;
            padding: 12px;
            color: #29231f;
            font: inherit;
            font-size: 14px;
            max-width: 100%
        }

        .kt-page input:focus,
        .kt-page select:focus,
        .kt-page textarea:focus {
            outline: 2px solid #d66016;
            outline-offset: 2px
        }

        .kt-page .search {
            flex: 1;
            min-width: 180px
        }

        .kt-page .table-wrap {
            overflow-x: auto
        }

        .kt-page table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
            font-size: 13px
        }

        .kt-page th {
            text-align: left;
            color: #786c62;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
            padding: 14px 12px;
            background: #fff6ec
        }

        .kt-page td {
            padding: 17px 12px;
            border-bottom: 1px solid #29231f10
        }

        .kt-page .badge {
            display: inline-block;
            background: #ffeddc;
            color: #9a3c06;
            border-radius: 7px;
            padding: 5px 9px;
            font-size: 11px
        }

        .kt-page .green {
            background: #e6f5ec;
            color: #086345
        }

        .kt-page .red {
            background: #fff0ec;
            color: #a22c20
        }

        .kt-page .muted {
            color: #786c62
        }

        .kt-page .split {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            margin: 22px 0
        }

        .kt-page .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px
        }

        .kt-page .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px
        }

        .kt-page .field input,
        .kt-page .field select,
        .kt-page .field textarea {
            width: 100%
        }

        .kt-page .full {
            grid-column: 1/-1
        }

        .kt-page .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px
        }

        .kt-page .day {
            border: 1px solid #29231f15;
            border-radius: 10px;
            min-height: 76px;
            padding: 10px;
            font-size: 13px
        }

        .kt-page .paid {
            background: #e6f5ec;
            color: #086345
        }

        .kt-page .late {
            background: #fff0ec;
            color: #a22c20
        }

        .kt-page .day small {
            display: block;
            font-size: 10px;
            margin-top: 10px
        }

        .kt-page .bar-row {
            display: grid;
            grid-template-columns: 70px 1fr 95px;
            gap: 12px;
            align-items: center;
            margin: 17px 0;
            font-size: 12px
        }

        .kt-page .bar-track {
            height: 10px;
            background: #fff0e0;
            border-radius: 99px;
            overflow: hidden
        }

        .kt-page .bar {
            height: 100%;
            background: #d66016;
            border-radius: 99px
        }

        .kt-page .hint {
            background: #fff4e7;
            color: #80410c;
            border: 1px solid #ead3b9;
            border-radius: 12px;
            padding: 14px;
            font-size: 13px;
            margin: 18px 0
        }

        .kt-page .empty {
            padding: 32px;
            text-align: center;
            border: 1px dashed #ccd5de;
            border-radius: 14px;
            color: #786c62
        }

        .kt-page .check-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 15px 0;
            font-size: 14px
        }

        .kt-page .check-row input {
            accent-color: #c64b08
        }

        .kt-page details {
            border: 1px solid #29231f18;
            border-radius: 14px;
            margin-top: 20px;
            background: #fff;
            padding: 20px
        }

        .kt-page summary {
            cursor: pointer;
            font-size: 14px;
            font-weight: 600
        }

        .kt-page details form {
            margin-top: 20px
        }

        .kt-page button {
            cursor: pointer
        }

        .kt-page a:focus-visible,
        .kt-page button:focus-visible {
            outline: 3px solid #fb923c;
            outline-offset: 3px
        }

        .kt-page .foot {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
            font-size: 12px;
            color: #786c62
        }

        @media(max-width:1050px) {
            .kt-page .cards {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width:650px) {

            .kt-page .split,
            .kt-page .form-grid {
                grid-template-columns: 1fr
            }

            .kt-page .full {
                grid-column: auto
            }

            .kt-page .panel {
                padding: 16px
            }

            .kt-page h1 {
                font-size: 24px
            }

            .kt-page .cards {
                gap: 10px
            }

            .kt-page .stat {
                padding: 15px
            }

            .kt-page .stat strong {
                font-size: 20px
            }

            .kt-page .calendar {
                gap: 4px
            }

            .kt-page .day {
                min-height: 54px;
                padding: 7px
            }

            .kt-page .day small {
                display: none
            }
        }

        .kt-page .pay-panel{container-type:inline-size}.kt-page .pay-intro{display:flex;justify-content:space-between;align-items:start;gap:16px;margin-bottom:24px}.kt-page .pay-eyebrow{display:block;font-size:10px;font-weight:800;letter-spacing:1.8px;color:#b3470d;margin-bottom:9px}.kt-page .pay-intro h2{font-size:22px;letter-spacing:-.5px;margin:0 0 8px}.kt-page .pay-intro p{font-size:13px}.kt-page .pay-secure{font-size:10px;white-space:nowrap;border:1px solid #edddce;border-radius:20px;padding:7px 11px;color:#8d6a4c;background:#fffbf7}.kt-page .pay-context{display:grid;grid-template-columns:1fr 1fr;gap:18px;padding-bottom:24px;border-bottom:1px solid #ece5df}.kt-page .pay-context select{width:100%}.kt-page .pay-workspace{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.8fr);gap:30px;margin-top:26px;align-items:start}.kt-page .pay-calendar{min-width:0}.kt-page .pay-calendar h3,.kt-page .pay-summary h3{font-size:16px;margin:0 0 7px;font-weight:700}.kt-page .pay-calendar-top p{font-size:12px}.kt-page .pay-month-nav{display:flex;align-items:center;gap:12px;margin:20px 0 16px}.kt-page .pay-month-label{flex:1;min-width:0}.kt-page .pay-month-label input{width:100%;text-align:center;border-color:#e8dfd7;background:#faf8f5}.kt-page .pay-month-arrow{width:42px;height:42px;border:1px solid #e7ddd4;border-radius:10px;background:#fff;font-size:25px;line-height:1;color:#7d4b29;cursor:pointer}.kt-page .pay-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:7px}.kt-page .pay-weekday{text-align:center;font-size:10px;font-weight:700;color:#8a7a6d;padding:3px 0 8px}.kt-page .pay-day{min-height:57px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;border:1px solid #e7ded5;border-radius:10px;background:#fff;color:#45362b;cursor:pointer;font:inherit;transition:background .12s,border-color .12s}.kt-page .pay-day strong{font-size:14px}.kt-page .pay-day span{height:12px;font-size:8px;line-height:12px;color:#b25828}.kt-page .pay-day.is-overdue{background:#fffaf5}.kt-page .pay-day:disabled{cursor:default;background:#f4f4f3;border-color:transparent;color:#b9b0a8}.kt-page .pay-day.is-paid{background:#eef6f0;color:#356348;border-color:#dcebe1}.kt-page .pay-day.is-paid span{color:#356348}.kt-page .pay-day.is-today{border-color:#c64b08}.kt-page .pay-day.is-selected{border-color:#c64b08;background:#c64b08;color:#fff;box-shadow:0 3px 7px #c64b0815}.kt-page .pay-day.is-selected span{color:white;font-size:12px}.kt-page .pay-day:not(:disabled):hover{border-color:#c64b08}.kt-page .pay-legend{display:flex;gap:16px;flex-wrap:wrap;margin:15px 0;font-size:10px;color:#827164}.kt-page .pay-legend span{display:flex;align-items:center;gap:5px}.kt-page .pay-legend i{display:inline-block;width:8px;height:8px;border-radius:3px}.kt-page .pay-legend .selected{background:#c64b08}.kt-page .pay-legend .paid{background:#b5d4bf}.kt-page .pay-legend .overdue{background:#f7d2b3}.kt-page .pay-calendar-actions{display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;padding:0 0 16px;border-bottom:1px solid #eee6df}.kt-page .pay-text-button{background:none;border:0;padding:5px 0;font:inherit;font-size:11px;color:#aa4009;cursor:pointer;font-weight:600}.kt-page .pay-text-button.muted{color:#8f8175}.kt-page button:disabled{opacity:.6}.kt-page .pay-add-period{font:inherit;font-size:12px;font-weight:600;color:#784726;background:none;border:0;padding:16px 0 0;cursor:pointer}.kt-page .pay-period-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:16px}.kt-page .pay-period-fields input{width:100%}.kt-page .pay-period-fields p{grid-column:1/-1;font-size:11px}.kt-page .pay-period-fields .btn{grid-column:1/-1}.kt-page .pay-summary{padding:23px;border:1px solid #eaded2;border-radius:16px;background:#fffaf5}.kt-page .pay-summary h3{font-size:17px}.kt-page .pay-period-list{list-style:none;padding:0;margin:18px 0;max-height:200px;overflow:auto}.kt-page .pay-period-list li{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:11px 0;border-bottom:1px solid #eee1d5}.kt-page .pay-period-list strong{display:block;font-size:11px}.kt-page .pay-period-list span{font-size:10px;color:#8d7764}.kt-page .pay-period-list button{min-width:32px;min-height:32px;border:0;border-radius:8px;background:#f3e9de;color:#8e6343;font-size:18px;cursor:pointer}.kt-page .pay-total{border-top:1px solid #e8d7c7;padding-top:20px;margin-top:22px}.kt-page .pay-total>span{font-size:12px;color:#876b53}.kt-page .pay-total strong{display:block;margin:7px 0;font-size:29px;letter-spacing:-1px;color:#291c12}.kt-page .pay-total small{font-size:12px;letter-spacing:0;color:#9b6844}.kt-page .pay-summary .pay-stake-note{font-size:10px;line-height:1.7;margin:0 0 22px;color:#937c69}.kt-page .pay-summary input{width:100%;background:white}.kt-page .pay-submit{width:100%;margin-top:22px;min-height:46px;cursor:pointer}.kt-page .pay-cancel{display:block;text-align:center;margin-top:14px;font-size:12px;color:#8b7260;text-decoration:none}.kt-page .pay-empty-selection{font-size:12px;line-height:1.7;margin:18px 0;color:#9d8673}.kt-page .pay-start{text-align:center;padding:45px 20px 22px}.kt-page .pay-start>span{font-size:32px;color:#d58952}.kt-page .pay-start h3{font-size:15px;margin:15px 0 8px}.kt-page .pay-start p{font-size:12px}.pay-sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.kt-page .pay-panel [x-cloak]{display:none!important}.kt-page .pay-panel button:focus-visible{outline:2px solid #c64b08;outline-offset:3px}
        .dark .kt-page .pay-summary{background:#261e18;border-color:#55402e}.dark .kt-page .pay-total strong,.dark .kt-page .pay-summary h3{color:#fff4e9}.dark .kt-page .pay-day{background:#261e18;border-color:#55402e;color:#eadaca}.dark .kt-page .pay-day:disabled{background:#201c18;color:#6f6257}.dark .kt-page .pay-day.is-paid{background:#20382b;color:#bbdcc6}.dark .kt-page .pay-day.is-selected{background:#c64b08;color:#fff}.dark .kt-page .pay-month-label input,.dark .kt-page .pay-month-arrow,.dark .kt-page .pay-summary input{background:#261e18;color:#eadaca;border-color:#55402e}.dark .kt-page .pay-secure{background:#261e18;border-color:#55402e;color:#e1b88f}
        @container(max-width:700px){.kt-page .pay-workspace{grid-template-columns:1fr;gap:24px}}
        @media(max-width:850px){.kt-page .pay-workspace{grid-template-columns:1fr;gap:24px}.kt-page .pay-summary{padding:20px}.kt-page .pay-period-list{max-height:260px}}@media(max-width:500px){.kt-page .pay-context{grid-template-columns:1fr}.kt-page .pay-intro{display:block}.kt-page .pay-secure{display:none}.kt-page .pay-intro h2{font-size:19px}.kt-page .pay-days{gap:4px}.kt-page .pay-day{min-height:49px;border-radius:7px}.kt-page .pay-day strong{font-size:12px}.kt-page .pay-period-fields{grid-template-columns:1fr}.kt-page .pay-summary{padding:17px}}
    </style>
<div class="heading">
    <div><h1>Cotisations</h1><p>Enregistrez les versements et suivez chaque opération.</p></div>
    <a class="btn secondary" href="{{ route('calendrier') }}" wire:navigate>Voir le calendrier</a>
</div>
@if (session()->has('success'))
    <div class="operation-flash" wire:key="operation-flash-{{ \Illuminate\Support\Str::uuid() }}" x-data="{ visible: true }" x-show="visible" role="status" aria-live="polite">
        <span class="operation-flash-icon" aria-hidden="true">✓</span><div><strong>Opération réussie</strong><p>{{ session('success') }}</p></div><button type="button" @click="visible = false" aria-label="Fermer le message">×</button>
    </div>
@endif
<section class="panel pay-panel" aria-labelledby="payment-form-title">
    <div class="pay-intro"><div><span class="pay-eyebrow">ENCAISSEMENT</span><h2 id="payment-form-title">Quelles journées souhaitez-vous régler ?</h2><p>Choisissez les jours réellement payés. Ils peuvent être séparés, sur un ou plusieurs mois.</p></div><span class="pay-secure">Un paiement par journée</span></div>
    <form wire:submit="save" novalidate>
        <div class="pay-context">
            <div class="field"><label for="customerId">Client</label><select id="customerId" wire:model.live="customerId" aria-invalid="{{ $errors->has('customerId') ? 'true' : 'false' }}"><option value="">Choisir un client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select>@error('customerId')<p class="field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="tontineId">Tontine</label><select id="tontineId" wire:model.live="tontineId" aria-invalid="{{ $errors->has('tontineId') ? 'true' : 'false' }}"><option value="">Choisir une tontine</option>@foreach($tontines as $tontine)<option value="{{ $tontine->id }}">{{ $tontine->reference }} · {{ number_format($tontine->daily_amount, 0, ',', ' ') }} GNF / jour</option>@endforeach</select>@error('tontineId')<p class="field-error" role="alert">{{ $message }}</p>@enderror</div>
        </div>
        @if($selectedTontine)
            <div class="pay-workspace" wire:key="selection-{{ $selectedTontine->id }}">
                <section class="pay-calendar" aria-label="Sélection des journées">
                    <div class="pay-calendar-top"><div><h3>Journées à régler</h3><p>Cycle du {{ $selectedTontine->start_date->format('d/m/Y') }} au {{ $selectedTontine->end_date->format('d/m/Y') }}</p></div></div>
                    <div class="pay-month-nav"><button type="button" class="pay-month-arrow" wire:click="changeMonth(-1)" aria-label="Mois précédent">‹</button><label class="pay-month-label" for="selectedMonth"><span class="pay-sr-only">Mois affiché</span><input id="selectedMonth" type="month" wire:model.live="selectedMonth" aria-invalid="{{ $errors->has('selectedMonth') ? 'true' : 'false' }}"></label><button type="button" class="pay-month-arrow" wire:click="changeMonth(1)" aria-label="Mois suivant">›</button></div>
                    @error('selectedMonth')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    <div class="pay-days" role="group" aria-label="Cliquez sur les journées à payer">
                        @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $weekday)<span class="pay-weekday" aria-hidden="true">{{ $weekday }}</span>@endforeach
                        @foreach($calendarDays as $day)
                            @if($day['day'] === null)<span class="pay-day-empty" aria-hidden="true"></span>
                            @else
                                <button type="button" wire:key="day-{{ $selectedTontine->id }}-{{ $day['date'] }}" wire:click="toggleDay('{{ $day['date'] }}')" aria-pressed="{{ $day['selected'] ? 'true' : 'false' }}" aria-label="{{ $day['date'] }} · {{ $day['selected'] ? 'Sélectionné' : ($day['paid'] ? 'Déjà payé' : ($day['eligible'] ? 'À régler' : 'Indisponible')) }}" @disabled(! $day['eligible'] && ! $day['selected']) @class(['pay-day', 'is-paid' => $day['paid'], 'is-selected' => $day['selected'], 'is-overdue' => $day['overdue'], 'is-today' => $day['today']])><strong>{{ $day['day'] }}</strong><span>{{ $day['selected'] ? '✓' : ($day['paid'] ? 'Payé' : ($day['overdue'] ? 'Retard' : '')) }}</span></button>
                            @endif
                        @endforeach
                    </div>
                    <div class="pay-legend"><span><i class="selected"></i>Sélectionné</span><span><i class="paid"></i>Déjà payé</span><span><i class="overdue"></i>En retard</span></div>
                    <div class="pay-calendar-actions"><button type="button" class="pay-text-button" wire:click="selectMonth">Sélectionner les impayés du mois</button><button type="button" class="pay-text-button muted" wire:click="clearSelection" @disabled(count($batchDates) === 0)>Tout désélectionner</button></div>
                    <div class="pay-period-editor" x-data="{ open: false }">
                        <button type="button" class="pay-add-period" @click="open = !open" :aria-expanded="open" aria-controls="period-editor">＋ Ajouter une période</button>
                        <div id="period-editor" class="pay-period-fields" x-show="open" x-cloak>
                            <div class="field"><label for="periodStart">Du</label><input id="periodStart" type="date" wire:model="periodStart" min="{{ $selectedTontine->start_date->toDateString() }}" max="{{ min(now()->toDateString(), $selectedTontine->end_date->toDateString()) }}">@error('periodStart')<p class="field-error" role="alert">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="periodEnd">Au</label><input id="periodEnd" type="date" wire:model="periodEnd" min="{{ $selectedTontine->start_date->toDateString() }}" max="{{ min(now()->toDateString(), $selectedTontine->end_date->toDateString()) }}">@error('periodEnd')<p class="field-error" role="alert">{{ $message }}</p>@enderror</div>
                            <button type="button" class="btn secondary" wire:click="addPeriod">Ajouter</button><p>Les jours déjà payés sont exclus. Vous pouvez ajouter plusieurs périodes.</p>
                        </div>
                    </div>
                    @error('selectedDates')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    @foreach($errors->get('selectedDates.*') as $messages)@foreach($messages as $message)<p class="field-error" role="alert">{{ $message }}</p>@endforeach @endforeach
                    @error('paymentDate')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    @error('paymentEndDate')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </section>
                <aside class="pay-summary" aria-label="Récapitulatif du paiement">
                    <span class="pay-eyebrow">VOTRE SÉLECTION</span><h3>{{ count($batchDates) }} journée(s) sélectionnée(s)</h3>
                    @if(count($selectedPeriods) > 0)
                        <ul class="pay-period-list">@foreach($selectedPeriods as $period)<li wire:key="period-{{ $period['start'] }}-{{ $period['end'] }}"><div><strong>{{ \Carbon\CarbonImmutable::parse($period['start'])->format('d/m/Y') }}@if($period['end'] !== $period['start']) – {{ \Carbon\CarbonImmutable::parse($period['end'])->format('d/m/Y') }}@endif</strong><span>{{ $period['count'] }} jour(s)</span></div><button type="button" wire:click="removePeriod('{{ $period['start'] }}', '{{ $period['end'] }}')" aria-label="Retirer la période du {{ $period['start'] }} au {{ $period['end'] }}">×</button></li>@endforeach</ul>
                    @else<p class="pay-empty-selection">Cliquez sur une journée ou ajoutez une période pour commencer.</p>@endif
                    <div class="pay-total" role="status" aria-live="polite"><span>Total à encaisser</span><strong>{{ number_format((int) $amount, 0, ',', ' ') }} <small>GNF</small></strong></div>
                    <p class="pay-stake-note">Les mises déjà fixées pour chaque journée sont conservées.</p>
                    @error('amount')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    <div class="field"><label for="receivedDate">Versement reçu le</label><input id="receivedDate" type="date" max="{{ now()->toDateString() }}" wire:model="receivedDate" aria-invalid="{{ $errors->has('receivedDate') ? 'true' : 'false' }}">@error('receivedDate')<p class="field-error" role="alert">{{ $message }}</p>@enderror</div>
                    @if($batchPaidCount > 0)<p class="field-error" role="alert">{{ $batchPaidCount }} journée(s) sélectionnée(s) viennent d’être réglées. Retirez-les de votre sélection.</p>@endif
                    <button type="submit" class="btn pay-submit" @disabled(count($batchDates) === 0 || $batchPaidCount > 0) wire:loading.attr="disabled" wire:target="save,toggleDay,selectMonth,addPeriod,clearSelection,removePeriod"><span wire:loading.remove wire:target="save">Confirmer le paiement</span><span wire:loading wire:target="save">Enregistrement…</span></button>
                    <a class="pay-cancel" href="{{ route('calendrier') }}" wire:navigate>Annuler</a>
                </aside>
            </div>
        @else<div class="pay-start"><span aria-hidden="true">▦</span><h3>Commencez par choisir un client et sa tontine</h3><p>Vous pourrez ensuite choisir librement les journées à régler.</p></div>@endif
    </form>
</section>
<section class="panel" id="historique" style="margin-top:22px">
    <h2>Historique des cotisations</h2>
    <div class="toolbar"><label>Mois cotisé <input type="month" wire:model.live="filterMonth" aria-label="Filtrer par mois cotisé"></label><button class="btn secondary" type="button" wire:click="$set('filterMonth', '')">Tous les mois</button><input type="search" class="search" wire:model.live.debounce.300ms="search" placeholder="Client ou référence de tontine" aria-label="Rechercher une cotisation"><input type="date" wire:model.live="filterDate" aria-label="Filtrer par date réelle du versement"></div>
    <p class="hint" style="margin-bottom:16px">{{ $payments->whereNull('voided_at')->count() }} journée(s) réglée(s) affichée(s) · Total affiché hors annulations : <strong>{{ number_format($displayedTotal, 0, ',', ' ') }} GNF</strong>. Regroupement par mois de la journée cotisée, sur cette page.</p>
    <div class="table-wrap"><table><thead><tr><th>Référence</th><th>Client</th><th>Tontine</th><th>Journée cotisée</th><th>Versement reçu le</th><th>Montant</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
    @forelse($paymentMonths as $month => $monthlyPayments)
        <tr><th colspan="8" scope="colgroup" style="background:#fff0e3;padding:14px;text-align:left">{{ ucfirst(\Carbon\CarbonImmutable::createFromFormat('!Y-m', $month)->locale('fr')->translatedFormat('F Y')) }} · {{ $monthlyPayments->whereNull('voided_at')->count() }} journée(s) réglée(s) · {{ number_format($monthlyPayments->whereNull('voided_at')->sum('amount'), 0, ',', ' ') }} GNF</th></tr>
        @foreach($monthlyPayments as $payment)
        <tr wire:key="payment-{{ $payment->id }}">
            <td>CT-{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</td><td>{{ $payment->tontine->customer->name }}</td><td>{{ $payment->tontine->reference }}</td>
            <td>{{ $payment->day->due_date->format('d/m/Y') }}</td><td>{{ $payment->payment_date->format('d/m/Y') }}</td><td>{{ number_format($payment->amount, 0, ',', ' ') }} GNF</td>
            <td><span class="badge {{ $payment->voided_at ? 'red' : 'green' }}">{{ $payment->voided_at ? 'Annulé' : 'Enregistré' }}</span></td>
            <td>@if(! $payment->voided_at)<button class="tontine-delete-action" type="button" wire:click="confirmCancellation({{ $payment->id }})">Annuler le versement</button>@else<span class="muted">Annulé</span>@endif</td>
        </tr>
        <tr wire:key="payment-history-{{ $payment->id }}"><td colspan="8">
            <details style="margin:0;padding:12px;white-space:normal"><summary>Voir la trace des opérations</summary>
                <ol style="display:grid;gap:12px;padding-left:20px;margin-top:16px">
                    @foreach($payment->auditHistory() as $event)
                        <li><strong>{{ $event['action'] === 'cancelled' ? 'Annulation' : 'Enregistrement' }}</strong>
                            · {{ $event['at'] ? \Illuminate\Support\Carbon::parse($event['at'])->timezone(config('app.timezone'))->format('d/m/Y à H:i') : 'Date non renseignée' }}
                            · {{ $event['user_name'] ?? 'Auteur non renseigné' }}
                            · {{ number_format($event['amount'], 0, ',', ' ') }} GNF
                            <p>Journée : {{ $event['due_date'] }} · Versement : {{ $event['payment_date'] }}</p>
                            @if($event['reason'])<p>Motif : {{ $event['reason'] }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </details>
        </td></tr>
        @endforeach
    @empty<tr><td colspan="8">Aucune cotisation trouvée.</td></tr>@endforelse
    </tbody></table></div>{{ $payments->onEachSide(1)->links('components.table-pagination') }}
</section>
<dialog class="tontine-delete-dialog" x-data x-effect="if ($wire.cancellingId !== null) { if (! $el.open) $el.showModal() } else { if ($el.open) $el.close() }" @cancel.prevent="$wire.dismissCancellation()" aria-labelledby="cancel-payment-title" aria-describedby="cancel-payment-description">
    <div class="tontine-delete-content">
        <button class="tontine-delete-close" type="button" wire:click="dismissCancellation" aria-label="Fermer la confirmation">×</button>
        <h2 id="cancel-payment-title">Annuler ce versement ?</h2>
        <p id="cancel-payment-description">Le montant sera retiré des totaux encaissés. La trace de l’opération et le motif seront conservés.</p>
        @if($paymentToCancel)<div class="tontine-delete-selection"><strong>{{ $paymentToCancel->tontine->customer->name }} · {{ number_format($paymentToCancel->amount, 0, ',', ' ') }} GNF</strong><small>Journée du {{ $paymentToCancel->day->due_date->format('d/m/Y') }}</small></div>@endif
        <form wire:submit="cancelPayment" novalidate>
            <div class="field"><label for="cancellationReason">Motif de l’annulation</label><textarea id="cancellationReason" wire:model="cancellationReason" rows="3" maxlength="500" aria-invalid="{{ $errors->has('cancellationReason') ? 'true' : 'false' }}" @error('cancellationReason') aria-describedby="cancellationReason-error" @enderror></textarea>
                @error('cancellationReason')<p class="field-error" id="cancellationReason-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="tontine-delete-buttons"><button class="tontine-delete-cancel" type="button" wire:click="dismissCancellation" autofocus>Conserver le versement</button><button class="tontine-delete-confirm" type="submit" wire:loading.attr="disabled" wire:target="cancelPayment">Confirmer l’annulation</button></div>
        </form>
    </div>
</dialog>
</div>
