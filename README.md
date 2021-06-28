# Astaroth

Личный фреймворк для создания ботов

___

## Содержание

1. Конфигурация
    + [Установка](#Installation)
    + [Требования](#Requirement)
    + [Содержание env](#Env)
2. Примеры и документация
    + [Примеры](doc/example)
    + [Документация по аттрибутам](doc/attribute.md)
    + [Документация по фасадам](doc/facade.md)
    + [Документация по Utils](doc/utils.md)

___

### Installation

```
composer create-project labile/astaroth-framework bot
```

### Requirement
> Для работы необходим `сервер`, на бесплатных хостингах даже не запустится\
> Платформа - `Linux`\
> Версия `PHP` - `>=8`\
> Расширения: `ext-pcntl`, `ext-posix`, `mbstring`\
> Веб сервер (callback) - нативный php, поднимается командой `composer serve` (php -S x.x.x.x)\
> Можно попробовать завести apache или nginx, но я не проверял 


### Env

```dotenv
APP_NAMESPACE = App\Command
ACCESS_TOKEN=slkaojdwiwajdowadjwa
TYPE=CALLBACK
API_VERSION=5.130
CONFIRMATION_KEY=2f21ed85
SECRET_KEY=
HANDLE_REPEATED_REQUESTS=
LOGGING_ERROR=
```

### Auth:

    ACCESS_TOKEN - access_token сообщества или пользователя
    API_VERSION - версия vk api
    CONFIRMATION_KEY - строка, которую должен вернуть сервер для события confirmation
    SECRET_KEY - произвольная строка, которая будет передаваться с каждым запросом (необязательный параметр)

### Остальные параметры:
~~LOGGING_ERROR - логирование ошибок. При значении true все ошибки будут логироваться в папку error~~ (на данный момент отсутствует реализация)\
TYPE - тип работы бота. Возможны только два типа - CALLBACK, LONGPOLL
