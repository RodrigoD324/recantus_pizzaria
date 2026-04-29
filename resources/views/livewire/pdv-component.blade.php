<div>
    <div class="grid grid-cols-12 gap-4" x-data="{ showResults: false }">
        <div class="col-span-12 mb-2 flex justify-between items-center bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isPdvAberto ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isPdvAberto ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <span class="font-bold text-sm uppercase tracking-wider {{ $isPdvAberto ? 'text-green-600' : 'text-red-600' }}">
                        {{ $isPdvAberto ? 'Caixa Aberto' : 'Caixa Fechado' }}
                    </span>
                </div>
            </div>
            <div>
                @if($isPdvAberto)
                    <button wire:click="abrirOperacaoPdv('fechamento')" class="bg-red-100 text-red-700 px-4 py-1.5 rounded-lg font-bold text-xs uppercase hover:bg-red-200 transition">
                        Encerrar Caixa
                    </button>
                @else
                    <button wire:click="abrirOperacaoPdv('abertura')" class="bg-green-100 text-green-700 px-4 py-1.5 rounded-lg font-bold text-xs uppercase hover:bg-green-200 transition">
                        Abrir Caixa
                    </button>
                @endif
            </div>
        </div>

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
                        @if($selectedIndex==$index) style="background-color: #e0e7ff; border-left: 4px solid #4f46e5;"
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
                                        class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-black rounded-full transition">
                                        -
                                    </button>
                                    <span wire:click="startEditQty({{ $index }})"
                                        class="font-bold w-12 text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded px-2 py-1 transition"
                                        title="Clique para editar quantidade">
                                        {{ $item['qty'] }}
                                    </span>
                                    <button type="button" wire:click="incrementQty({{ $index }})"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-green-500 hover:bg-green-600 text-black rounded-full transition">
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
    <div class="fixed bottom-4 right-4 bg-green-500 text-black px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
    @endif

    @if(session()->has('error'))
    <div class="fixed bottom-4 right-4 bg-red-500 text-black px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
    @endif

    @if(session()->has('success'))
    <div class="fixed bottom-4 right-4 bg-green-600 text-black px-6 py-3 rounded-lg shadow-lg z-50">
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
                            <!-- <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">R$</span> -->
                            <input type="text" wire:model.live="valorPago" placeholder="0,00" x-data="{}" x-on:input="
                                                    let numbers = $el.value.replace(/\D/g, '').slice(0, 9);
                                                    numbers = numbers.padStart(3, '0');
                                                    let reais = numbers.slice(0, -2);
                                                    let centavos = numbers.slice(-2);
                                                    reais = reais.replace(/^0+(\d)/, '$1');
                                                    let formatted = Number(reais).toLocaleString('pt-BR') + ',' + centavos;
                                                    if (reais === '' || reais === '0') formatted = '0,' + centavos;
                                                    $el.value = formatted;
                                                    $wire.set('valorPago', formatted);
                                                "
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

                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cliente (Nome,
                            CPF ou Celular)</label>
                        <input type="text" wire:model.live="clienteSearch"
                            wire:keydown.arrow-up="moveClienteSelectionUp"
                            wire:keydown.arrow-down="moveClienteSelectionDown" wire:keydown.enter="addSelectedCliente"
                            placeholder="Digite nome, CPF ou celular..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500 p-2">

                        @if(!empty($clienteSearchResults) && strlen($clienteSearch) > 0)
                        <div
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 max-h-60 overflow-y-auto">
                            @foreach($clienteSearchResults as $index => $result)
                            <div wire:click="selectCliente({{ $result['id'] }})" wire:key="cliente-{{ $index }}"
                                class="p-3 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-600 transition"
                                @if($clienteSelectedIndex==$index)
                                style="background-color: #e0e7ff; border-left: 4px solid #4f46e5;" @endif>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-gray-700 dark:text-white">{{ $result['nome'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            CPF: {{ $result['cpf'] ?: 'Não informado' }} | Celular:
                                            {{ $result['celular'] }}
                                        </div>
                                    </div>
                                    <div class="text-green-600 dark:text-green-400 text-sm">
                                        Selecionar →
                                    </div>
                                </div>
                                @if($clienteSelectedIndex == $index)
                                <div class="text-xs text-indigo-600 mt-1">
                                    ⏎ Enter para selecionar | ↑ ↓ para navegar
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observação</label>
                        <textarea wire:model="observacao" rows="6" placeholder="Ex: Endereço de entrega..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" wire:click="fecharModal"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition"
                            style="background: gray;">
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

    <!-- Modal de Impressão -->
    @if($showPrintModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 transition-all duration-300" 
         x-data="{ show: @entangle('showPrintModal') }" x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <!-- Backdrop com fade escuro e blur -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md"></div>

        <!-- Conteúdo do Modal -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full z-10 overflow-hidden transform transition-all border border-gray-100 dark:border-gray-700"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 translate-y-4 opacity-0"
             x-transition:enter-end="scale-100 translate-y-0 opacity-100">
            
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-2xl">🖨️</span> Imprimir Comanda
                    </h2>
                    <button wire:click="fecharModalImpressao" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-3 text-center uppercase tracking-wider">Quantidade de cópias</label>
                        <div class="flex items-center justify-center gap-6">
                            <button wire:click="$set('printQty', {{ $printQty > 1 ? $printQty - 1 : 1 }})" 
                                    style="background-color: #E5E7EB !important; color: #1F2937 !important;"
                                    class="w-12 h-12 flex items-center justify-center rounded-full shadow-sm hover:bg-gray-300 transition-all active:scale-90 border border-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            
                            <span class="text-3xl font-black text-orange-500 w-12 text-center tabular-nums">{{ $printQty }}</span>
                            
                            <button wire:click="$set('printQty', {{ $printQty < 10 ? $printQty + 1 : 10 }})" 
                                    style="background-color: #E5E7EB !important; color: #1F2937 !important;"
                                    class="w-12 h-12 flex items-center justify-center rounded-full shadow-sm hover:bg-gray-300 transition-all active:scale-90 border border-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button wire:click="emitirComanda" 
                                style="background-color: #F97316 !important; color: white !important;"
                                class="w-full font-bold py-4 rounded-xl shadow-lg transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                            <span>🖨️</span> CONFIRMAR IMPRESSÃO
                        </button>
                        
                        <button wire:click="fecharModalImpressao" 
                                style="background-color: #6B7280 !important; color: white !important;"
                                class="w-full font-semibold py-3 rounded-xl transition-all active:scale-[0.98]">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal Operação PDV (Abertura/Fechamento) -->
    @if($showOperacaoModal)
    <div class="fixed inset-0 z-[70] flex items-center justify-center p-4 shadow-2xl" 
         style="background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px);">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                        {{ $operacaoTipo == 'abertura' ? 'Abrir Caixa' : 'Encerrar Caixa' }}
                    </h2>
                    <button wire:click="$set('showOperacaoModal', false)" class="text-gray-400 hover:text-gray-600 transition text-2xl">×</button>
                </div>

                <div class="space-y-5">
                    <div class="bg-indigo-50 dark:bg-indigo-900/30 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800">
                        <label class="block text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-2 uppercase tracking-widest">
                            Valor em Dinheiro
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400 font-bold text-xl">R$</span>
                            <input type="text" wire:model.live="operacaoValor" 
                                placeholder="0,00"
                                class="w-full pl-12 pr-4 py-4 rounded-xl border-none bg-white dark:bg-gray-900 shadow-inner text-2xl font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                x-data="{}" 
                                x-on:input="
                                    let numbers = $el.value.replace(/\D/g, '').slice(0, 9);
                                    numbers = numbers.padStart(3, '0');
                                    let reais = numbers.slice(0, -2);
                                    let centavos = numbers.slice(-2);
                                    reais = reais.replace(/^0+(\d)/, '$1');
                                    let formatted = Number(reais).toLocaleString('pt-BR') + ',' + centavos;
                                    if (reais === '' || reais === '0') formatted = '0,' + centavos;
                                    $el.value = formatted;
                                    $wire.set('operacaoValor', formatted);
                                ">
                        </div>
                        @error('operacaoValor') <span class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Observações (Opcional)</label>
                        <textarea wire:model="operacaoObservacao" rows="3" 
                            class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Alguma observação importante?"></textarea>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button wire:click="$set('showOperacaoModal', false)" 
                            class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold py-4 rounded-xl hover:bg-gray-200 transition uppercase tracking-wider text-sm">
                            Cancelar
                        </button>
                        <button wire:click="confirmarOperacaoPdv" 
                            class="flex-1 bg-indigo-600 text-white font-bold py-4 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none transition uppercase tracking-wider text-sm">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
           Livewire.on('print-order', (event) => {
               const data = Array.isArray(event) ? event[0] : event;
               const html = data.html;
               const qty = data.qty || 1;
               
               console.log('Imprimindo...', qty, 'cópias');
               
               for (let i = 0; i < qty; i++) {
                   setTimeout(() => {
                       const w = window.open('', '_blank', 'width=800,height=600');
                       w.document.write(html);
                       w.document.close();
                       
                       w.onload = function() {
                           w.focus();
                           w.print();
                           setTimeout(() => w.close(), 1000);
                       };
                       
                       // Fallback se o onload não disparar (alguns browsers com document.write)
                       setTimeout(() => {
                           if (!w.closed) {
                               w.focus();
                               w.print();
                               setTimeout(() => w.close(), 1000);
                           }
                       }, 1000);
                       
                   }, i * 1500);
               }
           });
        });
    </script>
</div>