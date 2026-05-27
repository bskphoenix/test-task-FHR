@php
    $isRunning = ($progress['status'] ?? 'idle') === 'running';
    $isCompleted = ($progress['status'] ?? 'idle') === 'completed';
    $isFailed = ($progress['status'] ?? 'idle') === 'failed';
    $progressPercent = (float) ($progress['progress_percent'] ?? 0);
@endphp

<div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                @if ($isRunning)
                    {{ $progress['stage_label'] ?? 'Выполнение...' }}
                @elseif ($isCompleted)
                    <span class="text-success-600 dark:text-success-400">
                        {{ $progress['stage_label'] ?? 'Готово' }}
                    </span>
                @elseif ($isFailed)
                    <span class="text-danger-600 dark:text-danger-400">
                        {{ $progress['stage_label'] ?? 'Ошибка' }}
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
                'bg-danger-500' => $isFailed,
                'bg-success-500' => $isCompleted,
                'bg-primary-500' => ! $isFailed && ! $isCompleted,
            ])
            style="width: {{ min(100, max(0, $progressPercent)) }}%"
        ></div>
    </div>

    <dl class="task3-progress-metrics">
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
            <dt class="text-xs text-gray-500 dark:text-gray-400">Выгружено</dt>
            <dd class="mt-1 text-sm font-semibold">
                {{ number_format($progress['processed_count'] ?? 0, 0, ',', ' ') }}
                /
                {{ number_format($progress['total_count'] ?? 0, 0, ',', ' ') }}
            </dd>
        </div>
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
    </dl>

    @if ($isFailed && ! empty($progress['error_message']))
        <p class="text-sm text-danger-600 dark:text-danger-400">
            {{ $progress['error_message'] }}
        </p>
    @endif
</div>
