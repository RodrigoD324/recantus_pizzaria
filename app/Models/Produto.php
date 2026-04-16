<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produto';

    protected $fillable = [
        'descricao',
        'codigo',
        'valor',
        'quantidade',
        'id_cancelamento',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_produto', 'id_produto', 'id_pedido')
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }

}