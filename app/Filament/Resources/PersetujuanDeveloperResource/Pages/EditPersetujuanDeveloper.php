<?php

namespace App\Filament\Resources\PersetujuanDeveloperResource\Pages;

use App\Filament\Resources\PersetujuanDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditPersetujuanDeveloper extends EditRecord
{
    protected static string $resource = PersetujuanDeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update record
        $record->update($data);

        // Send notification based on status change
        $statusMessages = [
            '9' => 'Permohonan berhasil diteruskan ke tahap Bank.',
            '6' => 'Permohonan berhasil ditunda.',
            '3' => 'Permohonan berhasil ditolak.',
            '2' => 'Permohonan tetap dalam tahap Developer.',
        ];

        $message = $statusMessages[$data['status_permohonan']] ?? 'Permohonan berhasil diupdate.';

        Notification::make()
            ->title('Berhasil Diproses')
            ->body($message)
            ->success()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Permohonan berhasil diproses';
    }
}
