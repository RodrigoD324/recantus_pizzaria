<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_pagamento')->insert([
            [
                'nome' => 'Dinheiro',
                'referencia' => 'dinheiro',
                'permite_troco' => true,
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'PIX',
                'referencia' => 'pix',
                'permite_troco' => false,
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Débito',
                'referencia' => 'debito',
                'permite_troco' => false,
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Crédito',
                'referencia' => 'credito',
                'permite_troco' => false,
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Fiado',
                'referencia' => 'fiado',
                'permite_troco' => false,
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}