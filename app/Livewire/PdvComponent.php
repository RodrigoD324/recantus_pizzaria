<?php

namespace App\Livewire;

use App\Models\Produto;
use App\Models\Pedido;
use App\Models\PedidoProduto;
use App\Models\PedidoStatus;
use App\Models\TipoPagamento;
use Livewire\Component;

class PdvComponent extends Component
{
    public $cart = [];
    public $total_venda = 0;
    public $ultimo_preco = 0;
    public $search = '';
    public $searchResults = [];
    public $selectedIndex = -1;
    public $editingIndex = null;
    public $editingQty = '';

    public $showPaymentModal = false;
    public $tiposPagamento = [];
    public $selectedPaymentType = null;
    public $valorPago = '';
    public $troco = 0;
    public $observacao = '';
    public $showTroco = false;
    public $valorPagoRaw = '';

    public function mount()
    {
        $this->carregarTiposPagamento();
    }

    public function carregarTiposPagamento()
    {
        $this->tiposPagamento = TipoPagamento::whereNull('id_cancelamento')->get();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $this->searchResults = Produto::where('descricao', 'like', '%' . strtoupper($this->search) . '%')
                ->orWhere('codigo', 'like', "{$this->search}%")
                ->whereNull('id_cancelamento')
                ->limit(10)
                ->get()
                ->toArray();
            $this->selectedIndex = 0;
        } else {
            $this->searchResults = [];
            $this->selectedIndex = -1;
        }
    }

    public function moveSelectionUp()
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    public function moveSelectionDown()
    {
        if ($this->selectedIndex < count($this->searchResults) - 1) {
            $this->selectedIndex++;
        }
    }

    public function addSelectedProduct()
    {
        if ($this->selectedIndex >= 0 && isset($this->searchResults[$this->selectedIndex])) {
            $this->addToCart($this->searchResults[$this->selectedIndex]['id']);
        }
    }

    public function addToCart($productId)
    {
        $product = Produto::find($productId);

        if (!$product)
            return;

        $exists = false;
        foreach ($this->cart as &$item) {
            if ($item['id'] == $product->id) {
                $item['qty']++;
                $item['subtotal'] = $item['qty'] * $item['price'];
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->descricao,
                'qty' => 1,
                'price' => $product->valor,
                'subtotal' => $product->valor,
            ];
        }

        $this->ultimo_preco = $product->valor;
        $this->calculateTotal();
        $this->search = '';
        $this->searchResults = [];
        $this->selectedIndex = -1;
    }

    public function calculateTotal()
    {
        $this->total_venda = collect($this->cart)->sum('subtotal');
    }

    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    public function incrementQty($index)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['qty']++;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
            $this->calculateTotal();
        }
    }

    public function decrementQty($index)
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['qty'] > 1) {
                $this->cart[$index]['qty']--;
                $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
                $this->calculateTotal();
            } else {
                $this->removeItem($index);
            }
        }
    }

    public function startEditQty($index)
    {
        $this->editingIndex = $index;
        $this->editingQty = $this->cart[$index]['qty'];
    }

    public function saveQty()
    {
        if ($this->editingIndex !== null && isset($this->cart[$this->editingIndex])) {
            $newQty = (int) $this->editingQty;
            if ($newQty > 0) {
                $this->cart[$this->editingIndex]['qty'] = $newQty;
                $this->cart[$this->editingIndex]['subtotal'] = $this->cart[$this->editingIndex]['qty'] * $this->cart[$this->editingIndex]['price'];
                $this->calculateTotal();
            }
        }
        $this->editingIndex = null;
        $this->editingQty = '';
    }

    public function cancelEdit()
    {
        $this->editingIndex = null;
        $this->editingQty = '';
    }

    public function abrirModalPagamento()
    {
        if (count($this->cart) == 0) {
            session()->flash('error', 'Carrinho vazio!');
            return;
        }

        $this->showPaymentModal = true;
        $this->selectedPaymentType = null;
        $this->valorPago = '';
        $this->valorPagoRaw = '';
        $this->troco = 0;
        $this->observacao = '';
        $this->showTroco = false;
    }

    public function fecharModal()
    {
        $this->showPaymentModal = false;
        $this->reset(['selectedPaymentType', 'valorPago', 'valorPagoRaw', 'troco', 'observacao']);
        $this->showTroco = false;
    }

    public function updatedSelectedPaymentType($value)
    {
        // Se não tiver valor selecionado ou for vazio, reseta os campos
        if (empty($value)) {
            $this->showTroco = false;
            $this->valorPago = '';
            $this->troco = 0;
            return;
        }

        $tipo = TipoPagamento::find($value);

        if (!$tipo) {
            $this->showTroco = false;
            $this->valorPago = '';
            $this->troco = 0;
            return;
        }

        $this->showTroco = $tipo->permite_troco;

        if ($this->showTroco) {
            // Para dinheiro, não preenche automaticamente
            $this->valorPago = '';
            $this->troco = 0;
        } else {
            // Para outras formas de pagamento, preenche com o valor total
            $this->valorPago = 'R$ ' . number_format($this->total_venda, 2, ',', '.');
            $this->calcularTroco();
        }
    }

    public function updatedValorPago($value)
    {
        $this->calcularTroco();
    }

    public function updatedValorPagoRaw($value)
    {
        $numbers = preg_replace('/\D/', '', $value);
        $numbers = substr($numbers, 0, 9);

        if (strlen($numbers) >= 3) {
            $reais = substr($numbers, 0, -2);
            $centavos = substr($numbers, -2);
            $reais = ltrim($reais, '0');
            if ($reais === '') $reais = '0';
            $this->valorPago = number_format((float)($reais . '.' . $centavos), 2, ',', '.');
        } elseif (strlen($numbers) > 0) {
            if (strlen($numbers) == 1) {
                $this->valorPago = "0,0{$numbers}";
            } elseif (strlen($numbers) == 2) {
                $this->valorPago = "0,{$numbers}";
            }
        } else {
            $this->valorPago = '';
        }

        $this->calcularTroco();
    }

    public function calcularTroco()
    {
        $valorPago = $this->parseMoney($this->valorPago);

        if ($valorPago > $this->total_venda) {
            $this->troco = $valorPago - $this->total_venda;
        } else {
            $this->troco = 0;
        }
    }

    private function parseMoney($value)
    {
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }

    public function finalizarVenda()
    {
        if (count($this->cart) == 0) {
            session()->flash('error', 'Carrinho vazio!');
            return;
        }

        if (!$this->selectedPaymentType) {
            session()->flash('error', 'Selecione uma forma de pagamento!');
            return;
        }

        $tipoPagamento = TipoPagamento::find($this->selectedPaymentType);
        $valorPago = $this->parseMoney($this->valorPago);

        if ($tipoPagamento->referencia != 'fiado') {
            if (!$valorPago || $valorPago <= 0) {
                dd("Primeiro IF");
                return session()->flash('error', 'Informe o valor pago!');
            }

            if ($valorPago < $this->total_venda) {
                dd("Segundo IF");
                return session()->flash('error', 'Valor pago é menor que o total da venda!');
            }
        }

        try {
            if ($tipoPagamento->referencia == 'fiado') {
                $status = PedidoStatus::where('referencia', 'pendente')->first();
                $valorPagoFinal = 0;
                $valorAPagar = $this->total_venda;
                $trocoFinal = 0;
            } else {
                $status = PedidoStatus::where('referencia', 'Finalizado')->first();
                $valorPagoFinal = $valorPago;
                $valorAPagar = 0;
                $trocoFinal = $this->troco;
            }

            dd([
                'id_tipo_pagamento' => $this->selectedPaymentType,
                'id_vendedor' => auth()->id(),
                'id_pedido_status' => $status->id,
                'valor_total' => $this->total_venda,
                'valor_pago' => $valorPagoFinal,
                'valor_a_pagar' => $valorAPagar,
                'troco' => $trocoFinal,
                'observacao' => empty($this->observacao) ? null : $this->observacao,
            ]);

            $pedido = Pedido::create([
                'id_tipo_pagamento' => $this->selectedPaymentType,
                'id_vendedor' => auth()->id(),
                'id_pedido_status' => $status->id,
                'valor_total' => $this->total_venda,
                'valor_pago' => $valorPagoFinal,
                'valor_a_pagar' => $valorAPagar,
                'troco' => $trocoFinal,
                'observacao' => $this->observacao,
            ]);

            foreach ($this->cart as $item) {
                PedidoProduto::create([
                    'id_pedido' => $pedido->id,
                    'id_produto' => $item['id'],
                    'quantidade' => $item['qty'],
                    'preco_unitario' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $mensagem = "✅ Venda finalizada com sucesso!\n";
            $mensagem .= "📋 Pedido #{$pedido->numero_pedido}\n";
            $mensagem .= "💰 Total: R$ " . number_format($this->total_venda, 2, ',', '.') . "\n";
            $mensagem .= "💳 Forma: {$tipoPagamento->nome}";

            if ($tipoPagamento->referencia == 'dinheiro') {
                $mensagem .= "\n⏳ Status: Pendente - Cliente ficou devendo R$ " . number_format($valorAPagar, 2, ',', '.');
            } elseif ($trocoFinal > 0) {
                $mensagem .= "\n💵 Troco: R$ " . number_format($trocoFinal, 2, ',', '.');
            }

            session()->flash('success', $mensagem);

            $this->cart = [];
            $this->total_venda = 0;
            $this->ultimo_preco = 0;
            $this->fecharModal();

            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao finalizar venda: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pdv-component');
    }
}
