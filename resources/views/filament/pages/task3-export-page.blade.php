<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/filament/pages/task3-page.css') }}">
    @endpush

    <div
        x-data
        x-on:task3-export-complete.window="window.open($event.detail.downloadUrl, '_blank')"
    >
        <div class="task3-panels">
            <div class="task3-panels-progress">
                <x-filament::section heading="Ход выгрузки">
                    @if ($this->progress)
                        @include('filament.pages.partials.task3-progress', ['progress' => $this->progress])
                    @else
                        <div class="task3-description">
                            <p>
                                Нажмите «Выгрузить пользователей», чтобы начать пошаговое формирование CSV-файла.
                                Страница не перезагружается — прогресс обновляется через AJAX.
                            </p>
                        </div>
                    @endif

                    <div wire:loading wire:target="startExport,processExportStep" class="mt-4">
                        <p class="text-sm text-primary-600 dark:text-primary-400">
                            Обработка шага выгрузки...
                        </p>
                    </div>
                </x-filament::section>
            </div>

            <div class="task3-panels-actions">
                <x-filament::section heading="Действия">
                    <div class="task3-actions">
                        <div class="task3-stat">
                            <span class="task3-stat__label">Пользователей в тестовой БД</span>
                            <span class="task3-stat__value">{{ number_format($this->totalUsers, 0, ',', ' ') }}</span>
                        </div>

                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-arrow-down-tray"
                            wire:click="startExport"
                            wire:loading.attr="disabled"
                            wire:target="startExport,processExportStep"
                            :disabled="$this->isExporting || $this->totalUsers === 0"
                        >
                            <span wire:loading.remove wire:target="startExport,processExportStep">Выгрузить пользователей</span>
                            <span wire:loading wire:target="startExport,processExportStep">Выгрузка выполняется...</span>
                        </x-filament::button>

                        @if ($this->downloadUrl)
                            <x-filament::button
                                tag="a"
                                href="{{ $this->downloadUrl }}"
                                target="_blank"
                                color="success"
                                icon="heroicon-o-document-arrow-down"
                            >
                                Скачать CSV
                            </x-filament::button>
                        @endif

                        @if ($this->totalUsers === 0)
                            <p class="task3-description-note">
                                Для заполнения тестовой базы выполните:
                                <code>php artisan task3:setup</code>
                            </p>
                        @endif
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
