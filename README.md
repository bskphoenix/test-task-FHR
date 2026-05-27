# Test Task FHR

Laravel-приложение с админ-панелью [Filament](https://filamentphp.com) и тремя тестовыми задачами:

| Задача | Описание |
|--------|----------|
| **Задача 1** | Пузырьковая сортировка массива из 200 000 чисел (нативный модуль на C) |
| **Задача 2** | CRUD составов игроков по сезонам и выгрузка SQL-дампа БД |
| **Задача 3** | Отдельная БД с 500 000+ пользователей и пошаговая выгрузка в CSV |

## Требования

- **PHP** ≥ 8.3 с расширениями: `pdo`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`
- **Composer** 2.x
- **Clang** — для сборки нативной сортировки (Задача 1)
- Опционально **MySQL/MariaDB** — если нужны отдельные MySQL-базы вместо SQLite

Проверка PHP:

```bash
php -v
php -m | grep -E 'pdo|sqlite|mbstring'
clang --version
```

## Быстрая установка

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

## Пошаговая установка

### 1. Зависимости

```bash
composer install
```

Либо через встроенный скрипт (создаёт `.env`, генерирует ключ, выполняет миграции):

```bash
composer run setup
```

> Скрипт `setup` не создаёт файл SQLite и не запускает сидеры — при необходимости выполните шаги ниже вручную.

### 2. Окружение

```bash
cp .env.example .env
php artisan key:generate
```

Основные параметры в `.env`:

```env
APP_URL=http://localhost:8000
APP_LOCALE=ru

DB_CONNECTION=sqlite

TASK3_DB_DRIVER=sqlite
# TASK3_DB_DATABASE=   # по умолчанию database/task3_users.sqlite
```

### 3. Основная база данных

По умолчанию используется SQLite (`database/database.sqlite`):

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

Сидер создаёт:

- пользователя для входа в админку;
- демо-данные для Задачи 2 (сезоны, клубы, игроки, составы).

### 4. Нативная сортировка (Задача 1)

Соберите исполняемый файл из `native/bubble_sort.c`:

```bash
php artisan task1:build-native
```

Проверка:

```bash
ls -l native/bubble_sort
php artisan task1:sort --count=100
```

Без сборки страница «Сортировка» в админке покажет подсказку с этой командой.

### 5. База данных Задачи 3

Отдельное подключение `task3_users`. Для SQLite:

```bash
touch database/task3_users.sqlite
php artisan task3:setup
```

Команда выполняет миграции из `database/migrations/task3` и заполняет таблицу минимум **500 001** тестовым пользователем.

Полезные опции:

```bash
# пересоздать таблицы и заново заполнить
php artisan task3:setup --fresh

# указать количество пользователей (не меньше 500 001)
php artisan task3:setup --count=600000
```

## Запуск

### Локальная разработка

```bash
php artisan serve
```

Приложение будет доступно по адресу [http://127.0.0.1:8000](http://127.0.0.1:8000). Корневой URL перенаправляет на `/admin`.

Для одновременного запуска сервера, очереди и логов:

```bash
composer run dev
```

> Для работы приложения в фоне (сортировка из админки, сессии, кэш) каталог `storage/` должен быть доступен на запись.

### Вход в админку

| Поле | Значение |
|------|----------|
| URL | `/admin` |
| Email | `admin@admin.com` |
| Пароль | `password` |

## Полезные команды

```bash
# Задача 1 — сборка и запуск сортировки из консоли
php artisan task1:build-native
php artisan task1:sort
php artisan task1:sort --count=200000

# Задача 3 — подготовка отдельной БД
php artisan task3:setup
php artisan task3:setup --fresh

# Миграции и сидеры основной БД
php artisan migrate
php artisan db:seed
```

Результаты сортировки сохраняются в `storage/app/bubble-sort-results/`.  
CSV-выгрузки Задачи 3 — в `storage/app/task3-exports/`.

## MySQL (опционально)

### Основная БД

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed
```

### БД Задачи 3

```env
TASK3_DB_DRIVER=mysql
TASK3_DB_HOST=127.0.0.1
TASK3_DB_PORT=3306
TASK3_DB_DATABASE=task3_users
TASK3_DB_USERNAME=root
TASK3_DB_PASSWORD=
```

Создайте базу `task3_users` в MySQL, затем:

```bash
php artisan task3:setup --fresh
```

## Структура проекта

```
app/
├── Console/Commands/     # task1:build-native, task1:sort, task3:setup
├── Filament/             # страницы и ресурсы админки
├── Http/Controllers/     # скачивание результатов сортировки и CSV
├── Models/               # модели основной БД и task3
└── Services/             # сортировка, выгрузка CSV, SQL-дамп

database/
├── migrations/           # основная БД
├── migrations/task3/     # отдельная БД Задачи 3
└── seeders/

native/
└── bubble_sort.c         # исходник нативной сортировки (→ bubble_sort)
```

## Устранение неполадок

**«Исполняемый файл native/bubble_sort не найден»**  
→ `php artisan task1:build-native` (нужен установленный `clang`).

**«В тестовой базе нет пользователей» (Задача 3)**  
→ `php artisan task3:setup`.

**Ошибки миграций SQLite**  
→ убедитесь, что файлы `database/database.sqlite` и `database/task3_users.sqlite` существуют и доступны на запись.

**Сортировка из админки не стартует**  
→ проверьте права на `storage/` и наличие `nohup` в системе (используется для фонового запуска `task1:sort`).
