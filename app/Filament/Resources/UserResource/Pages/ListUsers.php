<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'todos' => Tab::make('Todos'),
            // 'ativos' => Tab::make('Ativados')->modifyQueryUsing(fn(Builder $query) => $query->whereNull('id_cancelamento')),
            // 'desativados' => Tab::make('Desativados')->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('id_cancelamento')),
        ];
    }
}
