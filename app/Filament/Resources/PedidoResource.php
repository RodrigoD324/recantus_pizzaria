<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Models\Pedido;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Gestão';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction('ver')
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
                TextColumn::make('id')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipoPagamento.nome')
                    ->label('Forma de Pagamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendedor.pessoa.nome')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.nome')
                    ->label('Status'),
                TextColumn::make('valor_total')
                    ->label('Valor Total'),
                TextColumn::make('valor_pago')
                    ->label('Valor Pago'),
                TextColumn::make('valor_a_pagar')
                    ->label('Valor A Pagar'),
                TextColumn::make('troco')
                    ->label('Troco'),
            ])
            ->actions([
                Action::make('cancelar')
                    ->label('Desativar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Pedido')
                    ->modalDescription('Informe o motivo do cancelamento deste pedido.')
                    ->modalSubmitActionLabel('Confirmar Cancelamento')
                    ->hidden(fn($record) => $record->id_cancelamento !== null)
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->required(),
                    ])
                    ->action(function (Pedido $record, array $data): void {
                        $cancelamento = \App\Models\Cancelamento::create([
                            'motivo' => $data['motivo'],
                            'id_usuario' => auth()->id(),
                        ]);
                        $record->update([
                            'id_cancelamento' => $cancelamento->id,
                        ]);
                        Notification::make()
                            ->title('Pedido cancelado com sucesso')
                            ->success()
                            ->send();
                    }),
                Action::make('ativar')
                    ->label('Ativar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reativar Pedido')
                    ->modalDescription('Tem certeza que deseja ativar este pedido novamente?')
                    ->visible(fn($record) => $record->id_cancelamento !== null)
                    ->action(function (Pedido $record): void {
                        $record->update([
                            'id_cancelamento' => null,
                        ]);

                        Notification::make()
                            ->title('Pedido reativado com sucesso')
                            ->success()
                            ->send();
                    }),
                Action::make('ver')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn(Pedido $record) => "Pedido #{$record->id}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->infolist([
                        TextEntry::make('observacao')
                            ->label('Observação')
                            ->default('Sem observação'),

                        RepeatableEntry::make('produtos')
                            ->label('Produtos')
                            ->schema([
                                TextEntry::make('descricao')
                                    ->label('Produto'),

                                TextEntry::make('pivot.quantidade')
                                    ->label('Quantidade'),

                                TextEntry::make('pivot.preco_unitario')
                                    ->label('Preço'),

                                TextEntry::make('pivot.subtotal')
                                    ->label('Subtotal'),
                            ])
                    ])
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
            'index' => Pages\ListPedidos::route('/'),
            // 'create' => Pages\CreatePedido::route('/criar'),
            // 'edit' => Pages\EditPedido::route('/{record}/editar'),
        ];
    }
}
