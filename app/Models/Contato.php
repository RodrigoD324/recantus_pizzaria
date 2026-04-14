<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contato extends Model
{
    use HasFactory;

    protected $table = 'contato';

    protected $fillable = [
        'ddd_celular',
        'celular',
        'email',
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