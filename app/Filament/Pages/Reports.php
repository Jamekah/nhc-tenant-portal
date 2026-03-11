<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 14;

    protected static ?string $title = 'Reports';

    protected static string $view = 'filament.pages.reports';

    public ?int $revenueYear = null;
    public ?int $revenueMonth = null;

    public function mount(): void
    {
        $this->revenueYear = (int) now()->format('Y');
        $this->revenueMonth = (int) now()->format('m');
    }

    public function getArrearsReportUrl(): string
    {
        return route('admin.pdf.arrears');
    }

    public function getRevenueReportUrl(): string
    {
        return route('admin.pdf.revenue', [
            'year' => $this->revenueYear,
            'month' => $this->revenueMonth,
        ]);
    }
}
