<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System Management';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.system-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Settings')
                    ->description('Configure default payment and billing parameters.')
                    ->schema([
                        Forms\Components\Select::make('default_payment_frequency')
                            ->options([
                                'fortnightly' => 'Fortnightly',
                                'monthly' => 'Monthly',
                            ])
                            ->default('fortnightly')
                            ->label('Default Payment Frequency'),
                        Forms\Components\TextInput::make('overdue_threshold_days')
                            ->numeric()
                            ->default(14)
                            ->minValue(1)
                            ->maxValue(90)
                            ->suffix('days')
                            ->label('Overdue Threshold')
                            ->helperText('Number of days after due date before an invoice is marked overdue.'),
                        Forms\Components\TextInput::make('arrears_threshold_days')
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->maxValue(180)
                            ->suffix('days')
                            ->label('Arrears Threshold')
                            ->helperText('Number of days overdue before a tenant is flagged as in arrears.'),
                    ])->columns(3),

                Forms\Components\Section::make('Invoice Settings')
                    ->description('Configure invoice generation defaults.')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_prefix')
                            ->default('INV')
                            ->maxLength(10)
                            ->label('Invoice Number Prefix'),
                        Forms\Components\TextInput::make('invoice_due_days')
                            ->numeric()
                            ->default(14)
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('days')
                            ->label('Payment Due Days')
                            ->helperText('Number of days from invoice issue date until payment is due.'),
                    ])->columns(2),

                Forms\Components\Section::make('System Defaults')
                    ->description('General system configuration.')
                    ->schema([
                        Forms\Components\TextInput::make('organization_name')
                            ->default('National Housing Corporation')
                            ->maxLength(255)
                            ->label('Organization Name'),
                        Forms\Components\TextInput::make('support_email')
                            ->email()
                            ->default('support@nhc.gov.pg')
                            ->maxLength(255)
                            ->label('Support Email'),
                        Forms\Components\TextInput::make('support_phone')
                            ->default('+675 321 7000')
                            ->maxLength(20)
                            ->label('Support Phone'),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'label' => str($key)->replace('_', ' ')->title()->toString(),
                    'group' => $this->getGroupForKey($key),
                    'type' => is_numeric($value) ? 'integer' : 'string',
                ]
            );
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    private function getGroupForKey(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'default_payment') || str_contains($key, 'threshold') => 'payment',
            str_starts_with($key, 'invoice') => 'invoice',
            default => 'general',
        };
    }
}
