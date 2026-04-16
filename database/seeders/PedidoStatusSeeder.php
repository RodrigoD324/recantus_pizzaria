<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pedido_status')->insert([
            [
                'nome' => 'Pendente',
                'referencia' => 'pendente',
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Finalizado',
                'referencia' => 'finalizado',
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cancelado',
                'referencia' => 'cancelado',
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Em produção',
                'referencia' => 'em_producao',
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Entregue',
                'referencia' => 'entregue',
                'id_cancelamento' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}