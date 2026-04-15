<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
}
