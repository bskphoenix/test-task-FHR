@php
    $isRunning = ($progress['status'] ?? 'idle') === 'running';
    $isCancelled = ($progress['status'] ?? 'idle') === 'cancelled' || ($progress['cancelled'] ?? false);
    $progressPercent = (float) ($progress['progress_percent'] ?? 0);
@endphp

<div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                @if ($isRunning)
                    {{ $progress['stage_label'] ?? 'Выполнение...' }}
                @elseif ($isCancelled)
                    <span class="text-warning-600 dark:text-warning-400">
                        {{ $progress['stage_label'] ?? 'Остановлено' }}
                    </span>
                @else
                    {{ $progress['stage_label'] ?? 'Готово к запуску' }}
                @endif
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Время выполнения {{ number_format((float) ($progress['elapsed_seconds'] ?? 0), 1, ',', ' ') }} сек.
            </p>
        </div>

        <div class="text-right text-sm font-semibold text-primary-600 dark:text-primary-400">
            {{ number_format($progressPercent, 1, ',', ' ') }}%
        </div>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
        <div
            @class([
                'h-full rounded-full transition-all duration-300 ease-out',
                'bg-warning-500' => $isCancelled,
                'bg-primary-500' => ! $isCancelled,
            ])
            style="width: {{ min(100, max(0, $progressPercent)) }}%"
        ></div>
    </div>

    <dl class="task1-progress-metrics">
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <dt class="text-xs text-gray-500 dark:text-gray-400">Текущая память</dt>
            <dd class="mt-1 text-sm font-semibold">
                {{ number_format(($progress['memory_current_bytes'] ?? 0) / 1024 / 1024, 2, ',', ' ') }} МБ
            </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <dt class="text-xs text-gray-500 dark:text-gray-400">Пик памяти</dt>
            <dd class="mt-1 text-sm font-semibold">
                {{ number_format(($progress['memory_peak_bytes'] ?? 0) / 1024 / 1024, 2, ',', ' ') }} МБ
            </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <dt class="text-xs text-gray-500 dark:text-gray-400">Элементов</dt>
            <dd class="mt-1 text-sm font-semibold">
                {{ number_format($progress['element_count'] ?? 0, 0, ',', ' ') }}
            </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <dt class="text-xs text-gray-500 dark:text-gray-400">Проход сортировки</dt>
            <dd class="mt-1 text-sm font-semibold">
                @if (($progress['pass'] ?? 0) > 0)
                    {{ number_format($progress['pass'], 0, ',', ' ') }} / ~{{ number_format($progress['estimated_passes'] ?? 0, 0, ',', ' ') }}
                @else
                    —
                @endif
            </dd>
        </div>
    </dl>

    @if (! empty($progress['steps']))
        <div>
            <h4 class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Журнал действий
            </h4>
            <ul class="task1-action-log space-y-2 rounded-lg border border-gray-200 p-3 text-sm dark:border-white/10">
                @foreach (array_reverse($progress['steps']) as $step)
                    <li class="flex gap-3 text-gray-700 dark:text-gray-200">
                        <span class="shrink-0 font-mono text-xs text-gray-400">{{ $step['time'] }}</span>
                        <span>{{ $step['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
