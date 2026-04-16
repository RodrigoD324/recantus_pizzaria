<div class="grid grid-cols-12 gap-4" x-data="{ showResults: false }">
    <div class="col-span-8">
        <!-- Campo de busca -->
        <div class="mb-4 relative">
            <input 
                type="text" 
                wire:model.live="search" 
                wire:keydown.arrow-up="moveSelectionUp"
                wire:keydown.arrow-down="moveSelectionDown"
                wire:keydown.enter="addSelectedProduct"
                placeholder="PESQUISAR PRODUTOS (NOME OU CÓDIGO) - DIGITE E USE AS SETAS"
                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase p-3 text-lg"
                @focus="showResults = true"
                @blur="setTimeout(() => showResults = false, 200)"
            >
            
            @if(!empty($searchResults) && strlen($search) > 0)
                <div class="absolute z-50 w-full max-w-3xl mt-1 bg-white rounded-lg shadow-lg border border-gray-200" x-show="showResults">
                    @foreach($searchResults as $index => $result)
                        <div 
                            wire:click="addToCart({{ $result['id'] }})"
                            wire:key="result-{{ $index }}"
                            class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 transition"
                            @if($selectedIndex == $index)
                                style="background-color: #e0e7ff; border-left: 4px solid #4f46e5;"
                            @endif
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-700">{{ $result['codigo'] }}</span>
                                    <span class="mx-2">-</span>
                                    <span class="text-gray-800">{{ $result['descricao'] }}</span>
                                </div>
                                <div>
                                    <span class="text-green-600 font-bold text-lg">R$ {{ number_format($result['valor'], 2, ',', '.') }}</span>
                                </div>
                            </div>
                            @if($selectedIndex == $index)
                                <div class="text-xs text-indigo-600 mt-1">
                                    ⏎ Enter para adicionar | ↑ ↓ para navegar
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Tabela do Carrinho -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">PRODUTO</th>
                        <th class="px-4 py-3 text-center">QUANTIDADE</th>
                        <th class="px-4 py-3 text-right">UNITÁRIO</th>
                        <th class="px-4 py-3 text-right">TOTAL</th>
                        <th class="px-4 py-3 text-center">AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cart as $index => $item)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3 font-medium uppercase">{{ $item['name'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($editingIndex === $index)
                                    <div class="flex items-center justify-center gap-2">
                                        <input 
                                            type="number" 
                                            wire:model="editingQty" 
                                            wire:keydown.enter="saveQty"
                                            wire:keydown.escape="cancelEdit"
                                            class="w-20 text-center rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                            min="1"
                                            autofocus
                                        >
                                        <button 
                                            type="button" 
                                            wire:click="saveQty"
                                            class="text-green-600 hover:text-green-800"
                                        >
                                            ✓
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="cancelEdit"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            ✗
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" wire:click="decrementQty({{ $index }})"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full transition">
                                            -
                                        </button>
                                        <span 
                                            wire:click="startEditQty({{ $index }})"
                                            class="font-bold w-12 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded px-2 py-1 transition"
                                            title="Clique para editar quantidade"
                                        >
                                            {{ $item['qty'] }}
                                        </span>
                                        <button type="button" wire:click="incrementQty({{ $index }})"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-full transition">
                                            +
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">R$ {{ number_format($item['price'], 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition">
                                    Remover
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Carrinho vazio. Adicione produtos usando a busca acima.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($cart) > 0)
                <tfoot class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-bold text-lg">TOTAL:</td>
                        <td class="px-4 py-3 text-right font-bold text-lg">R$ {{ number_format($total_venda, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="col-span-4 space-y-4">
        <div class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
            <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">ÚLTIMO PREÇO</p>
            <p class="text-4xl font-black">R$ {{ number_format($ultimo_preco, 2, ',', '.') }}</p>
        </div>

        <div class="p-6 bg-emerald-50 dark:bg-emerald-950 rounded-xl border border-emerald-200 dark:border-emerald-800">
            <p class="text-xs font-bold text-emerald-600 uppercase">TOTAL GERAL</p>
            <p class="text-6xl font-black text-emerald-700 dark:text-emerald-400">R$ {{ number_format($total_venda, 2, ',', '.') }}</p>
        </div>

        <div class="p-4">
            <button type="button" wire:click="finalizarVenda"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-xl transition text-lg"
                {{ count($cart) == 0 ? 'disabled' : '' }}>
                FINALIZAR VENDA
            </button>
        </div>
    </div>
    
    <!-- Notificações -->
    @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-purple-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>