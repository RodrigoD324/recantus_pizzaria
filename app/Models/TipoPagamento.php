<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPagamento extends Model
{
    protected $table = 'tipo_pagamento';
    
    protected $fillable = [
        'nome', 'referencia', 'permite_troco', 'id_cancelamento'
    ];
}