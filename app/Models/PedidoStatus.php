<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoStatus extends Model
{
    protected $table = 'pedido_status';
    
    protected $fillable = [
        'nome', 'referencia', 'id_cancelamento'
    ];
}