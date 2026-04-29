<?php

namespace Database\Seeders;

use App\Models\OperacaoPdvTipo;
use Illuminate\Database\Seeder;

class OperacaoPdvTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['nome' => 'Abertura', 'referencia' => OperacaoPdvTipo::ABERTURA],
            ['nome' => 'Fechamento', 'referencia' => OperacaoPdvTipo::FECHAMENTO],
            ['nome' => 'Suprimento', 'referencia' => OperacaoPdvTipo::SUPRIMENTO],
            ['nome' => 'Sangria', 'referencia' => OperacaoPdvTipo::SANGRIA],
        ];

        foreach ($types as $type) {
            OperacaoPdvTipo::updateOrCreate(
                ['referencia' => $type['referencia']],
                ['nome' => $type['nome']]
            );
        }
    }
}
