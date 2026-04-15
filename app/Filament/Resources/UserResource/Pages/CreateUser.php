<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Pessoa;
use App\Models\Contato;
use App\Models\Endereco;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $cpf = preg_replace('/\D/', '', $data['pessoa']['cpf'] ?? '');

            if (Pessoa::where('cpf', $cpf)->exists()) {
                throw ValidationException::withMessages([
                    'data.pessoa.cpf' => 'Já existe uma pessoa cadastrada com este CPF.',
                ]);
            }

            if (!empty($data['pessoa']['contato']['email'])) {
                $email = $data['pessoa']['contato']['email'];

                if (Contato::where('email', $email)->exists()) {
                    throw ValidationException::withMessages([
                        'data.pessoa.contato.email' => 'Já existe um usuário cadastrado com este e-mail.',
                    ]);
                }
            }

            $contato = Contato::create($data['pessoa']['contato']);

            $endereco = Endereco::create($data['pessoa']['endereco']);

            $pessoa = Pessoa::create([
                'nome' => $data['pessoa']['nome'],
                'cpf' => $cpf,
                'id_contato' => $contato->id,
                'id_endereco' => $endereco->id,
            ]);

            return User::create([
                'id_pessoa' => $pessoa->id,
                'id_usuario_tipo' => $data['id_usuario_tipo'],
                'login' => $data['login'],
                'password' => $data['password'],
            ]);
        });
    }
}