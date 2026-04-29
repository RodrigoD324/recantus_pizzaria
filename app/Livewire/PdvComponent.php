<?php

namespace App\Livewire;

use App\Models\OperacaoPdv;
use App\Models\OperacaoPdvTipo;
use App\Traits\HasPrintableComanda;
use App\Models\Pessoa;
use App\Models\Produto;
use App\Models\Pedido;
use App\Models\PedidoProduto;
use App\Models\PedidoStatus;
use App\Models\TipoPagamento;
use Livewire\Component;

class PdvComponent extends Component
{
    use HasPrintableComanda;
    
    public $isPdvAberto = false;
    public $showOperacaoModal = false;
    public $operacaoValor = '';
    public $operacaoObservacao = '';
    public $operacaoTipo = '';

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

    public $clienteSearch = '';
    public $clienteSearchResults = [];
    public $clienteSelectedIndex = -1;
    public $selectedCliente = null;
    public $selectedClienteNome = '';

    public $showPrintModal = false;
    public $printQty = 2;
    public $lastPedido = null;

    public function mount()
    {
        $this->isPdvAberto = OperacaoPdv::isAberto();
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

    public function updatedClienteSearch()
    {
        if (strlen($this->clienteSearch) > 0) {
            $searchTerm = strtolower($this->clienteSearch);

            $this->clienteSearchResults = Pessoa::whereNull('pessoa.id_cancelamento')
                ->leftJoin('contato', 'pessoa.id_contato', '=', 'contato.id')
                ->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(pessoa.nome) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhere('pessoa.cpf', 'like', "%{$searchTerm}%")
                        ->orWhereRaw('LOWER(contato.celular) LIKE ?', ['%' . $searchTerm . '%']);
                })
                ->select('pessoa.id', 'pessoa.nome', 'pessoa.cpf', 'contato.celular')
                ->limit(10)
                ->get()
                ->map(function ($pessoa) {
                    return [
                        'id' => $pessoa->id,
                        'nome' => $pessoa->nome,
                        'cpf' => $pessoa->cpf,
                        'celular' => $pessoa->celular ?? 'N/A'
                    ];
                })
                ->toArray();

            $this->clienteSelectedIndex = 0;
        } else {
            $this->clienteSearchResults = [];
            $this->clienteSelectedIndex = -1;
        }
    }

    public function moveClienteSelectionUp()
    {
        if ($this->clienteSelectedIndex > 0) {
            $this->clienteSelectedIndex--;
        }
    }

    public function moveClienteSelectionDown()
    {
        if ($this->clienteSelectedIndex < count($this->clienteSearchResults) - 1) {
            $this->clienteSelectedIndex++;
        }
    }

    public function selectCliente($clienteId)
    {
        $cliente = Pessoa::with(['contato', 'endereco'])->find($clienteId);
        if ($cliente) {
            $this->selectedCliente = $cliente->id;
            $this->selectedClienteNome = $cliente->nome;
            $this->clienteSearch = '';
            $this->clienteSearchResults = [];

            $celular = $cliente->contato->celular ?? '';
            $endereco = $cliente->endereco;

            $enderecoCompleto = '';
            if ($endereco) {
                $enderecoCompleto = "Endereço: ";
                $enderecoCompleto .= $endereco->logradouro ?: '';
                if ($endereco->numero)
                    $enderecoCompleto .= ", {$endereco->numero}";
                if ($endereco->complemento)
                    $enderecoCompleto .= " - {$endereco->complemento}";
                if ($endereco->bairro)
                    $enderecoCompleto .= "\nBairro: {$endereco->bairro}";
                if ($endereco->cidade)
                    $enderecoCompleto .= " - {$endereco->cidade}";
                if ($endereco->estado)
                    $enderecoCompleto .= "/{$endereco->estado}";
                if ($endereco->cep)
                    $enderecoCompleto .= "\nCEP: {$endereco->cep}";
            }

            $this->observacao = "Cliente: {$cliente->nome}\n";
            $this->observacao .= "CPF: {$cliente->cpf}\n";
            $this->observacao .= "Celular: {$celular}\n";
            if ($enderecoCompleto) {
                $this->observacao .= $enderecoCompleto;
            }
            $this->observacao;

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Cliente selecionado',
                'message' => "{$cliente->nome} foi adicionado à venda."
            ]);
        }
    }

    public function addSelectedCliente()
    {
        if ($this->clienteSelectedIndex >= 0 && isset($this->clienteSearchResults[$this->clienteSelectedIndex])) {
            $this->selectCliente($this->clienteSearchResults[$this->clienteSelectedIndex]['id']);
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
        if (!$this->isPdvAberto) {
            $this->showOperacaoModal = true;
            $this->operacaoTipo = 'abertura';
            return $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => 'Caixa Fechado',
                'message' => 'Você precisa abrir o caixa antes de realizar vendas.'
            ]);
        }
        
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

        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => 'Produto adicionado!',
            'message' => "{$product->descricao}\nR$ " . number_format($product->valor, 2, ',', '.')
        ]);
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
        if (!$this->isPdvAberto) {
            $this->showOperacaoModal = true;
            $this->operacaoTipo = 'abertura';
            return;
        }

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
            $valorTotalFormatado = number_format($this->total_venda, 2, ',', '.');
            $this->valorPago = $valorTotalFormatado;
            $this->valorPagoRaw = $valorTotalFormatado;
            $this->calcularTroco();
        } else {
            $this->valorPago = number_format($this->total_venda, 2, ',', '.');
            $this->valorPagoRaw = number_format($this->total_venda, 2, ',', '.');
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
            if ($reais === '')
                $reais = '0';
            $this->valorPago = number_format((float) ($reais . '.' . $centavos), 2, ',', '.');
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
            return $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Carrinho Vazio',
                'message' => 'Adicione produtos antes de finalizar a venda.'
            ]);
        }

        if (!$this->selectedPaymentType) {
            return $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Pagamento não selecionado',
                'message' => 'Selecione uma forma de pagamento.'
            ]);
        }

        $tipoPagamento = TipoPagamento::find($this->selectedPaymentType);
        $valorPago = $this->parseMoney($this->valorPago);

        if ($tipoPagamento->referencia != 'fiado') {
            if (!$valorPago || $valorPago <= 0) {
                return $this->dispatch('showNotification', [
                    'type' => 'error',
                    'title' => 'Valor inválido',
                    'message' => 'Informe o valor recebido.'
                ]);
            }
            if ($valorPago < $this->total_venda) {
                $falta = number_format($this->total_venda - $valorPago, 2, ',', '.');
                return $this->dispatch('showNotification', [
                    'type' => 'warning',
                    'title' => 'Valor insuficiente',
                    'message' => "Faltam R$ {$falta} para completar o pagamento."
                ]);
            }
        }

        try {
            if ($tipoPagamento->referencia == 'fiado') {
                $status = PedidoStatus::where('referencia', 'pendente')->first();
                $valorPagoFinal = 0;
                $valorAPagar = $this->total_venda;
                $trocoFinal = 0;
            } else {
                $status = PedidoStatus::where('referencia', 'finalizado')->first();
                $valorPagoFinal = $valorPago;
                $valorAPagar = 0;
                $trocoFinal = $this->troco;
            }

            $pedido = Pedido::create([
                'id_tipo_pagamento' => $this->selectedPaymentType,
                'id_vendedor' => auth()->id(),
                'id_pedido_status' => $status->id,
                'valor_total' => $this->total_venda,
                'valor_pago' => $valorPagoFinal,
                'valor_a_pagar' => $valorAPagar,
                'troco' => $trocoFinal,
                'observacao' => empty($this->observacao) ? null : $this->observacao,
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

            $mensagem = "Total: R$ " . number_format($this->total_venda, 2, ',', '.') . "\n";
            $mensagem .= "Pagamento: {$tipoPagamento->nome}\n";

            if ($tipoPagamento->referencia == 'credit_account') {
                $mensagem .= "Status: Pendente\n";
                $mensagem .= "Débito: R$ " . number_format($valorAPagar, 2, ',', '.');
            } elseif ($trocoFinal > 0) {
                $mensagem .= "Troco: R$ " . number_format($trocoFinal, 2, ',', '.');
            } else {
                $mensagem .= "Pagamento confirmado";
            }

            $this->cart = [];
            $this->total_venda = 0;
            $this->ultimo_preco = 0;
            $this->fecharModal();

            $this->abrirModalImpressao($pedido->id);

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => "Venda Finalizada!",
                'message' => "Pedido #{$pedido->numero_pedido}\n{$mensagem}"
            ]);

            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            dd($e);
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Erro na venda',
                'message' => 'Ocorreu um erro ao processar a venda. Tente novamente.'
            ]);
            \Log::error('Erro ao finalizar venda: ' . $e->getMessage());
        }
    }

    public function abrirModalImpressao($pedidoId)
    {
        $this->lastPedido = Pedido::with(['itens.produto', 'vendedor'])->find($pedidoId);
        $this->printQty = 2;
        $this->showPrintModal = true;
    }

    public function emitirComanda()
    {
        if (!$this->lastPedido) {
            return;
        }

        $html = $this->generateComandaHtml($this->lastPedido);

        $this->fecharModalImpressao();

        $this->dispatch('print-order', [
            'html' => $html,
            'qty' => $this->printQty
        ]);
    }

    public function fecharModalImpressao()
    {
        $this->showPrintModal = false;
        $this->printQty = 2;
        $this->lastPedido = null;
    }

    public function abrirOperacaoPdv($tipo)
    {
        $this->operacaoTipo = $tipo;
        $this->operacaoValor = '';
        $this->operacaoObservacao = '';
        $this->showOperacaoModal = true;
    }

    public function confirmarOperacaoPdv()
    {
        $this->validate([
            'operacaoValor' => 'required',
        ]);

        $valor = $this->parseMoney($this->operacaoValor);
        $tipoObj = OperacaoPdvTipo::where('referencia', $this->operacaoTipo)->first();

        OperacaoPdv::create([
            'id_usuario' => auth()->id(),
            'id_operacao_pdv_tipo' => $tipoObj->id,
            'valor' => $valor,
            'observacao' => $this->operacaoObservacao,
        ]);

        $this->isPdvAberto = OperacaoPdv::isAberto();
        $this->showOperacaoModal = false;

        $msg = $this->operacaoTipo == 'abertura' ? 'Caixa aberto com sucesso!' : 'Caixa fechado com sucesso!';
        
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => 'Operação Realizada',
            'message' => $msg
        ]);

        if ($this->operacaoTipo == 'fechamento') {
            $this->reset(['cart', 'total_venda', 'ultimo_preco']);
        }
    }

    public function render()
    {
        return view('livewire.pdv-component');
    }
}
