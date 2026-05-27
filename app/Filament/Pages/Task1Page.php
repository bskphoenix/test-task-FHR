<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Sorting\BubbleSortCancellation;
use App\Services\Sorting\BubbleSortProgress;
use App\Services\Sorting\BubbleSortRunner;
use App\Services\Sorting\BubbleSortSessionStore;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\On;

class Task1Page extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBarsArrowDown;

    protected static ?string $navigationLabel = 'Задача 1';

    protected static ?string $title = 'Задача 1: Пузырьковая сортировка';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'task-1';

    protected string $view = 'filament.pages.task1-page';

    /** @var array{elementCount: int} */
    public array $data = [];

    public bool $isRunning = false;

    public ?string $runId = null;

    /** @var array<string, mixed>|null */
    public ?array $progress = null;

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public function mount(): void
    {
        $this->data = [
            'elementCount' => BubbleSortRunner::DEFAULT_ELEMENT_COUNT,
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Пузырьковая сортировка массива числовых данных (~200 000 элементов) с минимальным потреблением памяти.';
    }

    /** Запускает пошаговую сортировку */
    public function runBubbleSort(
        BubbleSortRunner $bubbleSortRunner,
        BubbleSortCancellation $bubbleSortCancellation,
        BubbleSortSessionStore $sessionStore,
    ): void {
        set_time_limit(0);

        $count = max(2, min(BubbleSortRunner::DEFAULT_ELEMENT_COUNT, (int) ($this->data['elementCount'] ?? BubbleSortRunner::DEFAULT_ELEMENT_COUNT)));
        $ownerKey = $this->resolveOwnerKey();

        if ($this->runId !== null) {
            $bubbleSortCancellation->clear($this->runId);
            $sessionStore->forget($this->runId);
        }

        $this->runId = $bubbleSortCancellation->startRun();
        $bubbleSortCancellation->registerActiveRun($this->runId, $ownerKey);

        $this->isRunning = true;
        $this->result = null;
        $this->progress = [
            'status' => BubbleSortProgress::STATUS_RUNNING,
            'stage' => BubbleSortProgress::STAGE_GENERATING,
            'stage_label' => 'Подготовка к запуску...',
            'progress_percent' => 0,
            'elapsed_seconds' => 0,
            'memory_current_bytes' => memory_get_usage(true),
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'element_count' => $count,
            'pass' => 0,
            'estimated_passes' => 0,
            'steps' => [],
            'cancelled' => false,
        ];

        $bubbleSortRunner->startSteppedRun(
            runId: $this->runId,
            sessionStore: $sessionStore,
            count: $count,
            ownerKey: $ownerKey,
            onProgress: fn (BubbleSortProgress $progress): mixed => $this->handleProgress($progress),
        );

        $this->dispatch('bubble-sort-step');
    }

    /** Выполняет один шаг сортировки */
    #[On('bubble-sort-step')]
    public function processBubbleSortStep(
        BubbleSortRunner $bubbleSortRunner,
        BubbleSortCancellation $bubbleSortCancellation,
        BubbleSortSessionStore $sessionStore,
    ): void {
        if (! $this->isRunning || $this->runId === null) {
            return;
        }

        set_time_limit(0);

        $runId = $this->runId;
        $shouldCancel = fn (): bool => $bubbleSortCancellation->shouldStop($runId);

        if ($shouldCancel()) {
            $this->finishBubbleSort(
                $bubbleSortRunner->processStep(
                    runId: $runId,
                    sessionStore: $sessionStore,
                    onProgress: fn (BubbleSortProgress $progress): mixed => $this->handleProgress($progress),
                    shouldCancel: fn (): bool => true,
                ),
                $runId,
                $bubbleSortCancellation,
            );

            return;
        }

        $outcome = $bubbleSortRunner->processStep(
            runId: $runId,
            sessionStore: $sessionStore,
            onProgress: fn (BubbleSortProgress $progress): mixed => $this->handleProgress($progress),
            shouldCancel: $shouldCancel,
        );

        if ($outcome['continue']) {
            if ($shouldCancel()) {
                $this->finishBubbleSort(
                    $bubbleSortRunner->processStep(
                        runId: $runId,
                        sessionStore: $sessionStore,
                        onProgress: fn (BubbleSortProgress $progress): mixed => $this->handleProgress($progress),
                        shouldCancel: fn (): bool => true,
                    ),
                    $runId,
                    $bubbleSortCancellation,
                );

                return;
            }

            $this->dispatch('bubble-sort-step');

            return;
        }

        $this->finishBubbleSort($outcome, $runId, $bubbleSortCancellation);
    }

    /** Запрашивает остановку активной сортировки */
    public function stopBubbleSort(BubbleSortCancellation $bubbleSortCancellation): void
    {
        $ownerKey = $this->resolveOwnerKey();
        $runId = $this->runId ?? $bubbleSortCancellation->getActiveRunId($ownerKey);

        if ($runId === null) {
            Notification::make()
                ->title('Нет активной сортировки')
                ->body('Сначала запустите сортировку.')
                ->warning()
                ->send();

            return;
        }

        $bubbleSortCancellation->requestStop($runId);

        Notification::make()
            ->title('Запрошена остановка')
            ->body('Сортировка будет прервана в ближайшие секунды. Появится промежуточный результат.')
            ->info()
            ->send();
    }

    /** @param array{continue: bool, result: array<string, mixed>|null} $outcome */
    private function finishBubbleSort(
        array $outcome,
        string $runId,
        BubbleSortCancellation $bubbleSortCancellation,
    ): void {
        $bubbleSortCancellation->clear($runId);
        $bubbleSortCancellation->clearActiveRun($this->resolveOwnerKey());

        $this->isRunning = false;
        $this->runId = null;
        $this->result = $outcome['result'];

        if ($this->result === null) {
            Notification::make()
                ->title('Сортировка прервана')
                ->body('Сессия сортировки не найдена.')
                ->warning()
                ->send();

            return;
        }

        if ($this->result['cancelled']) {
            Notification::make()
                ->title('Сортировка остановлена')
                ->body(sprintf(
                    'Выполнено %s проходов за %s сек. Ниже показан промежуточный результат.',
                    number_format($this->result['completed_passes'], 0, ',', ' '),
                    number_format($this->result['duration_seconds'], 3, ',', ' '),
                ))
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Сортировка завершена')
            ->body(sprintf(
                'Отсортировано %s элементов за %s сек.',
                number_format($this->result['count'], 0, ',', ' '),
                number_format($this->result['duration_seconds'], 3, ',', ' '),
            ))
            ->success()
            ->send();
    }

    private function handleProgress(BubbleSortProgress $progress): void
    {
        $this->progress = $progress->toArray();
    }

    private function resolveOwnerKey(): string
    {
        $userId = auth()->id();

        return $userId !== null ? 'user:' . $userId : 'session:' . session()->getId();
    }
}
