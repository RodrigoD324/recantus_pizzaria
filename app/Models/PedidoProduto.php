<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoProduto extends Model
{
    protected $table = 'pedido_produto';
    
    protected $fillable = [
        'id_pedido', 'id_produto', 'quantidade', 
        'preco_unitario', 'subtotal', 'id_cancelamento'
    ];
    
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
    
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }
}