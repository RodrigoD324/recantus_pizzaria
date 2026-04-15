<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord();
        $pessoa = $user->pessoa;

        if ($pessoa) {
            $data['pessoa'] = [
                'nome' => $pessoa->nome,
                'cpf' => $pessoa->cpf,
                'contato' => [
                    'email' => $pessoa->contato?->email,
                    'ddd_celular' => $pessoa->contato?->ddd_celular,
                    'celular' => $pessoa->contato?->celular,
                ],
                'endereco' => [
                    'cep' => $pessoa->endereco?->cep,
                    'logradouro' => $pessoa->endereco?->logradouro,
                    'numero' => $pessoa->endereco?->numero,
                    'complemento' => $pessoa->endereco?->complemento,
                    'bairro' => $pessoa->endereco?->bairro,
                    'municipio' => $pessoa->endereco?->municipio,
                    'estado' => $pessoa->endereco?->estado,
                ],
            ];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Atualiza os dados do usuário (login, senha, tipo, etc)
        $record->update($data);

        // 2. Verifica se existem dados de 'pessoa' no formulário
        if (isset($data['pessoa'])) {
            $pessoaData = $data['pessoa'];
            $pessoa = $record->pessoa;

            if ($pessoa) {
                // Atualiza os dados da Pessoa (Nome, CPF)
                $pessoa->update($pessoaData);

                // Atualiza os dados de Contato
                if (isset($pessoaData['contato'])) {
                    $pessoa->contato()->update($pessoaData['contato']);
                }

                // Atualiza os dados de Endereço
                if (isset($pessoaData['endereco'])) {
                    $pessoa->endereco()->update($pessoaData['endereco']);
                }
            }
        }

        return $record;
    }
}
