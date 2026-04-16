<div>
    <div class="grid grid-cols-12 gap-4" x-data="{ showResults: false }">
        <div class="col-span-8">
            <div class="mb-4 relative">
                <input type="text" wire:model.live="search" wire:keydown.arrow-up="moveSelectionUp"
                    wire:keydown.arrow-down="moveSelectionDown" wire:keydown.enter="addSelectedProduct"
                    placeholder="PESQUISAR PRODUTOS (NOME OU CÓDIGO) - DIGITE E USE AS SETAS"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase p-3 text-lg"
                    @focus="showResults = true" @blur="setTimeout(() => showResults = false, 200)">

                @if(!empty($searchResults) && strlen($search) > 0)
                    <div class="absolute z-50 w-full max-w-3xl mt-1 bg-white rounded-lg shadow-lg border border-gray-200"
                        x-show="showResults">
                        @foreach($searchResults as $index => $result)
                            <div wire:click="addToCart({{ $result['id'] }})" wire:key="result-{{ $index }}"
                                class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 transition"
                                @if($selectedIndex == $index) style="background-color: #e0e7ff; border-left: 4px solid #4f46e5;"
                                @endif>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-gray-700">{{ $result['codigo'] }}</span>
                                        <span class="mx-2">-</span>
                                        <span class="text-gray-800">{{ $result['descricao'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-green-600 font-bold text-lg">R$
                                            {{ number_format($result['valor'], 2, ',', '.') }}</span>
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

            <div
                class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
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
                                            <input type="number" wire:model="editingQty" wire:keydown.enter="saveQty"
                                                wire:keydown.escape="cancelEdit"
                                                class="w-20 text-center rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                min="1" autofocus>
                                            <button type="button" wire:click="saveQty"
                                                class="text-green-600 hover:text-green-800">
                                                ✓
                                            </button>
                                            <button type="button" wire:click="cancelEdit"
                                                class="text-red-600 hover:text-red-800">
                                                ✗
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" wire:click="decrementQty({{ $index }})"
                                                class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full transition">
                                                -
                                            </button>
                                            <span wire:click="startEditQty({{ $index }})"
                                                class="font-bold w-12 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded px-2 py-1 transition"
                                                title="Clique para editar quantidade">
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
                                <td class="px-4 py-3 text-right font-bold">R$
                                    {{ number_format($item['subtotal'], 2, ',', '.') }}
                                </td>
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
                                <td class="px-4 py-3 text-right font-bold text-lg">R$
                                    {{ number_format($total_venda, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="col-span-4">
            <div class="sticky top-4">
                <button type="button" wire:click="abrirModalPagamento"
                    style="width: 100%; background-color: #F97316; color: white; font-weight: bold; padding: 1.5rem 1.5rem; border-radius: 0.75rem; transition: all 0.3s; font-size: 1.25rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: none; cursor: pointer; {{ count($cart) == 0 ? 'opacity: 0.5; cursor: not-allowed;' : '' }}"
                    onmouseover="this.style.backgroundColor='#EA580C'" onmouseout="this.style.backgroundColor='#F97316'"
                    {{ count($cart) == 0 ? 'disabled' : '' }}>
                    FINALIZAR VENDA
                </button>
            </div>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data="{ show: true }" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.away="$wire.fecharModal()"
            style="background-color: rgba(0, 0, 0, 0.5);">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform scale-95 opacity-0"
                x-transition:enter-end="transform scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="transform scale-100 opacity-100"
                x-transition:leave-end="transform scale-95 opacity-0">

                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Finalizar Venda</h2>
                        <button wire:click="fecharModal"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-2xl">
                            ×
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-emerald-50 dark:bg-emerald-950 p-4 rounded-lg">
                            <p class="text-sm text-emerald-600 dark:text-emerald-400">TOTAL DA VENDA</p>
                            <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-400">
                                R$ {{ number_format($total_venda, 2, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Forma de
                                Pagamento *</label>
                            <select wire:model.live="selectedPaymentType"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione...</option>
                                @foreach($tiposPagamento as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($selectedPaymentType && $showTroco)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor Recebido
                                    *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">R$</span>
                                    <input type="text" wire:model.live="valorPagoRaw" placeholder="0,00"
                                        class="w-full pl-8 pr-3 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500">
                                </div>
                            </div>
                        @endif

                        @if($troco > 0)
                            <div class="bg-blue-50 dark:bg-blue-950 p-3 rounded-lg">
                                <p class="text-sm text-blue-600 dark:text-blue-400">TROCO</p>
                                <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">R$
                                    {{ number_format($troco, 2, ',', '.') }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observação</label>
                            <textarea wire:model="observacao" rows="2" placeholder="Ex: Endereço de entrega..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500"></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" wire:click="fecharModal"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition" style="background: gray;">
                                Cancelar
                            </button>
                            <button type="button" wire:click="finalizarVenda"
                                class="flex-1 text-white font-bold py-3 px-4 rounded-lg transition"
                                style="background-color: #F97316;" onmouseover="this.style.backgroundColor='#EA580C'"
                                onmouseout="this.style.backgroundColor='#F97316'" {{ (!$selectedPaymentType || ($showTroco && str_replace(['R$', '.', ','], ['', '', '.'], $valorPago) < $total_venda)) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
                                Confirmar Venda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>