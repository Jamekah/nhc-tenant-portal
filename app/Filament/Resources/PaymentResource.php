<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 13;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['invoice', 'tenancy.tenant', 'tenancy.property', 'recorder']);

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
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->options(
                                Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
                                    ->with('tenancy.tenant')
                                    ->get()
                                    ->mapWithKeys(fn ($inv) => [
                                        $inv->id => "{$inv->invoice_number} — {$inv->tenancy->tenant->name} (Balance: PGK " . number_format($inv->balance, 2) . ")"
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $invoice = Invoice::find($state);
                                    if ($invoice) {
                                        $set('tenancy_id', $invoice->tenancy_id);
                                        $set('amount', $invoice->balance);
                                    }
                                }
                            }),
                        Forms\Components\Hidden::make('tenancy_id'),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('PGK')
                            ->required()
                            ->minValue(0.01)
                            ->label('Payment Amount'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Method')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'cheque' => 'Cheque',
                                'online_gateway' => 'Online Gateway',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->maxLength(100)
                            ->helperText('Bank reference, cheque number, or receipt ID'),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->required()
                            ->default(now())
                            ->label('Payment Date'),
                    ])->columns(3),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('completed'),
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
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Invoice'),
                Tables\Columns\TextColumn::make('tenancy.tenant.name')
                    ->searchable()
                    ->sortable()
                    ->label('Tenant'),
                Tables\Columns\TextColumn::make('tenancy.property.property_code')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PGK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString()),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Paid At'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'cheque' => 'Cheque',
                        'online_gateway' => 'Online Gateway',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('downloadReceipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Payment $record) => route('admin.pdf.receipt', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $record): bool => $record->status === 'completed'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
