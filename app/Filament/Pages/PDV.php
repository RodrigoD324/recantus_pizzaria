<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PDV extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.pdv';
    protected static ?string $navigationGroup = 'Frente de Caixa';
    protected static ?string $title = 'PDV';
    protected static ?string $slug = 'pdv';

    public function mount(): void
    {
        //
    }
}