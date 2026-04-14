<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioTipo extends Model
{
    use HasFactory;

    protected $table = 'usuario_tipo';

    protected $fillable = [
        'referencia',
        'nome',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_usuario_tipo');
    }
}