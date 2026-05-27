<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/filament/pages/task3-page.css') }}">
    @endpush

    <x-filament::section heading="Условие задачи">
        <div class="task3-description">
            <p>
                Необходимо создать отдельную тестовую базу с более чем 500&nbsp;000 пользователей
                и по нажатию кнопки «Выгрузить пользователей» сформировать CSV-файл без перезагрузки страницы.
            </p>

            <div>
                <h3 class="task3-description-heading">Поля для скачивания:</h3>
                <ul class="task3-description-list">
                    <li>Фамилия</li>
                    <li>Имя</li>
                    <li>Телефон</li>
                    <li>E-mail</li>
                </ul>
            </div>

            <p class="task3-description-note">
                Выгрузка выполняется пошагово через AJAX-запросы Livewire. После завершения открывается вкладка скачивания
                и появляется ссылка на файл.
            </p>
            <p class="task3-description-note">
                Запустить выгрузку можно в подменю слева в разделе «Выгрузка».
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
