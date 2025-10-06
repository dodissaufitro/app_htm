<?php

namespace App\Filament\Resources\PersetujuanDeveloperResource\Pages;

use App\Filament\Resources\PersetujuanDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ViewPersetujuanDeveloper extends ViewRecord
{
    protected static string $resource = PersetujuanDeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Proses Persetujuan')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
            Actions\Action::make('approve_to_bank')
                ->label('Lanjut ke Bank')
                ->icon('heroicon-o-building-library')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Lanjut ke Bank')
                ->modalDescription('Apakah Anda yakin ingin melanjutkan permohonan ini ke tahap Bank?')
                ->action(function ($record) {
                    $record->update(['status_permohonan' => '9']);
                    $this->redirect(static::getResource()::getUrl('index'));
                }),
            Actions\Action::make('hold')
                ->label('Tunda')
                ->icon('heroicon-o-pause-circle')
                ->color('gray'),
            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pemohon')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id_pendaftaran')
                                    ->label('ID Pendaftaran')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('nama')
                                    ->label('Nama Pemohon')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('nik')
                                    ->label('NIK')
                                    ->copyable(),
                                TextEntry::make('no_hp')
                                    ->label('No. HP')
                                    ->copyable(),
                            ]),
                    ])
                    ->icon('heroicon-o-user'),

                Section::make('Detail Permohonan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('gaji')
                                    ->label('Gaji')
                                    ->money('IDR'),
                                TextEntry::make('bank.nama_bank')
                                    ->label('Bank')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('harga_unit')
                                    ->label('Harga Unit')
                                    ->money('IDR'),
                                TextEntry::make('lokasi_rumah')
                                    ->label('Lokasi Rumah'),
                            ]),
                    ])
                    ->icon('heroicon-o-home'),

                Section::make('Status dan Catatan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status.nama')
                                    ->label('Status Saat Ini')
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Update')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('keterangan')
                                    ->label('Catatan')
                                    ->columnSpanFull()
                                    ->placeholder('Belum ada catatan'),
                            ]),
                    ])
                    ->icon('heroicon-o-clipboard-document-check'),
            ]);
    }
}
