<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
class CEP
{
    public static function get(?string $cep): array
    {
        if (!$cep)
            return [];

        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8)
            return [];

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed()) {
            throw new \Exception('Erro na consulta do CEP.');
        }

        return $response->json();

    }
}
