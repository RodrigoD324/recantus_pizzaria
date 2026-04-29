<?php

namespace App\Traits;

use App\Models\Pedido;

trait HasPrintableComanda
{
    public function generateComandaHtml(Pedido $pedido): string
    {
        $pedido->load(['itens.produto', 'vendedor', 'tipoPagamento']);
        $itens = $pedido->itens;
        
        $clienteData = $this->parseObservacao($pedido->observacao);
        $data = date('d/m/Y');
        $hora = date('H:i:s');

        $html = '
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <title>Comanda #' . $pedido->id . '</title>
                <style>
                    @page { margin: 0; }
                    body { 
                        font-family: "Courier New", Courier, monospace; 
                        width: 300px; 
                        margin: 0; 
                        padding: 10px; 
                        font-size: 12px;
                        line-height: 1.2;
                        color: #000;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .bold { font-weight: bold; }
                    .dashed-line { border-top: 1px dashed #000; margin: 5px 0; }
                    .header-info { display: flex; justify-content: space-between; font-size: 11px; }
                    .store-name { font-size: 14px; margin: 5px 0; }
                    .store-addr { font-size: 11px; }
                    .order-info { margin: 5px 0; font-size: 12px; }
                    .seller-info { margin: 5px 0; font-size: 11px; }
                    
                    table { width: 100%; border-collapse: collapse; margin: 5px 0; }
                    th { text-align: left; border-bottom: 1px dashed #000; padding-bottom: 2px; font-size: 10px; }
                    td { padding: 2px 0; vertical-align: top; }
                    
                    .item-row td { font-size: 11px; }
                    .item-desc { text-transform: uppercase; font-weight: bold; display: block; }
                    .item-code { font-size: 10px; display: block; }
                    
                    .totals { margin-top: 5px; }
                    .total-line { display: flex; justify-content: space-between; margin: 2px 0; }
                    .signature { margin-top: 20px; text-align: center; }
                    .sig-line { border-top: 1px solid #000; width: 80%; margin: 20px auto 5px; }
                    
                    @media print { 
                        .no-print { display: none; } 
                    }
                </style>
            </head>
            <body>
                <div class="header-info">
                    <span>@CONTROLE INTERNO</span>
                    <span>' . $hora . '</span>
                    <span>' . $data . '</span>
                </div>
                
                <div class="dashed-line"></div>
                
                <div class="text-center">
                    <div class="store-name bold">Recantus Pizzaria</div>
                    <div class="store-addr">Rua: Shiguetoshi Suzuki 595 - Sala 1</div>
                    <div class="store-addr">Vila Paulista - Mogi das Cruzes SP.</div>
                </div>
                
                <div class="dashed-line"></div>
                
                <div class="order-info">
                    <span class="bold">' . str_pad($pedido->id, 5, "0", STR_PAD_LEFT) . '-* ' . ($clienteData["nome"] ?: "Consumidor") . '</span>
                </div>
                
                <div class="dashed-line"></div>
                
                <div class="seller-info">
                    Vendedor: ' . strtoupper($pedido->vendedor->name ?? "SISTEMA") . ' Controle: ' . $pedido->id . '
                </div>';

                if ($clienteData["endereco"]) {
                    $html .= '
                    <div class="dashed-line"></div>
                    <div style="font-size: 10px;">
                        <span class="bold">Endereço:</span> ' . $clienteData["endereco"] . '
                    </div>';
                }

                $html .= '
                <div class="dashed-line"></div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">Descricao</th>
                            <th style="width: 15%;">Qt</th>
                            <th style="width: 10%;">Und</th>
                            <th style="width: 15%;" class="text-right">Unit</th>
                            <th style="width: 20%;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($itens as $item) {
                    $html .= '
                        <tr class="item-row">
                            <td colspan="5">
                                <span class="item-desc">' . $item->produto->descricao . '</span>
                                <span class="item-code">' . $item->produto->codigo . '</span>
                            </td>
                        </tr>
                        <tr class="item-row" style="border-bottom: 1px dotted #eee;">
                            <td></td>
                            <td>' . $item->quantidade . '</td>
                            <td>UN</td>
                            <td class="text-right">' . number_format($item->preco_unitario, 2, ",", ".") . '</td>
                            <td class="text-right">' . number_format($item->subtotal, 2, ",", ".") . '</td>
                        </tr>';
                }

                $html .= '
                    </tbody>
                </table>
                
                <div class="dashed-line"></div>
                
                <div class="totals">
                    <div class="total-line">
                        <span>Total a pagar ....................</span>
                        <span class="bold">R$ ' . number_format($pedido->valor_total, 2, ",", ".") . '</span>
                    </div>
                    <div class="total-line">
                        <span>' . ($pedido->tipoPagamento->nome ?? "Dinheiro") . '..........................</span>
                        <span class="bold">R$ ' . number_format($pedido->valor_pago > 0 ? $pedido->valor_pago : $pedido->valor_total, 2, ",", ".") . '</span>
                    </div>
                </div>
                
                <div class="signature">
                    <div class="sig-line"></div>
                    <div style="font-size: 10px;">Assinatura :</div>
                    <div class="bold" style="font-size: 11px;">* ' . ($clienteData["nome"] ?: "Consumidor") . '</div>
                </div>
                
                <div class="dashed-line" style="border-top-style: double; border-top-width: 3px;"></div>
                
                <div class="no-print" style="text-align:center; margin-top:30px; border-top: 1px solid #ccc; padding-top: 20px;">
                    <button onclick="window.print()" style="padding:12px 24px; background:#F97316; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; box-shadow: 0 4px 6px rgba(249, 115, 22, 0.2);">🖨️ Imprimir Agora</button>
                    <button onclick="window.close()" style="padding:12px 24px; background:#6b7280; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; margin-left:10px;">✕ Fechar</button>
                </div>
                
                <script>
                    if(window.location.search.includes("print=true")) {
                        setTimeout(() => {
                            window.print();
                        }, 500);
                    }
                </script>
            </body>
            </html>';

        return $html;
    }

    private function parseObservacao($observacao)
    {
        $data = [
            "nome" => null,
            "cpf" => null,
            "celular" => null,
            "endereco" => null
        ];

        if (empty($observacao)) return $data;

        if (preg_match('/Cliente:\s*(.+)/', $observacao, $match)) {
            $data["nome"] = trim($match[1]);
        }

        if (preg_match('/CPF:\s*(.+)/', $observacao, $match)) {
            $data["cpf"] = trim($match[1]);
        }

        if (preg_match('/Celular:\s*(.+)/', $observacao, $match)) {
            $data["celular"] = trim($match[1]);
        }

        if (preg_match('/Endereço:\s*(.+)/s', $observacao, $match)) {
            $endereco = trim($match[1]);
            $endereco = preg_replace('/(CPF|Celular|Bairro|CEP):.*/s', '', $endereco);
            $data["endereco"] = trim($endereco);
            
            if (preg_match('/Bairro:\s*(.+)/', $observacao, $m)) {
                $data["endereco"] .= " - Bairro: " . trim($m[1]);
            }
        }

        return $data;
    }
}
