<?php

namespace App\Filament\Resources\DaftarBankResource\Pages;

use App\Filament\Resources\DaftarBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDaftarBank extends EditRecord
{
    protected static string $resource = DaftarBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->color('info'),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Bank')
                ->modalDescription('Apakah Anda yakin ingin menghapus bank ini?'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bank berhasil diperbarui')
            ->body('Perubahan data bank telah berhasil disimpan.');
    }
}
