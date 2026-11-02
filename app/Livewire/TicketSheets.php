<?php

namespace App\Livewire;

use App\Models\Setting;
use App\TicketSheet;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TicketSheets extends Component
{
    public string $businessName = '';

    public string $phone = '';

    public string $address = '';

    public string $dailyAmount = '';

    public string $month = '';

    public string $footer = '';

    public bool $watermark = true;

    public string $watermarkOpacity = '10';

    public string $watermarkSize = '22';

    public string $color = 'blue';

    public string $style = 'classic';

    public string $layout = '8';

    public string $pageCount = '1';

    public bool $numberCards = false;

    public string $startNumber = '1';

    /** @var array<string, mixed> */
    #[Locked]
    public array $preview = [];

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        auth()->user()->activeTenantId();
        $this->fill(TicketSheet::defaults(auth()->user()->tenant->name, Setting::current()));
        $this->generate();
    }

    public function chooseTemplate(string $template): void
    {
        abort_unless(auth()->check(), 403);
        auth()->user()->activeTenantId();
        $templates = self::templates();
        abort_unless(array_key_exists($template, $templates), 422);
        $this->style = $template;
        $this->color = $templates[$template]['color'];
        $this->layout = $templates[$template]['layout'];
        $this->generate();
    }

    /** @return array<string, array{label: string, description: string, layout: string, color: string}> */
    private static function templates(): array
    {
        return [
            'classic' => ['label' => 'Groupe solidaire', 'description' => 'Un en-tête bleu graphique et une présentation claire pour votre groupe.', 'layout' => '8', 'color' => 'blue'],
            'orange' => ['label' => 'Tontine espoir', 'description' => 'Une carte crème et orange, chaleureuse et facile à compléter.', 'layout' => '8', 'color' => 'orange'],
            'minimal' => ['label' => 'Tontine progrès', 'description' => 'Un en-tête vert profond, un motif végétal et des détails soignés.', 'layout' => '12', 'color' => 'green'],
        ];
    }

    public function updated(string $property): void
    {
        if (array_key_exists($property, TicketSheet::rules())) {
            $this->generate();
        }
    }

    public function generate(): void
    {
        abort_unless(auth()->check(), 403);
        auth()->user()->activeTenantId();
        $this->preview = $this->validate(TicketSheet::rules(), TicketSheet::messages());
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);
        auth()->user()->activeTenantId();

        return view('livewire.ticket-sheets', [
            'design' => TicketSheet::design($this->preview),
            'templates' => self::templates(),
            'palettes' => TicketSheet::PALETTES,
            'printUrl' => route('tickets.sheets.print', $this->preview),
        ])->layout('components.layouts.backend');
    }
}
