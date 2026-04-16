<?php

namespace App\Livewire;

use App\Models\Produto;
use Livewire\Component;

class PdvComponent extends Component
{
    public $cart = [];
    public $total_venda = 0;
    public $ultimo_preco = 0;
    public $search = '';
    public $searchResults = [];
    public $selectedIndex = -1; // Índice do item selecionado na busca
    public $editingIndex = null; // Índice do item que está editando quantidade
    public $editingQty = ''; // Valor temporário da quantidade

    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $this->searchResults = Produto::where('descricao', 'like', '%' . strtoupper($this->search) . '%')
                ->orWhere('codigo', 'like', "{$this->search}%")
                ->limit(10)
                ->get()
                ->toArray();
            $this->selectedIndex = 0; // Reseta seleção para o primeiro item
        } else {
            $this->searchResults = [];
            $this->selectedIndex = -1;
        }
    }

    // Navegação com setas
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

    // Adiciona o item selecionado ou o item atual
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

    // Inicia edição da quantidade
    public function startEditQty($index)
    {
        $this->editingIndex = $index;
        $this->editingQty = $this->cart[$index]['qty'];
    }

    // Salva a quantidade editada
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

    // Cancela edição
    public function cancelEdit()
    {
        $this->editingIndex = null;
        $this->editingQty = '';
    }

    public function finalizarVenda()
    {
        if (count($this->cart) == 0) {
            session()->flash('error', 'Carrinho vazio!');
            return;
        }

        session()->flash('success', 'Venda finalizada! Total: R$ ' . number_format($this->total_venda, 2, ',', '.'));

        $this->cart = [];
        $this->total_venda = 0;
        $this->ultimo_preco = 0;
    }

    public function render()
    {
        return view('livewire.pdv-component');
    }
}