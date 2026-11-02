<?php

namespace App\Livewire;

use App\ActivityReport;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Rapport extends Component
{
    use WithPagination;

    public string $startDate = '';

    public string $endDate = '';

    #[Locked]
    public string $reportStart = '';

    #[Locked]
    public string $reportEnd = '';

    public function mount(): void
    {
        $this->startDate = match (Setting::current()->report_period) {
            'week' => now()->startOfWeek()->toDateString(),
            'today' => now()->toDateString(),
            default => now()->startOfMonth()->toDateString(),
        };
        $this->endDate = now()->toDateString();
        $this->generate();
    }

    public function generate(): void
    {
        abort_unless(auth()->check(), 403);
        auth()->user()->activeTenantId();
        $data = $this->validate(ActivityReport::rules(), ActivityReport::messages());
        $this->reportStart = $data['startDate'];
        $this->reportEnd = $data['endDate'];
        $this->resetPage();
    }

    public function currentMonth(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generate();
    }

    public function render(ActivityReport $builder): View
    {
        $report = $builder->build($this->reportStart, $this->reportEnd);
        $page = max(1, (int) $this->getPage());
        $clients = new LengthAwarePaginator($report['clients']->forPage($page, 15)->values(), $report['clients']->count(), 15, $page, ['path' => request()->url()]);

        return view('livewire.rapport', compact('report', 'clients'))->layout('components.layouts.backend');
    }
}
