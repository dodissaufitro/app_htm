<?php

namespace App\Filament\Resources\DaftarBankResource\Pages;

use App\Filament\Resources\DaftarBankResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateDaftarBank extends CreateRecord
{
    protected static string $resource = DaftarBankResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bank berhasil ditambahkan')
            ->body('Data bank telah berhasil disimpan ke sistem.');
    }
}
