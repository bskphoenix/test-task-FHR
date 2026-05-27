<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/filament/pages/task1-page.css') }}">
    @endpush

    @if (! $this->nativeReady)
        <div class="mb-4 rounded-lg border border-danger-300 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-600 dark:bg-danger-950 dark:text-danger-200">
            <strong>Сортировка недоступна.</strong> {{ $this->nativeSetupHint }}
        </div>
    @endif

    <div
        @if ($this->sortInProgress && ! $this->downloadsReady)
            wire:poll.5s="refreshConsoleSortLog"
        @endif
    >
        <x-filament::section heading="Запуск решения">
        <div class="task1-description">
            <p>
                Сортировка выполняется в фоне. Результаты сохраняются в CSV:
                <code>storage/app/bubble-sort-results/original.csv</code> и
                <code>sorted.csv</code>.
            </p>

            <p class="task1-description-subheading">Пример:</p>
            <pre class="task1-example">php artisan task1:sort --count=200000</pre>

            <div class="task1-params-actions">
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-play"
                    wire:click="runConsoleSort"
                    wire:loading.attr="disabled"
                    wire:target="runConsoleSort"
                    :disabled="! $this->nativeReady"
                >
                    <span wire:loading.remove wire:target="runConsoleSort">Запустить из интерфейса</span>
                    <span wire:loading wire:target="runConsoleSort">Запуск...</span>
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    icon="heroicon-o-arrow-path"
                    wire:click="refreshConsoleSortLog"
                    wire:target="refreshConsoleSortLog"
                >
                    <span wire:loading.remove wire:target="refreshConsoleSortLog">Обновить лог</span>
                    <span wire:loading wire:target="refreshConsoleSortLog">Чтение...</span>
                </x-filament::button>
            </div>

            <div class="task1-log-wrapper">
                <p class="task1-description-note">
                    Лог: <code>{{ $this->lastRunLogPath }}</code>
                </p>

                <pre class="task1-log-output">{{ $this->lastRunLogContent }}</pre>
            </div>

            @if ($this->downloadsReady)
                <div class="task1-params-actions">
                    <x-filament::button
                        tag="a"
                        href="{{ route('task1.download.original') }}"
                        target="_blank"
                        color="gray"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Скачать исходные данные (CSV)
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        href="{{ route('task1.download.sorted') }}"
                        target="_blank"
                        color="success"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Скачать отсортированный массив (CSV)
                    </x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>
    </div>
</x-filament-panels::page>
