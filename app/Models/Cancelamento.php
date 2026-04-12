<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cancelamento extends Model
{
    use HasFactory;

    protected $table = 'cancelamento';
    protected $fillable = ['motivo', 'id_usuario'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}