<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperacaoPdvTipo extends Model
{
    protected $table = 'operacao_pdv_tipo';

    protected $fillable = [
        'nome',
        'referencia',
        'id_cancelamento',
    ];

    const ABERTURA = 'abertura';
    const FECHAMENTO = 'fechamento';
    const SUPRIMENTO = 'suprimento';
    const SANGRIA = 'sangria';
}
