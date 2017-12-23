## Telegram bot sdk v.2 (long polling edition)

### Модифицированный форк: https://github.com/irazasyed/telegram-bot-sdk (Версия: 2.1)

### Документация: https://telegram-bot-sdk.readme.io/docs

#### Отличия:
* Удалена Webhooks платформа.
* Добавлена подержа Action для команд `/command:action`
* Добавлена подержа параметров для команд `/command param1 param2` (параметры доступны в виде нумерованного массива)
* Изменен вывод информации о командах `/help`
* Добавлена возможность обработки входящего сообщения (не команды), командой из прошлой сессии `/last_command`

## Установка

#### Шаг 1: Добавление проекта
```
composer require alexanderminin/telegram-bot-sdk-long-polling
```

#### Шаг 2: Добавление Service Provider
Откройте config/app.php и добавьте в массив providers:

```
 Telegram\Bot\Laravel\TelegramServiceProvider::class
```

#### Шаг 3: Добавление Facade
Откройте config/app.php и добавьте в массив aliases:

```
 'Telegram'  => Telegram\Bot\Laravel\Facades\Telegram::class
```

#### Шаг 4: Добавление конфигурационного файла
Выполните в терминале:

```
 php artisan vendor:publish --provider="Telegram\Bot\Laravel\TelegramServiceProvider"
```