<section class="ticket-sheet" data-color="{{ $design['color'] }}" data-style="{{ $design['style'] }}" data-count="{{ $design['cardsPerPage'] }}" style="--watermark-opacity:{{ $design['watermarkOpacity'] / 100 }};--watermark-size:{{ $design['watermarkSize'] }}%;--ticket-accent:{{ $design['palette']['accent'] }};--ticket-tint:{{ $design['palette']['tint'] }};--ticket-light:{{ $design['palette']['light'] }};--sheet-columns:{{ $design['columns'] }};--sheet-rows:{{ $design['rows'] }}" aria-label="Feuille A4 {{ $pageIndex + 1 }}">
    @for($cardIndex = 0; $cardIndex < (($singleCard ?? false) ? 1 : $design['cardsPerPage']); $cardIndex++)
        <article class="blank-ticket" aria-label="Ticket vierge {{ $pageIndex * $design['cardsPerPage'] + $cardIndex + 1 }}">
            <header class="bt-header">
                <div class="bt-emblem">@include('tickets.card-icon', ['icon' => $design['style'] === 'minimal' ? 'leaf' : 'group'])</div>
                <div class="bt-heading"><span class="bt-eyebrow">TONTINE</span><h2>{{ $design['businessName'] }}</h2><div class="bt-title">CARTE TONTINE</div></div>
                <span class="bt-month-badge">@include('tickets.card-icon', ['icon' => 'calendar'])<span>{{ $design['monthBadge'] ?: 'Mois : ........' }}</span></span>
            </header>
            <div class="bt-body">
                @if($design['watermark'])<div class="bt-watermark" aria-hidden="true"><img src="{{ $platformLogoUrl }}" alt="" width="1254" height="1254" loading="eager" decoding="sync"></div>@endif
                @if($design['style'] === 'minimal')<div class="bt-leaf-watermark" aria-hidden="true">@include('tickets.card-icon', ['icon' => 'leaf'])</div>@endif
                <div class="bt-member-grid">
                    <div class="bt-member-col">
                        <div class="bt-info-row">@include('tickets.card-icon', ['icon' => 'person'])<div><span class="bt-label">Nom du membre</span><strong class="bt-write-line">................................</strong></div></div>
                        <div class="bt-info-row">@include('tickets.card-icon', ['icon' => 'phone'])<div><span class="bt-label">Téléphone</span><strong class="bt-write-line">................................</strong></div></div>
                    </div>
                    <div class="bt-member-col">
                        <div class="bt-info-row">@include('tickets.card-icon', ['icon' => 'calendar'])<div><span class="bt-label">Période</span><strong>{{ $design['monthLabel'] ?: '................................' }}</strong></div></div>
                        <div class="bt-info-row">@include('tickets.card-icon', ['icon' => 'coins'])<div><span class="bt-label">Montant / jour</span><strong>{{ $design['dailyAmount'] ? number_format((int) $design['dailyAmount'], 0, ',', ' ') : '................' }} GNF</strong></div></div>
                    </div>
                </div>
                <div class="bt-reference"><span>Inscrit le : .... / .... / ........</span><span>Carte n° @if($design['numberCards'])<strong>{{ str_pad((string) ($design['startNumber'] + $pageIndex * $design['cardsPerPage'] + $cardIndex), 6, '0', STR_PAD_LEFT) }}</strong>@else............@endif</span></div>
                <table class="bt-calendar" aria-label="Cases de cotisation de 1 à 31"><thead><tr>@foreach(['LUN', 'MAR', 'MER', 'JEU', 'VEN', 'SAM', 'DIM'] as $weekday)<th scope="col">{{ $weekday }}</th>@endforeach</tr></thead><tbody>
                    @for($row = 0; $row < $design['calendarWeeks']; $row++)<tr>@for($column = 0; $column < 7; $column++)@php($day = $row * 7 + $column - $design['calendarOffset'] + 1)<td @class(['bt-empty' => $day < 1 || $day > $design['daysInMonth']])>@if($day >= 1 && $day <= $design['daysInMonth'])<span>{{ $day }}</span>@else<span aria-hidden="true">&nbsp;</span>@endif</td>@endfor</tr>@endfor
                </tbody></table>
                <div class="bt-legend"><span><i class="paid"></i>Payé</span><span><i class="today"></i>Aujourd’hui</span><span><i class="late"></i>En retard</span><span><i class="unpaid"></i>Non payé</span></div>
                <div class="bt-summary">
                    <div class="bt-summary-item">@include('tickets.card-icon', ['icon' => 'coins'])<div><span class="bt-label">Total payé</span><strong class="bt-total-paid">............ GNF</strong></div></div>
                    <div class="bt-summary-item">@include('tickets.card-icon', ['icon' => 'hourglass'])<div><span class="bt-label">Reste à payer</span><strong class="bt-total-due">............ GNF</strong></div></div>
                    <div class="bt-summary-item">@include('tickets.card-icon', ['icon' => 'trend'])<div><span class="bt-label">Statut</span><span class="bt-status">................</span></div></div>
                </div>
                <div class="bt-bottom">@if($design['phone'] || $design['address'])<span class="bt-contact">{{ $design['phone'] }}@if($design['phone'] && $design['address']) · @endif{{ $design['address'] }}</span>@endif @if($design['footer'])<span class="bt-note">{{ $design['footer'] }}</span>@endif<span class="bt-footer">Ktontine · Carte à conserver <span>Cachet / visa : ............</span></span></div>
            </div>
        </article>
    @endfor
</section>
