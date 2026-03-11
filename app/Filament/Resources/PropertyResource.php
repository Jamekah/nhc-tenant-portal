<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Property Management';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->hasRole('admin') && $user->region_id) {
            $query->where('region_id', $user->region_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Property Details')
                    ->schema([
                        Forms\Components\TextInput::make('property_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->helperText('Unique property identifier, e.g. NCD-RES-001'),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'residential' => 'Residential',
                                'institutional' => 'Institutional',
                                'land' => 'Land',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'available' => 'Available',
                                'occupied' => 'Occupied',
                                'under_maintenance' => 'Under Maintenance',
                                'decommissioned' => 'Decommissioned',
                            ])
                            ->required()
                            ->default('available'),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('suburb')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('province')
                            ->required()
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Specifications & Rent')
                    ->schema([
                        Forms\Components\TextInput::make('bedrooms')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('size_sqm')
                            ->numeric()
                            ->minValue(0)
                            ->label('Size (sqm)'),
                        Forms\Components\TextInput::make('monthly_rent')
                            ->numeric()
                            ->prefix('PGK')
                            ->required()
                            ->minValue(0),
                        Forms\Components\Select::make('payment_frequency')
                            ->options([
                                'fortnightly' => 'Fortnightly',
                                'monthly' => 'Monthly',
                            ])
                            ->required()
                            ->default('fortnightly'),
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
                Tables\Columns\TextColumn::make('property_code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable()
                    ->label('Region'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'residential' => 'info',
                        'institutional' => 'warning',
                        'land' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('suburb')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->money('PGK')
                    ->sortable()
                    ->label('Rent (PGK)'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'info',
                        'under_maintenance' => 'warning',
                        'decommissioned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('tenancies_count')
                    ->counts('tenancies')
                    ->label('Tenancies')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region_id')
                    ->relationship('region', 'name')
                    ->label('Region')
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'residential' => 'Residential',
                        'institutional' => 'Institutional',
                        'land' => 'Land',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'under_maintenance' => 'Under Maintenance',
                        'decommissioned' => 'Decommissioned',
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
