<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Pessoa;
use App\Models\Contato;
use App\Models\Endereco;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $contato = \App\Models\Contato::create($data['pessoa']['contato']);

            $endereco = \App\Models\Endereco::create($data['pessoa']['endereco']);

            $pessoa = \App\Models\Pessoa::create([
                'nome' => $data['pessoa']['nome'],
                'cpf' => $data['pessoa']['cpf'],
                'id_contato' => $contato->id,
                'id_endereco' => $endereco->id,
            ]);

            return \App\Models\User::create([
                'id_pessoa' => $pessoa->id,
                'id_usuario_tipo' => $data['id_usuario_tipo'],
                'login' => $data['login'],
                'password' => $data['password'], 
            ]);
        });
    }
}