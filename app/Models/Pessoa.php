<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoa';

    protected $fillable = [
        'id_endereco',
        'id_contato',
        'nome',
        'cpf',
        'id_cancelamento',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function contato()
    {
        return $this->belongsTo(Contato::class, 'id_contato', 'id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'id_endereco', 'id');
    }
}