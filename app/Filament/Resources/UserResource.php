<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pessoa.contato', 'pessoa.endereco']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SEÇÃO 1: PESSOAL ---
                Forms\Components\Section::make('Informações Pessoais')
                    ->relationship('pessoa')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome Completo')
                            ->placeholder('Ex: João Silva Sauro')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->placeholder('000.000.000-00')
                            ->mask('999.999.999-99')
                            ->dehydrateStateUsing(fn(string $state): string => preg_replace('/\D/', '', $state))
                            ->required(),
                    ])->columns(3),

                // --- SEÇÃO 2: CONTATO ---
                Forms\Components\Section::make('Informações de Contato')
                    ->relationship('pessoa')
                    ->schema([
                        Forms\Components\Group::make()
                            ->relationship('contato')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail')
                                    ->placeholder('joao@exemplo.com')
                                    ->email()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('ddd_celular')
                                    ->label('DDD')
                                    ->placeholder('11')
                                    ->maxLength(2)
                                    ->required(),

                                Forms\Components\TextInput::make('celular')
                                    ->label('Celular')
                                    ->placeholder('988887777')
                                    ->mask('999999999')
                                    ->maxLength(9)
                                    ->required(),
                            ])->columns(4),
                    ]),

                // --- SEÇÃO 3: ENDEREÇO ---
                Forms\Components\Section::make('Informações de Endereço')
                    ->relationship('pessoa')
                    ->schema([
                        Forms\Components\Group::make()
                            ->relationship('endereco')
                            ->schema([
                                Forms\Components\TextInput::make('cep')
                                    ->label('CEP')
                                    ->placeholder('00000-000')
                                    ->mask('99999-999')
                                    ->dehydrateStateUsing(fn(string $state): string => preg_replace('/\D/', '', $state))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                        if (!$state)
                                            return;

                                        $cep = preg_replace('/\D/', '', $state);

                                        if (strlen($cep) !== 8)
                                            return;

                                        try {
                                            $response = \Illuminate\Support\Facades\Http::get("https://viacep.com.br/ws/{$cep}/json/");

                                            if ($response->failed()) {
                                                throw new \Exception('Erro na consulta do CEP.');
                                            }

                                            $data = $response->json();

                                            if (isset($data['erro']) && $data['erro'] === true) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('CEP não encontrado')
                                                    ->body('O CEP digitado não existe na base de dados.')
                                                    ->warning()
                                                    ->send();

                                                return;
                                            }

                                            $set('logradouro', $data['logradouro'] ?? '');
                                            $set('bairro', $data['bairro'] ?? '');
                                            $set('municipio', $data['localidade'] ?? '');
                                            $set('estado', $data['uf'] ?? '');

                                        } catch (\Exception $e) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Erro ao buscar CEP')
                                                ->body('Não foi possível conectar ao serviço de busca. Tente preencher manualmente.')
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                                Forms\Components\TextInput::make('logradouro')
                                    ->label('Logradouro')
                                    ->placeholder('Ex: Rua das Flores')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('numero')
                                    ->label('Número')
                                    ->placeholder('123')
                                    ->required(),

                                Forms\Components\TextInput::make('complemento')
                                    ->placeholder('Ex: Apto 12 / Bloco B')
                                    ->label('Complemento'),

                                Forms\Components\TextInput::make('bairro')
                                    ->label('Bairro')
                                    ->placeholder('Ex: Centro')
                                    ->required(),

                                Forms\Components\TextInput::make('municipio')
                                    ->label('Município')
                                    ->placeholder('Ex: São Paulo')
                                    ->required(),

                                Forms\Components\Select::make('estado')
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

                // --- SEÇÃO 4: ACESSO ---
                Forms\Components\Section::make('Acesso ao Sistema')
                    ->schema([
                        Forms\Components\TextInput::make('login')
                            ->placeholder('usuario.exemplo')
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->placeholder('********')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
