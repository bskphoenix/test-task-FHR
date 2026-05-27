<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/filament/pages/task1-page.css') }}">
    @endpush

    <x-filament::section>
        <div class="task1-description">
            <div>
                <h3 class="task1-description-heading">Условие задачи</h3>
                <p>
                    Написать пузырьковую фильтрацию для массива числовых данных от 200 тысяч элементов.
                </p>

                <p class="task1-description-subheading">Пример:</p>
                <pre class="task1-example">[15, 23, 1, -234, 400, …, 92]</pre>

                <p class="task1-description-note">
                    <strong>Примечание:</strong>
                    желательно получить результат максимально быстро, затрачивая минимум памяти при математических расчётах.
                </p>
            </div>

            <div>
                <h3 class="task1-description-heading">Итоговый результат</h3>
                <p>
                    Получить отсортированный массив данных от меньшего к большему. В устной форме обосновать своё решение.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
