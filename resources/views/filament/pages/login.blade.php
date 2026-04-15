<x-filament-panels::page.simple>
    <div class="fixed inset-0 flex">
        <div class="hidden w-2/3 bg-gray-100 lg:flex items-center justify-center">
            <img src="{{ asset('assets/imgs/pizza-maker.png') }}"
                alt="Pizzaria Recantu's"
                class="w-3/4 h-auto object-contain">
        </div>

        <div class="w-full lg:w-1/3 flex flex-col justify-center px-8 bg-white dark:bg-gray-900">
            <div class="mx-auto w-full max-w-md">
                <div class="text-center mb-8">
                    <img src="{{ asset('assets/imgs/recantus_logo.png') }}"
                        alt="Logo"
                        style="height: 160px !important; width: auto !important; margin: 0 auto;">

                    <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight">
                        Gestão Recantu's
                    </h1>
                    <p class="text-stone-500 text-sm mt-2">Acesse o painel administrativo</p>
                </div>

                <x-filament-panels::form wire:submit="authenticate" class="mt-6">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()" />
                </x-filament-panels::form>


                <footer class="mt-6 text-center text-xs text-stone-400">
                    &copy; {{ date('Y') }} Recantu's Pizzaria • v1.0.0 <br>
                    <span class="text-[10px]">Desenvolvido por Rodrigo</span>
                </footer>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>