<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contato extends Model
{
    use HasFactory;
    
    protected $table = 'contato';

    protected $fillable = [
        'ddd_telefone',
        'telefone',
        'ddd_celular',
        'celular',
        'email',
        'id_cancelamento',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}