<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperacaoPdv extends Model
{
    protected $table = 'operacao_pdv';

    protected $fillable = [
        'id_usuario',
        'id_operacao_pdv_tipo',
        'valor',
        'observacao',
        'id_cancelamento',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(OperacaoPdvTipo::class, 'id_operacao_pdv_tipo');
    }

    /**
     * Check if the POS is currently open.
     * The POS is open if the last operation was 'ABERTURA'.
     */
    public static function isAberto(): bool
    {
        $lastOp = self::whereNull('id_cancelamento')
            ->whereHas('tipo', function ($query) {
                $query->whereIn('referencia', [OperacaoPdvTipo::ABERTURA, OperacaoPdvTipo::FECHAMENTO]);
            })
            ->latest()
            ->first();

        if (!$lastOp) {
            return false;
        }

        return $lastOp->tipo->referencia === OperacaoPdvTipo::ABERTURA;
    }
}
