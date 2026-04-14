<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Helpers\CEP;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $breadcrumb = 'Usuários';

    public static function getPluralModelLabel(): string
    {
        return 'Usuários';
    }

    public static function getModelLabel(): string
    {
        return 'Usuário';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pessoa.contato', 'pessoa.endereco']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    // ->relationship('pessoa')
                    ->schema([
                        Section::make('Informações Pessoais')
                            ->schema([
                                TextInput::make('pessoa.nome')
                                    ->label('Nome Completo')
                                    ->placeholder('Ex: João Silva Sauro')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('pessoa.cpf')
                                    ->label('CPF')
                                    // ->unique()
                                    ->placeholder('000.000.000-00')
                                    ->mask('999.999.999-99')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn(string $state): string => preg_replace('/\D/', '', $state))
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        if ($get('../../id_usuario_tipo') == 2) {
                                            $cpfApenasNumeros = preg_replace('/\D/', '', $state);
                                            $set('../../login', $cpfApenasNumeros);
                                            $set('../../password', $cpfApenasNumeros);
                                        }
                                    }),
                            ])->columns(3),

                        Section::make('Informações de Contato')
                            ->schema([
                                Forms\Components\Group::make()
                                    // ->relationship('contato')
                                    ->schema([
                                        TextInput::make('pessoa.contato.email')
                                            ->label('E-mail')
                                            ->placeholder('joao@exemplo.com')
                                            ->email()
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('pessoa.contato.ddd_celular')
                                            ->label('DDD')
                                            ->placeholder('11')
                                            ->maxLength(2)
                                            ->required(),

                                        TextInput::make('pessoa.contato.celular')
                                            ->label('Celular')
                                            ->placeholder('988887777')
                                            ->mask('999999999')
                                            ->maxLength(9)
                                            ->required(),
                                    ])->columns(4),
                            ]),

                        Section::make('Informações de Endereço')
                            ->schema([
                                Forms\Components\Group::make()
                                    // ->relationship('endereco')
                                    ->schema([
                                        TextInput::make('pessoa.endereco.cep')
                                            ->label('CEP')
                                            ->placeholder('00000-000')
                                            ->mask('99999-999')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->dehydrateStateUsing(fn(string $state): string => preg_replace('/\D/', '', $state))
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                if (!$state)
                                                    return;
                                                try {
                                                    $data = CEP::get($state);
                                                    if (isset($data['erro']) && $data['erro'] === true) {
                                                        Notification::make()
                                                            ->title('CEP não encontrado')->warning()->send();
                                                        return;
                                                    }
                                                    $set('pessoa.endereco.logradouro', $data['logradouro'] ?? '');
                                                    $set('pessoa.endereco.bairro', $data['bairro'] ?? '');
                                                    $set('pessoa.endereco.municipio', $data['localidade'] ?? '');
                                                    $set('pessoa.endereco.estado', $data['uf'] ?? '');
                                                } catch (\Exception $e) {
                                                }
                                            }),

                                        TextInput::make('pessoa.endereco.logradouro')
                                            ->label('Logradouro')
                                            ->placeholder('Ex: Rua das Flores')
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('pessoa.endereco.numero')
                                            ->label('Número')
                                            ->placeholder('123')
                                            ->required(),

                                        TextInput::make('pessoa.endereco.complemento')
                                            ->label('Complemento')
                                            ->placeholder('Ex: Apto 12 / Bloco B'),

                                        TextInput::make('pessoa.endereco.bairro')
                                            ->label('Bairro')
                                            ->placeholder('Ex: Centro')
                                            ->required(),

                                        TextInput::make('pessoa.endereco.municipio')
                                            ->label('Município')
                                            ->placeholder('Ex: São Paulo')
                                            ->required(),

                                        Select::make('pessoa.endereco.estado')
                                            ->label('Estado')
                                            ->options([
                                                'AC' => 'Acre',
                                                'AL' => 'Alagoas',
                                                'AP' => 'Amapá',
                                                'AM' => 'Amazonas',
                                                'BA' => 'Bahia',
                                                'CE' => 'Ceará',
                                                'DF' => 'Distrito Federal',
                                                'ES' => 'Espírito Santo',
                                                'GO' => 'Goiás',
                                                'MA' => 'Maranhão',
                                                'MT' => 'Mato Grosso',
                                                'MS' => 'Mato Grosso do Sul',
                                                'MG' => 'Minas Gerais',
                                                'PA' => 'Pará',
                                                'PB' => 'Paraíba',
                                                'PR' => 'Paraná',
                                                'PE' => 'Pernambuco',
                                                'PI' => 'Piauí',
                                                'RJ' => 'Rio de Janeiro',
                                                'RN' => 'Rio Grande do Norte',
                                                'RS' => 'Rio Grande do Sul',
                                                'RO' => 'Rondônia',
                                                'RR' => 'Roraima',
                                                'SC' => 'Santa Catarina',
                                                'SP' => 'São Paulo',
                                                'SE' => 'Sergipe',
                                                'TO' => 'Tocantins',
                                            ])
                                            ->searchable()
                                            ->placeholder('Selecione um estado')
                                            ->required(),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Acesso ao Sistema')
                    ->schema([
                        Select::make('id_usuario_tipo')
                            ->label('Tipo de Usuário')
                            ->relationship('tipo', 'nome')
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state == 2) {
                                    $cpf = $get('pessoa.cpf');
                                    $cpfLimpio = preg_replace('/\D/', '', $cpf ?? '');
                                    $set('login', $cpfLimpio);
                                    $set('password', $cpfLimpio);
                                }
                            }),

                        TextInput::make('login')
                            ->label('Login')
                            ->placeholder('usuario.exemplo')
                            ->required()
                            ->disabled(fn(Get $get) => $get('id_usuario_tipo') == 2)
                            ->dehydrated(),

                        TextInput::make('password')
                            ->label('Senha')
                            ->placeholder('********')
                            ->password()
                            ->required(fn($context, Get $get) => $context === 'create' && $get('id_usuario_tipo') != 2)
                            ->disabled(fn(Get $get) => $get('id_usuario_tipo') == 2)
                            ->dehydrated(fn($state) => filled($state)),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                SelectFilter::make('id_usuario_tipo')
                    ->label('Tipo de Usuário')
                    ->options([
                        1   =>  'Admin',
                        2   =>  'Cliente'
                    ]),
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
                TextColumn::make('pessoa.nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pessoa.cpf')
                    ->label('CPF')
                    ->copyable(),

                TextColumn::make('pessoa.contato.email')
                    ->label('E-mail'),

                TextColumn::make('telefone')
                    ->label('Celular')
                    ->getStateUsing(function ($record) {
                        return "({$record->pessoa?->contato?->ddd_celular}) {$record->pessoa?->contato?->celular}";
                    }),
                TextColumn::make('id_usuario_tipo')
                    ->label('Tipo')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->id_usuario_tipo == 1 ? 'Admin' : 'Cliente')
                    ->color(fn($state) => $state === 'Admin' ? 'info' : 'warning'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->id_cancelamento ? 'Desativado' : 'Ativo')
                    ->color(fn($state) => $state === 'Ativo' ? 'success' : 'danger'),
            ])
            ->actions([
                // EditAction::make(),
                Action::make('cancelar')
                    ->label('Desativar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Usuário')
                    ->modalDescription('Informe o motivo do cancelamento deste usuário.')
                    ->modalSubmitActionLabel('Confirmar Cancelamento')
                    ->hidden(fn($record) => $record->id_cancelamento !== null)
                    ->form([
                        Textarea::make('motivo')
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
                        Notification::make()
                            ->title('Usuário cancelado com sucesso')
                            ->success()
                            ->send();
                    }),
                Action::make('ativar')
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

                        Notification::make()
                            ->title('Usuário reativado com sucesso')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('cancelar_selecionados')
                        ->label('Cancelar Selecionados')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->form([
                            Textarea::make('motivo')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/criar'),
            'edit' => Pages\EditUser::route('/{record}/editar'),
        ];
    }
}
