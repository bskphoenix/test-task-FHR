<x-filament-panels::page>
    <style>
        .task1-metrics-grid,
        .task1-progress-metrics {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
        }

        .task1-metrics-grid {
            gap: 1rem;
        }

        .task1-metrics-grid > div {
            flex: 1 1 140px;
            min-width: 0;
        }

        .task1-progress-metrics {
            gap: 0.75rem;
        }

        .task1-progress-metrics > div {
            flex: 1 1 140px;
            min-width: 0;
        }

        .task1-panels {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .task1-panels-progress {
            flex: 2 1 360px;
            min-width: 0;
        }

        .task1-panels-params {
            flex: 1 1 240px;
            min-width: 0;
            max-width: 22rem;
        }

        .task1-action-log {
            max-height: 12rem;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .task1-array-full {
            max-height: 24rem;
            overflow: auto;
            overscroll-behavior: contain;
            margin: 0;
            padding: 1rem;
            border-radius: 0.5rem;
            background: rgb(3 7 18 / 0.05);
            font-size: 0.875rem;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .dark .task1-array-full {
            background: rgb(255 255 255 / 0.05);
        }

        .task1-params-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .task1-params-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .task1-params-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .task1-results {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .task1-justification {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            font-size: 0.875rem;
            line-height: 1.6;
            color: rgb(3 7 18);
        }

        .dark .task1-justification {
            color: rgb(255 255 255);
        }

        .task1-intro-heading {
            margin: 0 0 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(3 7 18);
        }

        .dark .task1-intro-heading {
            color: rgb(255 255 255);
        }

        .task1-intro-action {
            margin: 0;
        }
    </style>

    <div class="task1-panels">
        <div class="task1-panels-progress">
            <x-filament::section heading="Ход выполнения">
                @if ($this->progress)
                    @include('filament.pages.partials.task1-progress', ['progress' => $this->progress])
                @else
                    <div class="task1-justification">
                        <h3 class="task1-intro-heading">Алгоритм</h3>
                        <p>
                            Сначала программа создаёт массив из случайных чисел заданного размера.
                            Исходный вариант сохраняется, чтобы потом можно было сравнить его с результатом.
                        </p>
                        <p>
                            Сортировка выполняется методом «пузырька»: массив просматривается слева направо,
                            соседние числа сравниваются между собой. Если левое больше правого — они меняются местами.
                            Такой проход повторяется снова и снова, пока все элементы не встанут по возрастанию.
                        </p>
                        <p>
                            Числа переставляются прямо в том же массиве — отдельная копия для сортировки не создаётся.
                            Во время работы здесь отображаются этап, время, память и журнал действий.
                            Кнопка «Остановить» прерывает процесс и показывает массив в текущем состоянии.
                        </p>
                        <p class="task1-intro-action">
                            Нажмите «Сгенерировать и отсортировать», чтобы запустить процесс.
                        </p>
                    </div>
                @endif

                <div wire:loading wire:target="runBubbleSort,processBubbleSortStep,stopBubbleSort" class="mt-4">
                    <p class="text-sm text-primary-600 dark:text-primary-400">
                        Обработка запроса...
                    </p>
                </div>
            </x-filament::section>
        </div>

        <div class="task1-panels-params">
            <x-filament::section heading="Параметры">
                <div class="task1-params-form">
                    <div class="task1-params-field">
                        <label for="task1-element-count" class="text-sm font-medium text-gray-950 dark:text-white">
                            Количество элементов
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="task1-element-count"
                                type="number"
                                min="2"
                                max="{{ \App\Services\Sorting\BubbleSortRunner::DEFAULT_ELEMENT_COUNT }}"
                                wire:model.live="data.elementCount"
                                :disabled="$this->isRunning"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="task1-params-actions">
                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-play"
                            wire:click="runBubbleSort"
                            wire:loading.attr="disabled"
                            wire:target="runBubbleSort,processBubbleSortStep"
                            :disabled="$this->isRunning"
                        >
                            <span wire:loading.remove wire:target="runBubbleSort,processBubbleSortStep">Сгенерировать и отсортировать</span>
                            <span wire:loading wire:target="runBubbleSort,processBubbleSortStep">Сортировка выполняется...</span>
                        </x-filament::button>

                        <x-filament::button
                            color="danger"
                            icon="heroicon-o-stop"
                            wire:loading.attr="disabled"
                            wire:target="stopBubbleSort"
                            :disabled="! $this->isRunning"
                            x-on:click.stop="
                                fetch(@js(route('task1.cancel')), {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': @js(csrf_token()),
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        run_id: $wire.runId ?? '',
                                    }),
                                }).then((response) => {
                                    if (response.ok) {
                                        $wire.stopBubbleSort();
                                    }
                                });
                            "
                        >
                            Остановить
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>

    @if ($this->result)
        <div class="task1-results">
            @if (! $this->result['cancelled'])
                <x-filament::section heading="Результат">
                    <dl class="task1-metrics-grid">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Элементов</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ number_format($this->result['count'], 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Время выполнения</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ number_format($this->result['duration_seconds'], 3, ',', ' ') }} сек.</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Проходов выполнено</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ number_format($this->result['completed_passes'], 0, ',', ' ') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Память в конце</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ number_format($this->result['memory_current_bytes'] / 1024 / 1024, 2, ',', ' ') }} МБ</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Пик памяти</dt>
                            <dd class="mt-1 text-lg font-semibold">{{ number_format($this->result['memory_peak_bytes'] / 1024 / 1024, 2, ',', ' ') }} МБ</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Корректность</dt>
                            <dd class="mt-1 text-lg font-semibold">
                                @if ($this->result['is_sorted'])
                                    <span class="text-success-600 dark:text-success-400">Массив отсортирован</span>
                                @else
                                    <span class="text-danger-600 dark:text-danger-400">Ошибка сортировки</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </x-filament::section>
            @endif

            @if ($this->result['original_elements'] !== [])
                <x-filament::section heading="Исходный массив">
                    <pre class="task1-array-full">{{ json_encode($this->result['original_elements'], JSON_UNESCAPED_UNICODE) }}</pre>
                </x-filament::section>
            @endif

            @if ($this->result['elements'] !== [])
                <x-filament::section :heading="$this->result['cancelled'] ? 'Промежуточное состояние массива' : 'Отсортированный массив'">
                    <pre class="task1-array-full">{{ json_encode($this->result['elements'], JSON_UNESCAPED_UNICODE) }}</pre>
                </x-filament::section>
            @endif
        </div>
    @endif
</x-filament-panels::page>
