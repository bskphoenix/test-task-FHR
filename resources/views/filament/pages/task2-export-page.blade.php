<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Выгрузка базы данных
        </x-slot>

        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
            <p>
                Нажмите «Скачать выгрузку», чтобы получить SQL-файл со структурой всех таблиц и INSERT-запросами
                с текущими данными из базы.
            </p>
            <p>
                Файл можно использовать для резервного копирования или переноса данных в другую среду.
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
