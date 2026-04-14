<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Endereco extends Model
{
    use HasFactory;

    protected $table = 'endereco';

    protected $fillable = [
        'estado',
        'municipio',
        'bairro',
        'cep',
        'logradouro',
        'numero',
        'complemento',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'id');
    }
}