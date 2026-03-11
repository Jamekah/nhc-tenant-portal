<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenancyResource\Pages;
use App\Models\Tenancy;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenancyResource extends Resource
{
    protected static ?string $model = Tenancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Tenant Management';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Tenancies';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['tenant', 'property.region']);

        $user = auth()->user();
        if ($user && $user->hasRole('admin') && $user->region_id) {
            $query->whereHas('property', function (Builder $q) use ($user) {
                $q->where('region_id', $user->region_id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tenancy Details')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(
                                User::role('client')
                                    ->where('is_active', true)
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('property_id')
                            ->relationship('property', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->property_code} - {$record->title}")
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Lease Terms')
                    ->schema([
                        Forms\Components\DatePicker::make('lease_start')
                            ->required()
                            ->label('Lease Start Date'),
                        Forms\Components\DatePicker::make('lease_end')
                            ->label('Lease End Date')
                            ->after('lease_start'),
                        Forms\Components\TextInput::make('agreed_rent')
                            ->numeric()
                            ->prefix('PGK')
                            ->required()
                            ->minValue(0)
                            ->label('Agreed Rent'),
                        Forms\Components\Select::make('payment_frequency')
                            ->options([
                                'fortnightly' => 'Fortnightly',
                                'monthly' => 'Monthly',
                            ])
                            ->required()
                            ->default('fortnightly'),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'terminated' => 'Terminated',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Select::make('tenant_status')
                            ->options([
                                'in_good_standing' => 'In Good Standing',
                                'overdue' => 'Overdue',
                                'in_arrears' => 'In Arrears',
                            ])
                            ->required()
                            ->default('in_good_standing'),
                    ])->columns(2),

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
                Tables\Columns\TextColumn::make('tenant.name')
                    ->searchable()
                    ->sortable()
                    ->label('Tenant'),
                Tables\Columns\TextColumn::make('property.property_code')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->label('Property'),
                Tables\Columns\TextColumn::make('property.region.name')
                    ->label('Region')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agreed_rent')
                    ->money('PGK')
                    ->sortable()
                    ->label('Rent (PGK)'),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->badge()
                    ->sortable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('lease_start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_end')
                    ->date()
                    ->sortable()
                    ->placeholder('Ongoing'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'terminated' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tenant_status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'in_good_standing' => 'success',
                        'overdue' => 'warning',
                        'in_arrears' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->label('Tenant Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\SelectFilter::make('tenant_status')
                    ->options([
                        'in_good_standing' => 'In Good Standing',
                        'overdue' => 'Overdue',
                        'in_arrears' => 'In Arrears',
                    ])
                    ->label('Tenant Status'),
                Tables\Filters\SelectFilter::make('payment_frequency')
                    ->options([
                        'fortnightly' => 'Fortnightly',
                        'monthly' => 'Monthly',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListTenancies::route('/'),
            'create' => Pages\CreateTenancy::route('/create'),
            'edit' => Pages\EditTenancy::route('/{record}/edit'),
        ];
    }
}
