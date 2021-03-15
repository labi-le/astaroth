# PHP-VK-BOT

Личная библиотека для создания ботов и всякой другой херни

___

## Содержание

1. [Установка](#1-установка)
    + [Настройка конфига](#11-настройка-конфгиа)
2. [Обработка команд](#2-обработка-команд)
    + [Варианты реагирования бота на сообщения](#211-реакция-не-предложения-или-другие-слова)
    + [Варианты реагирования бота на нажатие по кнопке](#212-реакция-на-нажатие-по-кнопке)

    - [Похоже на](#2211-похоже-на)
    - [Начинается с](#2212-начинается-с)
    - [Заканчивается на](#2213-заканчивается-на)
    - [Содержит](#2214-содержит)
    - [Дополнение](#2215-дополнение)

    + [Исполнение нескольких методов](#23-исполнение-нескольких-методов)
3. [Методы](#4-методы)

___

## 1. Установка

> composer require labile/vk-bot-constructor

### 1.1 Настройка конфига

```json
{
  "auth": {
    "token": "",
    "v": "5.130",
    "confirmation": "",
    "secret": false
  },
  "logging_error": false,
  "type": "callback"
}
```

##### Auth:

    token - access_token сообщества или пользователя
    v - версия используемого api vk
    confirmation - строка, которую должен вернуть сервер для события confirmation
    secret - произвольная строка, которая будет передаваться с каждым запросом (необязательный параметр)

##### Остальные параметры:

    logging_error - логирование ошибок. При значении true все ошибки будут логироваться в папку error
    type - тип работы бота. Возможны только два типа - callback, longpoll

## 2. Обработка команд

#### 2.1.1. Реакция не предложения или другие слова

Если хотите сделать реакцию на предложения, то отредактируйте метод **text()** в CommandList

```php
protected function text()  
{  
        return [

            [
                'text' => ['pr', 'print'],
                'method' => ['print']
            ],

            [
                'text' => ['блин', 'капец'],
                'method' => ['blin']
            ],

        ];
}
 ```

#### 2.1.2. Реакция на нажатие по кнопке

Реакция создаётся аналогичной выше, только метод **payload()**

```php
protected function payload()  
{  
        return [
            
            'chat' =>
                [
                    [
                        'payload' => 'registration',
                        'method' => ['chatRegistration'],
                        'type' => 'default'
                    ],

                ],

        ];
}
 ```

```php
Payload соответствующий команде выше:
['chat' => 'registration]

type - тип кнопки, callback или default
```

### 2.2. Вариативность вызова команды

Если указать ключ **text** как массив, то бот будет реагировать на несколько фраз

```php
'text'=>['text message 1', 'text message 2']
```

### 2.2.1. Варианты реагирования бота на сообщения

Вы можете вызывать команду разными вариантами:

- Похоже на
- Начинается с
- Заканчивается на
- Содержит

##### 2.2.1.1. Похоже на

Чтобы использовать этот вариант, добавьте **|** в начале строки

Вы можете настроить вероятность совпадения в диапазоне [0-100]

Установить эту настройку можно в **Launcher.php** отредактировав константу: **SIMILAR_PERCENT**

По умолчанию: 80%.

```php
'text'=>'|привет всем',
```

##### 2.2.1.2. Начинается с

Чтобы использовать этот вариант, добавьте **[|** в начале строки

```php
'text'=>'[|привет всем',
```

##### 2.2.1.3. Заканчивается на

Чтобы использовать этот вариант, добавьте **|]** в конец строки

```php
'text'=>'привет всем|]',
```

##### 2.2.1.4. Содержит

Чтобы использовать этот вариант, выделите фразу в фигурных скобках **{фраза}**

```php
'text' => '{привет всем}',
```

##### 2.2.1.5. Дополнение

Данный способ работает и с использованием множества вариантов вызова.

```php
'text' => ['[|привет', '{ку}', 'хай|]', '|здравствуйте']
```

### 2.3. Исполнение нескольких методов

Указание ключа в **method** как массив, подразумевает выполнение нескольких методов.
\
Если метод возвращает `булево` значение - `false`, то выполнение стека методов заканчивается

```php
'method' => ['hello', 'goodbye']
```

## 4. Методы
**Методы из [SimpleVK](https://simplevk.scripthub.ru/v3/classes/simplevk.html)**

**Методы-проверки:**
```php
    /**
     * Это личное сообщение?
     * return bool
     */
    public function isPrivateMessage(): bool

    /**
     * Это чат?
     * return bool
     */
    public function isChat(): bool
    
    /**
     * Чувак нажавший на каллбек кнопку - админ?
     * return bool
     */
    public function eventNoAccess(): bool

    /**
     * Это админ?
     */
    public function isAdmin(): bool

    /**
     * Это руководитель группы?
     * @return bool
     */
    public function isManagerGroup(): bool
```
\
**Методы ***статического класа*** `Utils`:**
```php
    /**
    * Транслитерация строк
    * @param string
    * @return string
    */
    public static function translit(string $str): string
    
    /**
     * Удаляет из строки самую первую подстроку
     * @param $text
     * @return string|bool
     */
    public static function removeFirstWord($text): string|bool

    /**
     * Выборка необходимой строки по ключу
     * @param string $string
     * @param int $substring
     * @return string|bool
     */
    public static function getWord(string $string, int $substring): string|bool
    
    /**
     * explode с возможностью использовать несколько символов
     * @param $delimiters
     * @param $string
     * @return array|bool
     */
    public static function multiExplode($delimiters, $string): array|bool

    /**
     * Является ли массив ассоциативным
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr): bool

    /**
     * Является ли массив последовательным
     * @param array $arr
     * @return bool
     */
    public static function isSeq(array $arr): bool

    /**
     * Является ли массив многомерным
     * @param array $array
     * @return bool
     */
    public static function isMulti(array $array): bool

    /**
     * Регулярка, выбирает все айдишники из текста
     * @param string $string
     * @return array|bool
     */
    public static function regexId(string $string): array|bool

    /**
     * Простой дебаг в stdout
     * @param $data
     */
    public static function var_dumpToStdout($data)

    /**
     * Булев в смайлы
     * @param $bool
     * @return string
     */
    public static function boolToSmile($bool): string

    /**
     * Строка в unixtime
     * 1 час
     * unixtime + 3600
     * @param string $string
     * @return int|false
     */
    public static function strTime(string $string): int|false
```

