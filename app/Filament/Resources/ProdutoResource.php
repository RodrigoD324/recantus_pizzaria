<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Models\Produto;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gestão';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações do Produto')
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Ex: PIZZA DE FRANGO C/REQUEIJÃO')
                            ->required()
                            ->maxLength(150)
                            ->autofocus()
                            ->validationMessages([
                                'required' => 'Ei, você esqueceu de preencher a Descrição!',
                                'max' => 'A descrição deve ter no máximo 150 caracteres.',
                            ]),
                        TextInput::make('codigo')
                            ->label('Código')
                            ->placeholder('Ex: 90')
                            ->required()
                            ->numeric()
                            ->mask('999999999')
                            ->validationMessages([
                                'required' => 'Ei, você esqueceu de preencher o Código!',
                                'integer' => 'O código deve ser um número inteiro.',
                            ]),
                        TextInput::make('valor')
                            ->label('Valor Unitário')
                            ->placeholder('0,00')
                            ->prefix('R$')
                            ->required()
                            ->type('text')
                            ->extraInputAttributes([
                                'x-data' => '{}',
                                'x-on:input' => "
                                    let numbers = \$el.value.replace(/\D/g, '').slice(0, 9);
                                    numbers = numbers.padStart(3, '0');
                                    let reais = numbers.slice(0, -2);
                                    let centavos = numbers.slice(-2);
                                    reais = reais.replace(/^0+(\d)/, '$1');
                                    let formatted = Number(reais).toLocaleString('pt-BR') + ',' + centavos;
                                    if (reais === '' || reais === '0') formatted = '0,' + centavos;
                                    \$el.value = formatted;
                                ",
                            ])
                            ->dehydrateStateUsing(function ($state) {
                                if (!$state)
                                    return null;
                                $onlyNumbers = preg_replace('/\D/', '', $state);
                                return (float) ($onlyNumbers / 100);
                            })
                            ->formatStateUsing(fn($state) => $state ? number_format((float) $state, 2, ',', '.') : null)
                            ->maxLength(14),
                        TextInput::make('quantidade')
                            ->label('Quantidade')
                            ->placeholder('Ex: 100')
                            ->required()
                            ->numeric()
                            ->mask('999999')
                            ->validationMessages([
                                'required' => 'Ei, você esqueceu de preencher a Quantidade!',
                                'integer' => 'O código deve ser um número inteiro.',
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ativo' => 'Ativado',
                        'desativado' => 'Desativado',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'ativo' => $query->whereNull('id_cancelamento'),
                            'desativado' => $query->whereNotNull('id_cancelamento'),
                            default => $query,
                        };
                    })
            ])
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('valor')
                    ->label('Unitário'),
                TextColumn::make('quantidade')
                    ->label('Quantidade'),
            ])
            ->actions([
                Action::make('cancelar')
                    ->label('Desativar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Produto')
                    ->modalDescription('Informe o motivo do cancelamento deste produto.')
                    ->modalSubmitActionLabel('Confirmar Cancelamento')
                    ->hidden(fn($record) => $record->id_cancelamento !== null)
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->required(),
                    ])
                    ->action(function (Produto $record, array $data): void {
                        $cancelamento = \App\Models\Cancelamento::create([
                            'motivo' => $data['motivo'],
                            'id_usuario' => auth()->id(),
                        ]);
                        $record->update([
                            'id_cancelamento' => $cancelamento->id,
                        ]);
                        Notification::make()
                            ->title('Produto cancelado com sucesso')
                            ->success()
                            ->send();
                    }),
                Action::make('ativar')
                    ->label('Ativar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reativar Produto')
                    ->modalDescription('Tem certeza que deseja ativar este produto novamente?')
                    ->visible(fn($record) => $record->id_cancelamento !== null)
                    ->action(function (Produto $record): void {
                        $record->update([
                            'id_cancelamento' => null,
                        ]);

                        Notification::make()
                            ->title('Produto reativado com sucesso')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/criar'),
            'edit' => Pages\EditProduto::route('/{record}/editar'),
        ];
    }
}
