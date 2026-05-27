# Test Task FHR

Laravel-приложение с админ-панелью [Filament](https://filamentphp.com) и тремя задачами:

| Задача | Описание |
|--------|----------|
| **1** | Пузырьковая сортировка 200 000 чисел |
| **2** | CRUD составов игроков по сезонам и SQL-дамп БД |
| **3** | Отдельная БД с 500 000+ пользователей и выгрузка в CSV |

## Требования

PHP ≥ 8.3, Composer 2.x, Clang (для Задачи 1). По умолчанию — SQLite; MySQL опционален.

## Установка

```bash
git clone <repository-url> test-task-FHR
cd test-task-FHR

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

php artisan task1:build-native
php artisan task3:setup
```

## Запуск

```bash
php artisan serve
```

Приложение: [http://127.0.0.1:8000](http://127.0.0.1:8000) → редирект на `/admin`.

**Вход в админку:** `admin@admin.com` / `password`

## Команды

```bash
# Задача 1
php artisan task1:build-native
php artisan task1:sort --count=200000

# Задача 3
php artisan task3:setup
php artisan task3:setup --fresh --count=600000
```

Результаты сортировки: `storage/app/bubble-sort-results/`  
CSV-выгрузки: `storage/app/task3-exports/`

## MySQL (опционально)

В `.env` укажите `DB_CONNECTION=mysql` и параметры подключения. Для Задачи 3 — `TASK3_DB_DRIVER=mysql` и отдельную базу `task3_users`, затем `php artisan task3:setup --fresh`.

## Если что-то не работает

- **native/bubble_sort не найден** → `php artisan task1:build-native`
- **Нет пользователей в Задаче 3** → `php artisan task3:setup`
- **Ошибки SQLite** → создайте `database/database.sqlite` и `database/task3_users.sqlite`
- **Сортировка из админки не стартует** → проверьте права на `storage/`
