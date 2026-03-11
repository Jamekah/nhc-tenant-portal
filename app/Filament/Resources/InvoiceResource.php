<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Tenancy;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 12;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['tenancy.tenant', 'tenancy.property']);

        $user = auth()->user();
        if ($user && $user->hasRole('admin') && $user->region_id) {
            $query->whereHas('tenancy.property', function (Builder $q) use ($user) {
                $q->where('region_id', $user->region_id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\Select::make('tenancy_id')
                            ->relationship('tenancy', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tenant->name} — {$record->property->property_code}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Tenancy'),
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->default(fn () => 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT)),
                    ])->columns(2),

                Forms\Components\Section::make('Billing Period')
                    ->schema([
                        Forms\Components\DatePicker::make('billing_period_start')
                            ->required()
                            ->label('Period Start'),
                        Forms\Components\DatePicker::make('billing_period_end')
                            ->required()
                            ->after('billing_period_start')
                            ->label('Period End'),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()
                            ->label('Due Date'),
                        Forms\Components\DateTimePicker::make('issued_at')
                            ->default(now())
                            ->label('Issued At'),
                    ])->columns(2),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('amount_due')
                            ->numeric()
                            ->prefix('PGK')
                            ->required()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $amountPaid = (float) ($get('amount_paid') ?? 0);
                                $set('balance', max(0, (float) $state - $amountPaid));
                            }),
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('PGK')
                            ->default(0)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $amountDue = (float) ($get('amount_due') ?? 0);
                                $set('balance', max(0, $amountDue - (float) $state));
                            }),
                        Forms\Components\TextInput::make('balance')
                            ->numeric()
                            ->prefix('PGK')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(3),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft'),
                    ]),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(2000),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('tenancy.tenant.name')
                    ->searchable()
                    ->sortable()
                    ->label('Tenant'),
                Tables\Columns\TextColumn::make('tenancy.property.property_code')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_period_start')
                    ->date('M Y')
                    ->label('Period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->money('PGK')
                    ->sortable()
                    ->label('Due'),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('PGK')
                    ->sortable()
                    ->label('Paid'),
                Tables\Columns\TextColumn::make('balance')
                    ->money('PGK')
                    ->sortable()
                    ->label('Balance')
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Invoice $record) => route('admin.pdf.invoice', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markSent')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record): bool => $record->status === 'draft')
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => 'sent',
                        'issued_at' => $record->issued_at ?? now(),
                    ])),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record): bool => in_array($record->status, ['sent', 'partially_paid', 'overdue']))
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => 'paid',
                        'amount_paid' => $record->amount_due,
                        'balance' => 0,
                    ])),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record): bool => !in_array($record->status, ['paid', 'cancelled']))
                    ->action(fn (Invoice $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
