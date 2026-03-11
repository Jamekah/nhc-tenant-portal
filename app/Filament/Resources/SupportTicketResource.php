<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Tenant Management';

    protected static ?int $navigationSort = 15;

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?string $recordTitleAttribute = 'ticket_number';

    public static function getNavigationBadge(): ?string
    {
        $count = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $urgentCount = SupportTicket::where('status', 'open')
            ->where('priority', 'urgent')
            ->count();

        return $urgentCount > 0 ? 'danger' : 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Region-scope for admin users
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
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('Ticket #')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('tenancy_id')
                            ->label('Tenancy')
                            ->relationship('tenancy', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                $record->tenant->name . ' — ' . ($record->property->property_code ?? 'N/A')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('category')
                            ->options([
                                'maintenance' => 'Maintenance',
                                'plumbing' => 'Plumbing',
                                'electrical' => 'Electrical',
                                'structural' => 'Structural',
                                'general_query' => 'General Query',
                                'billing' => 'Billing',
                            ])
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Assignment')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign to Admin')
                            ->options(function () {
                                return User::role(['super_admin', 'admin'])
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Unassigned'),

                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed'])),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('tenancy.tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenancy.property.property_code')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state) => match ($state) {
                        'maintenance' => 'primary',
                        'plumbing' => 'info',
                        'electrical' => 'warning',
                        'structural' => 'danger',
                        'billing' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subject')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'maintenance' => 'Maintenance',
                        'plumbing' => 'Plumbing',
                        'electrical' => 'Electrical',
                        'structural' => 'Structural',
                        'general_query' => 'General Query',
                        'billing' => 'Billing',
                    ]),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignee', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign to Admin')
                            ->options(function () {
                                return User::role(['super_admin', 'admin'])
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function (SupportTicket $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => $record->status === 'open' ? 'in_progress' : $record->status,
                        ]);
                    })
                    ->visible(fn (SupportTicket $record) => in_array($record->status, ['open', 'in_progress'])),

                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (SupportTicket $record) {
                        $record->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]);
                    })
                    ->visible(fn (SupportTicket $record) => in_array($record->status, ['open', 'in_progress'])),

                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (SupportTicket $record) {
                        $record->update([
                            'status' => 'closed',
                            'resolved_at' => $record->resolved_at ?? now(),
                        ]);
                    })
                    ->visible(fn (SupportTicket $record) => $record->status !== 'closed'),

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
            'index' => Pages\ListSupportTickets::route('/'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
            'view' => Pages\ViewSupportTicket::route('/{record}'),
        ];
    }
}
