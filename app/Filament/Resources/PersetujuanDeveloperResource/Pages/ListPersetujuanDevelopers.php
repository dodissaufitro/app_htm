<?php

namespace App\Filament\Resources\PersetujuanDeveloperResource\Pages;

use App\Filament\Resources\PersetujuanDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListPersetujuanDevelopers extends ListRecords
{
    protected static string $resource = PersetujuanDeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('workflow_info')
                ->label('Info Workflow Developer')
                ->icon('heroicon-o-information-circle')
                ->color('info')
                ->modalHeading('Workflow Developer')
                ->modalDescription('Sebagai Developer (urutan 3), Anda bertanggung jawab memproses permohonan yang telah disetujui verifikator. Anda dapat melanjutkan ke Bank, menunda, atau menolak permohonan.')
                ->modalWidth('lg'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn() => static::getResource()::getEloquentQuery()->count()),

            'need_attention' => Tab::make('Perlu Perhatian')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('updated_at', '<', now()->subDays(3)))
                ->badge(fn() => static::getResource()::getEloquentQuery()->where('updated_at', '<', now()->subDays(3))->count())
                ->badgeColor('warning'),

            'recent' => Tab::make('Terbaru')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('updated_at', '>=', now()->subDays(1)))
                ->badge(fn() => static::getResource()::getEloquentQuery()->where('updated_at', '>=', now()->subDays(1))->count())
                ->badgeColor('success'),
        ];
    }

    protected function getTableDescription(): ?string
    {
        $user = Auth::user();
        if ($user && $user->urutan === 3) {
            return 'Daftar permohonan yang telah disetujui verifikator dan menunggu persetujuan Developer. Sebagai Developer, Anda dapat memproses permohonan ini ke tahap selanjutnya.';
        }
        return null;
    }
}
