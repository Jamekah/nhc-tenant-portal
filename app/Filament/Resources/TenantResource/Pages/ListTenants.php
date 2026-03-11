<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('csvImport')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain'])
                        ->required()
                        ->helperText('CSV format: name, email, phone, property_code, rent'),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/public/' . $data['csv_file']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('File not found')
                            ->danger()
                            ->send();
                        return;
                    }

                    $csv = Reader::createFromPath($filePath, 'r');
                    $csv->setHeaderOffset(0);

                    $imported = 0;
                    $errors = [];

                    foreach ($csv->getRecords() as $index => $record) {
                        try {
                            $name = trim($record['name'] ?? '');
                            $email = trim($record['email'] ?? '');
                            $phone = trim($record['phone'] ?? '');
                            $propertyCode = trim($record['property_code'] ?? '');
                            $rent = (float) ($record['rent'] ?? 0);

                            if (empty($name) || empty($email)) {
                                $errors[] = "Row {$index}: Missing name or email";
                                continue;
                            }

                            // Create or find user
                            $user = User::where('email', $email)->first();
                            if (!$user) {
                                $user = User::create([
                                    'name' => $name,
                                    'email' => $email,
                                    'phone' => $phone,
                                    'password' => Hash::make('tenant2026!'),
                                    'is_active' => true,
                                ]);
                                $user->assignRole('client');
                            }

                            // Find property and create tenancy
                            if (!empty($propertyCode)) {
                                $property = Property::where('property_code', $propertyCode)->first();
                                if ($property) {
                                    Tenancy::create([
                                        'tenant_id' => $user->id,
                                        'property_id' => $property->id,
                                        'lease_start' => now(),
                                        'agreed_rent' => $rent > 0 ? $rent : $property->monthly_rent,
                                        'payment_frequency' => $property->payment_frequency,
                                        'status' => 'active',
                                        'tenant_status' => 'in_good_standing',
                                    ]);
                                    $property->update(['status' => 'occupied']);
                                } else {
                                    $errors[] = "Row {$index}: Property '{$propertyCode}' not found";
                                }
                            }

                            $imported++;
                        } catch (\Exception $e) {
                            $errors[] = "Row {$index}: " . $e->getMessage();
                        }
                    }

                    // Clean up uploaded file
                    @unlink($filePath);

                    $message = "Imported {$imported} tenants.";
                    if (count($errors) > 0) {
                        $message .= " Errors: " . count($errors);
                    }

                    Notification::make()
                        ->title($message)
                        ->body(count($errors) > 0 ? implode("\n", array_slice($errors, 0, 5)) : null)
                        ->success()
                        ->send();
                }),
        ];
    }
}
