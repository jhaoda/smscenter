SMSCenter
=========

Класс для работы с сервисом smsc.ru (SMS-Центр)

Возможности:
* отправка одного/нескольких сообщений одним запросом
* все поддерживаемые типы сообщений: sms, mms, hlr, flash, wap, bin/hex, ping, call, email
* получение стоимости рассылки
* проверка статуса сообщений
* проверка баланса
* получение информации об операторе по номеру

**TODO**: отправка файлов в mms-, email- и call-сообщениях

Минимальные требования — **PHP 5.4**+

***

#### Инициализация
```php
$smsc = new \JhaoDa\SmsCenter\Api(
    'ivan',
    md5('ivanovich'),
    $sender  = 'SuperIvan'
    $secure  = false,
    $timeout = 5
);
```

`$sender` - имя отправителя по умолчанию
`$secure` - использовать https или нет
`$timeout` - таймаут на подключение к серверу и на выполнение запроса

#### Отправка одного сообщения
```php
$ret = $sms->send(
    new \JhaoDa\SmsCenter\Message\Sms(
        '7991111111', 'Привет!'
    )
);

// на несколько номеров
$ret = $sms->send(
    new \JhaoDa\SmsCenter\Message\Sms(
        ['79991111111', '7(999) 222-22-22'], 'Привет!'
    )
);
```

#### Отправка нескольких сообщений
```php
$ret = $sms->send(
    new \JhaoDa\SmsCenter\Message\Sms(
        ['7(999) 111-11-11', '89992222222'], 'Привет!'
    ),
    new \JhaoDa\SmsCenter\Message\Sms(
        '79994444444', 'И тебе привет!'
    ),
    new \JhaoDa\SmsCenter\Message\Email(
        'me@test.com', 'Текст письма', 'Me', 'Тема письма'
    ),
);
```

#### Получение стоимости рассылки
```php
$ret = $smsc->getCost(
    new \JhaoDa\SmsCenter\Message\Sms(
        ['7991111111', '79992222222'], 'Начало около 251 млн лет, конец — 201 млн лет назад.'
    )
);
```

#### Получение баланса
```php
echo $smsc->getBalance(); // '72.2 RUR'
echo $smsc->getBalance()->balance, ' руб.'; // '72.2 руб.'
```

#### Получение информации об абоненте
```php
$smsc->getPhoneInfo('7991111111');
```

#### Проверка доступности номера
```php
$smsc->ping('7991111111');
```

#### Получение информации об операторе
```php
$smsc->getOperatorInfo('7991111111');
```

#### Получения статуса сообщения
```php
echo $sms->getStatus('7991111111', 47379, \JhaoDa\SMSCenter\Api::STATUS_INFO_EXT)
```

#### Проверка тарифной зоны
```php
if ($sms->getChargingZone('79991111111') == \JhaoDa\SmsCenter\Api::ZONE_RU) {
    // ...
}
```

Вспомогательная функция для форматирования номера
```php
echo \JhaoDa\SMSCenter\Api::formatPhone('8991111111');  // +7991111111
echo \JhaoDa\SMSCenter\Api::formatPhone('+8991111111'); // +7991111111
```
***

Лицензия: Apache License, Version 2.0
