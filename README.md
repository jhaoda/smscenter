<h1 align="center">SDK для <a href="https://smsc.ru">SMS-Центр</a></h1>

<p align="center">
    <a href="https://packagist.org/packages/jhaoda/smscenter"><img src="https://img.shields.io/packagist/v/jhaoda/smscenter.svg?style=flat" alt="Latest Version on Packagist" /></a>
    <a href="https://github.com/jhaoda/smscenter/actions?workflow=tests"><img src="https://github.com/jhaoda/smscenter/workflows/tests/badge.svg" alt="Testing" /></a>
    <a href="https://scrutinizer-ci.com/g/jhaoda/smscenter"><img src="https://img.shields.io/scrutinizer/g/jhaoda/smscenter.svg?style=flat" alt="Quality Score" /></a>
    <a href="https://scrutinizer-ci.com/g/jhaoda/smscenter/?branch=develop"><img src="https://img.shields.io/scrutinizer/coverage/g/jhaoda/smscenter/develop.svg?style=flat" alt="Code Coverage" /></a>
    <a href="https://packagist.org/packages/jhaoda/smscenter"><img src="https://poser.pugx.org/jhaoda/smscenter/downloads?format=flat" alt="Total Downloads"></a>
    <a href="https://raw.githubusercontent.com/jhaoda/smscenter/develop/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-428F7E?format=flat" alt="License MIT"></a>
</p>

## Содержание
- [Установка](#установка)
- [Отправка сообщений](#отправка-сообщений)

## Установка

> Минимальные требования — PHP 7.1+,  ext-json, ext-mbstring.

Для установки используйте менеджер пакетов [Composer](https://getcomposer.org/):
```bash
composer require jhaoda/smscenter
```

## Логирование

Для логирования запросов и ответов можно подключить любой логгер, реализующий стандарт [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md), например, [Monolog](https://github.com/Seldaek/monolog):
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = (new Logger('smsc.ru'))
    ->pushHandler(new StreamHandler('path/to/your.log', Logger::INFO));

$client->setLogger($log);
```

## Отправка сообщений

#### Инициализация
```php
use GuzzleHttp\Client as GuzzleClient;

$smsc = new \JhaoDa\SmsCenter\Client(
    'login', 'password', new GuzzleClient()
);
```

## Запуск тестов

```
$ vendor/bin/phpunit
```

## Авторы

- [JhaoDa](https://github.com/jhaoda)

## Лиценция

Данный SDK распространяется под лицензией [MIT](http://opensource.org/licenses/MIT).
