<?php

namespace App\Filament\Resources\TenancyResource\Pages;

use App\Filament\Resources\TenancyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenancies extends ListRecords
{
    protected static string $resource = TenancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
