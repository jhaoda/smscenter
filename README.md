SMSCenter
=========

Класс для работы с сервисом smsc.ru (SMS-Центр)

Функции:
* отправка сообщений
* проверка статуса сообещний
* получение стоимости рассылки
* проверка баланса
* получение информации об операторе

Функциональность параметра **list** пока не реализована.

Настройки передаются при создании экзепляра класса в виде массива.
Если формат ответа сервера = FMT_JSON, то ответ сервера принудительно конвертируется в utf-8.

Примеры использования:
```php
<?php
// Инициализация
$smsc = new SMSCenter(array(
	'login'	   => 'ivan',
	'password' => md5('ivanovich'),
	'charset' => SMSCenter::CHARSET_UTF8
));

// Отправка сообщения
$smsc->sendMessage('+7991111111', 'Превед, медведы!', 'SuperIvan');

// Отправка сообщения на 2 номера
$smsc->send(array('+7(999)1111111', '+7(999)222-22-22'), 'Превед, медведы! Одно сообщение на 2 номера.', 'SuperIvan');

// Получение баланса
$smsc->getBalance();

// Получение информации об операторе
$smsc->getOperatorInfo('7991111111');

// Получения статуса сообщения
$smsc->getStatus('+7991111111', 6, SMSCenter::STATUS_INFO_EXT);

// Получение стоимости рассылки
$smsc->getCost('+7991111111', 'Начало около 251 млн лет, конец — 201 млн лет назад, длительность около 50 млн лет.');
?>
```
Лицензия: Да хоть как используйте. Упомянете меня — и на том спасибо.