# Установка приложения

* Создайте свой .env файл на основе .env.example
* Выполните команды:
- `composer install` - установка необходимых зависимостей;
- `php artisan key:generate` - генерация ключа приложения;
- `./vendor/bin/sail up -d` - запуск докер контейнеров;
- `./vendor/bin/sail artisan migrate` - выполнение миграций;
- `./vendor/bin/sail artisan db:seed` - заполнение бд случайными данными.

# Запуск автотестов:

* `./vendor/bin/sail artisan test`.

# Описание роутов:

* `ПУТЬ_ВАШЕГО_ХОСТА_В_ENV/docs/api` - документация OpenApi (UI Scramble);
* `ПУТЬ_ВАШЕГО_ХОСТА_В_ENV/docs/api.json` - получить документацию OpenApi в виде json.

# Добавление фоновых задач и их запуск

* Убедиться, что в `.env` файле прописан `OMDB_API_KEY`;
* Отправить запрос на эндпойнт `api/films` c `imdb_id` от имени пользователя с ролью модератор;
* Запустить обработку фоновых задач `./vendor/bin/sail artisan queue:work`;
* `vendor/bin/sail artisan queue:retry all` - перезапуск проваленных задач;
* `vendor/bin/sail artisan queue:flush` - удаление проваленных задач. 