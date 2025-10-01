<?php

namespace App\Filament\Resources\KelengkapanDataResource\Pages;

use App\Filament\Resources\KelengkapanDataResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateKelengkapanData extends CreateRecord
{
    protected static string $resource = KelengkapanDataResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Filament::auth()->id();
        $data['id_pendaftaran'] = 'REG' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) . '/' . date('Y');
        $data['username'] = strtolower(str_replace(' ', '.', $data['nama'] ?? 'user'));

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
