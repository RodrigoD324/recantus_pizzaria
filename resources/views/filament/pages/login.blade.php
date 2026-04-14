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
                    <!-- <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Pizzaria Recantu's
                    </h1> -->
                    <img src="{{ asset('assets/imgs/recantus_logo.png') }}" alt="Logo" class="mx-auto h-[180px] w-auto">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Monitore e gerencie sua pizzaria de forma prática.
                    </p>
                </div>

                <x-filament-panels::form wire:submit="authenticate" class="mt-6">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>