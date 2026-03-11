<?php

namespace App\Filament\Resources\TenancyResource\Pages;

use App\Filament\Resources\TenancyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenancy extends CreateRecord
{
    protected static string $resource = TenancyResource::class;

    protected function afterCreate(): void
    {
        // Mark the property as occupied when a tenancy is created
        if ($this->record->status === 'active') {
            $this->record->property->update(['status' => 'occupied']);
        }
    }
}
