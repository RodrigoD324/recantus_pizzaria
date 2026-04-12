<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Gestão de Usuários';

    protected static ?string $breadcrumb = 'Usuários';

    public static function getPluralModelLabel(): string
    {
        return 'Usuários';
    }

    public static function getModelLabel(): string
    {
        return 'Usuário';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Pessoais')
                    ->relationship('pessoa')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome Completo')
                            ->required(),

                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->dehydrateStateUsing(fn(string $state): string => preg_replace('/\D/', '', $state))
                            ->required(),

                        Forms\Components\Group::make()
                            ->relationship('contato')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->required(),

                                Forms\Components\TextInput::make('ddd_celular')
                                    ->label('DDD')
                                    ->maxLength(2)
                                    ->required(),

                                Forms\Components\TextInput::make('celular')
                                    ->label('Celular')
                                    ->mask('999999999')
                                    ->maxLength(9)
                                    ->required(),
                            ])->columns(3),
                    ])->columns(2),

                Forms\Components\Section::make('Acesso ao Sistema')
                    ->schema([
                        Forms\Components\TextInput::make('login')
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn($context) => $context === 'create'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pessoa.nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pessoa.cpf')
                    ->label('CPF')
                    ->copyable(),

                Tables\Columns\TextColumn::make('pessoa.contato.email')
                    ->label('E-mail'),

                Tables\Columns\TextColumn::make('telefone')
                    ->label('Celular')
                    ->getStateUsing(function ($record) {
                        return "({$record->pessoa?->contato?->ddd_celular}) {$record->pessoa?->contato?->celular}";
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->id_cancelamento ? 'Desativado' : 'Ativo')
                    ->color(fn($state) => $state === 'Ativo' ? 'success' : 'danger'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cancelar')
                    ->label('Desativar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Usuário')
                    ->modalDescription('Informe o motivo do cancelamento deste usuário.')
                    ->modalSubmitActionLabel('Confirmar Cancelamento')
                    ->hidden(fn($record) => $record->id_cancelamento !== null)
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo')
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $cancelamento = \App\Models\Cancelamento::create([
                            'motivo' => $data['motivo'],
                            'id_usuario' => auth()->id(),
                        ]);
                        $record->update([
                            'id_cancelamento' => $cancelamento->id,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Usuário cancelado com sucesso')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('ativar')
                    ->label('Ativar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Reativar Usuário')
                    ->modalDescription('Tem certeza que deseja ativar este usuário novamente?')
                    ->visible(fn($record) => $record->id_cancelamento !== null)
                    ->action(function (User $record): void {
                        $record->update([
                            'id_cancelamento' => null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Usuário reativado com sucesso')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cancelar_selecionados')
                        ->label('Cancelar Selecionados')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('motivo')
                                ->label('Motivo para todos os selecionados')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $cancelamento = \App\Models\Cancelamento::create([
                                'motivo' => $data['motivo'],
                                'id_usuario' => auth()->id(),
                            ]);

                            $records->each(fn($record) => $record->update([
                                'id_cancelamento' => $cancelamento->id,
                            ]));
                        })
                        ->requiresConfirmation(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
