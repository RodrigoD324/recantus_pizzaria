<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';

    protected $fillable = [
        'id_tipo_pagamento',
        'id_vendedor',
        'id_pedido_status',
        'valor_total',
        'valor_pago',
        'valor_a_pagar',
        'troco',
        'observacao',
        'id_cancelamento'
    ];

    public function getNumeroPedidoAttribute()
    {
        return 'PED-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function tipoPagamento()
    {
        return $this->belongsTo(TipoPagamento::class, 'id_tipo_pagamento');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'id_vendedor');
    }

    public function status()
    {
        return $this->belongsTo(PedidoStatus::class, 'id_pedido_status');
    }

    public function itens()
    {
        return $this->hasMany(PedidoProduto::class, 'id_pedido');
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'pedido_produto', 'id_pedido', 'id_produto')
            ->withPivot('quantidade', 'preco_unitario', 'subtotal')
            ->withTimestamps();
    }
}
