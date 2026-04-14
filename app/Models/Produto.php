<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produto';

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

}