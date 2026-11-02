<div class="kt-page" x-data x-on:client-editing.window="$nextTick(() => $refs.customerName.focus())">
    <style>
.customer-row-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.customer-row-actions .btn{white-space:nowrap;font-size:12px;padding:9px 12px}.customer-edit-action{display:inline-flex!important;align-items:center;gap:7px;color:#a3430d!important;border-color:#e8c4ab!important;background:#fff6ed!important}.customer-edit-action svg{width:15px;height:15px}.customer-edit-action:disabled{opacity:.5}

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

            .error {
                font-color: red;
                background-color: red;
            }
        }
    </style>
    <!-- Template visuel : données fictives. Raccorder les actions, filtres et formulaires à Livewire. -->
    <div class="heading">
        <div>
            <h1>Clients</h1>
            <p>Organisez vos clients et retrouvez leurs coordonnées.</p>
        </div><span class="badge">{{ $clients->total() }} client(s)</span>

    </div>
    @if (session()->has('success'))
        <div class="client-toast" wire:key="client-notification-{{ (string) \Illuminate\Support\Str::uuid() }}"
            x-data="{ show: true, timer: null, paused: false, start() { clearTimeout(this.timer);
                    this.paused = false;
                    this.timer = setTimeout(() => this.show = false, 6000); }, init() { this.start(); }, destroy() { clearTimeout(this.timer); } }" x-show="show" x-cloak x-transition.opacity.duration.200ms
            @mouseenter="clearTimeout(timer); paused = true" @mouseleave="start()"
            @focusin="clearTimeout(timer); paused = true" @focusout="start()" :class="{ 'is-paused': paused }"
            role="status" aria-live="polite" aria-atomic="true">
            <div class="client-toast-body">
                <span class="client-toast-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 12 4 4 8-9" />
                    </svg></span>
                <div class="client-toast-copy"><span class="client-toast-brand">KTONTINE · CLIENTS</span>
                    <h3>{{ session('customer_action') === 'updated' ? 'Client modifié' : 'Client enregistré' }}</h3>
                    <p>{{ session('success') === 'Nouveau client ajouter' ? 'Le nouveau client a été ajouté avec succès.' : session('success') }}
                    </p>
                </div>
                <button class="client-toast-close" type="button" @click="clearTimeout(timer); show = false"
                    aria-label="Fermer la notification"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                        <path d="m6 6 12 12M6 18 18 6" />
                    </svg></button>
            </div>
            <div class="client-toast-track" aria-hidden="true"><span></span></div>
        </div>
    @endif
    <section class="panel client-create-panel" aria-labelledby="client-create-title">
        <div class="client-create-heading"><span class="client-create-icon" aria-hidden="true">{{ $editingId ? '✎' : '＋' }}</span>
            <div>
                <h2 id="client-create-title">{{ $editingId ? 'Modifier le client' : 'Ajouter un client' }}</h2>
                <p>Renseignez ses coordonnées. Les erreurs éventuelles apparaissent sous chaque champ.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="validation-summary" role="alert" aria-live="assertive">
                <span class="validation-summary-icon" aria-hidden="true">!</span>
                <div>
                    <strong>Quelques informations sont à corriger.</strong>
                    <p>
                        Vérifiez les champs signalés ci-dessous, puis essayez à nouveau.
                    </p>
                </div>
            </div>
        @endif
        <form wire:submit="register" novalidate>
            <div class="form-grid">
                <div class="field">
                    <label for="name">Nom complet</label>
                    <input id="name" x-ref="customerName" wire:model="name" type="text" placeholder=""
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @error('name') aria-describedby="name-error" @enderror>

                    @error('name')
                        <p id="name-error" class="field-error" role="alert">{{ $message }}</p>
                    @enderror

                </div>
                <div class="field"><label for="phone">Téléphone</label>
                    <input id="phone" wire:model="phone" type="tel" placeholder=""
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        @error('phone') aria-describedby="phone-error" @enderror>
                    @error('phone')
                        <p id="phone-error" class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field"><label for="address">Adresse</label>
                    <input id="address" wire:model="address" type="text" placeholder=""
                        aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                        @error('address') aria-describedby="address-error" @enderror>

                    @error('address')
                        <p id="address-error" class="field-error" role="alert">{{ $message }}</p>
                    @enderror

                </div>
                <div class="field"><label for="status">Statut</label>
                    <select id="status" wire:model="status"
                        aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                        @error('status') aria-describedby="status-error" @enderror>

                        @foreach (\App\Models\Customer::data() as $data)
                            <option>{{ $data }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p id="status-error" class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="kt-form-actions">
                <button type="button" wire:click="annuler" class="kt-form-cancel">
                    Annuler
                </button>
                <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="register">{{ $editingId ? 'Enregistrer les modifications' : 'Enregistrer' }}</button>
            </div>
        </form>
    </section>
    <section class="panel">
        <h2>Liste des clients</h2>
        <div class="toolbar">
            <label for="search-clients" class="muted">Rechercher</label>
            <input class="search" wire:model.live="search" id="search-clients" type="search"
                placeholder="Nom ou téléphone">


        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Tontines</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr wire:key="customer-{{ $client->id }}">
                            <td>{{ $client->reference }}</td>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->phone }}</td>
                            <td>{{ $client->tontines_count }}</td>
                            <td><span
                                    class="{{ $client->status === 'Actif' ? 'badge green' : 'badge red' }}">{{ $client->status }}</span>
                            </td>
                            <td><div class="customer-row-actions"><button type="button" class="btn secondary customer-edit-action" wire:click="edit({{ $client->id }})" wire:loading.attr="disabled" wire:target="edit" aria-label="Modifier {{ $client->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 4 5 5M4 20l5-1L21 7a2.1 2.1 0 0 0-4-4L5 15Z"/></svg>Modifier</button><a class="btn secondary" href="{{ route('clients.ticket', ['customer' => $client->id]) }}" target="_blank" rel="noopener" aria-label="Ouvrir le ticket de {{ $client->name }} (nouvel onglet)">Ticket / Imprimer</a></div></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Aucun client enregistré</td>

                        </tr>
                    @endforelse
                    {{--  <tr>
                        <td>CL-002</td>
                        <td>Ibrahima Barry</td>
                        <td>620 00 00 02</td>
                        <td>1</td>
                        <td><span class="badge green">Actif</span></td>
                    </tr>
                    <tr>
                        <td>CL-003</td>
                        <td>Fatou Camara</td>
                        <td>620 00 00 03</td>
                        <td>1</td>
                        <td><span class="badge red">Inactif</span></td>
                    </tr>  --}}
                </tbody>
            </table>
        </div>
        {{ $clients->onEachSide(1)->links('components.table-pagination') }}
    </section>

    {{--  <div>
            @if ($errors->any())

             foreach($errors->all() as $error)

                <span>{{$error}}</span>
             @endforeach

             @endif


        </div>  --}}

</div>
