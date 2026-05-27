<x-filament-panels::page>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/filament/pages/task2-page.css') }}">
    @endpush

    <x-filament::section heading="Условие задачи">
        <div class="task2-description">
            <p>
                Необходимо разработать таблицы базы данных для хранения игроков по сезонам.
            </p>

            <div>
                <h3 class="task2-description-heading">Клуб игрока должен содержать информацию:</h3>
                <ul class="task2-description-list">
                    <li>Название клуба на русском</li>
                    <li>Название клуба на английском</li>
                    <li>Город клуба на русском</li>
                    <li>Город клуба на английском</li>
                </ul>
            </div>

            <div>
                <h3 class="task2-description-heading">Информация об игроке:</h3>
                <ul class="task2-description-list">
                    <li>ФИО на русском</li>
                    <li>ФИО на английском</li>
                    <li>Вес</li>
                    <li>Рост</li>
                    <li>Игровой номер игрока</li>
                </ul>
            </div>

            <p class="task2-description-note">
                Реализация свободная.
            </p>
        </div>
    </x-filament::section>

    <x-filament::section heading="Структура таблиц БД" class="task2-section-gap">
        <div class="task2-schema-diagram">
            <p class="task2-description-note">
                Связи между основными сущностями. Переводы вынесены в отдельные таблицы; номер игрока и клуб в сезоне хранятся в составе.
            </p>

            <div class="task2-schema-flow">
                <span class="task2-schema-node">locales</span>
            </div>

            <div class="task2-schema-flow">
                <span class="task2-schema-node">seasons</span>
                <span class="task2-schema-arrow">→</span>
                <span class="task2-schema-node task2-schema-node--center">player_season_memberships</span>
                <span class="task2-schema-arrow">←</span>
                <span class="task2-schema-node">clubs</span>
                <span class="task2-schema-arrow">←</span>
                <span class="task2-schema-node">players</span>
            </div>

            <div class="task2-schema-flow">
                <span class="task2-schema-node">clubs</span>
                <span class="task2-schema-arrow">→</span>
                <span class="task2-schema-node">club_translations</span>
                <span class="task2-schema-node" style="margin-left: 1rem;">players</span>
                <span class="task2-schema-arrow">→</span>
                <span class="task2-schema-node">player_translations</span>
            </div>

            <div class="task2-schema-grid">
                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">locales</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row">code <span class="task2-schema-table__comment">ru, en</span></li>
                        <li class="task2-schema-table__row">name</li>
                        <li class="task2-schema-table__row">is_default, is_active</li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">seasons</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row">slug <span class="task2-schema-table__comment">2025-2026</span></li>
                        <li class="task2-schema-table__row">starts_at, ends_at</li>
                        <li class="task2-schema-table__row">is_active</li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">clubs</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">club_translations</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key task2-schema-table__key--fk">FK</span> club_id</li>
                        <li class="task2-schema-table__row">locale, name, city</li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">players</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row">weight_kg <span class="task2-schema-table__comment">вес</span></li>
                        <li class="task2-schema-table__row">height_cm <span class="task2-schema-table__comment">рост</span></li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">player_translations</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key task2-schema-table__key--fk">FK</span> player_id</li>
                        <li class="task2-schema-table__row">locale, full_name</li>
                    </ul>
                </div>

                <div class="task2-schema-table">
                    <div class="task2-schema-table__header">player_season_memberships</div>
                    <ul class="task2-schema-table__body">
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key">PK</span> id</li>
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key task2-schema-table__key--fk">FK</span> season_id</li>
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key task2-schema-table__key--fk">FK</span> player_id</li>
                        <li class="task2-schema-table__row"><span class="task2-schema-table__key task2-schema-table__key--fk">FK</span> club_id</li>
                        <li class="task2-schema-table__row">jersey_number <span class="task2-schema-table__comment">№</span></li>
                        <li class="task2-schema-table__row">joined_at, left_at <span class="task2-schema-table__comment">трансфер</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section heading="Итоговый результат" class="task2-section-gap">
        <div class="task2-description">
            <p>
                Получить выгрузку из БД (можно с минимальным набором данных: 3 клуба, 2 сезона, 5 человек).
                Выгрузка должна содержать структуру таблиц и набор данных (dump db).
            </p>
            <p class="task2-description-note">
                Управление данными — в подменю слева: клубы, игроки, сезоны, составы. Выгрузку можно скачать в разделе «Выгрузка».
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
