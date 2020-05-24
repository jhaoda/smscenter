<?php

/**
 * This file is part of SmsCenter SDK package.
 *
 * © JhaoDa (https://github.com/jhaoda)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @noinspection PhpUnusedPrivateFieldInspection */

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Enum;

use JhaoDa\SmsCenter\Core\Enum;

/**
 * Ошибки.
 *
 * @see https://smsc.ru/api/http/send/sms/sms_answer/#menu
 *
 * @method static Error PRAMETERS() Ошибка в параметрах
 * @method static Error CREDENTIALS() Неверный логин, пароль или попытка отправить сообщения с IP-адреса, не входящего в список разрешенных
 * @method static Error NO_ENOUGH_MONEY() Недостаточно средств
 * @method static Error IP_BLOCKED() IP-адрес временно заблокирован из-за частых ошибок в запросах
 * @method static Error INVALID_DATE() Неверный формат даты
 * @method static Error MESSAGE_DENIED() Сообщение запрещено (по тексту или по имени отправителя)
 * @method static Error INVALID_PHONE() Неверный формат номера телефона
 * @method static Error CANNOT_BE_DELIVERED() Сообщение на указанный номер не может быть доставлено
 * @method static Error TOO_MANY_REQUESTS() Отправка 2+ одинаковых сообщений или 5+ одинаковых запросов стоимости за минуту; 15+ одновременных запросов
 */
final class Error extends Enum
{
    /** Ошибка в параметрах */
    private const PRAMETERS = 1;

    /** Неверный логин, пароль или попытка отправить сообщения с IP-адреса, не входящего в список разрешенных */
    private const CREDENTIALS = 2;

    /** Недостаточно средств */
    private const NO_ENOUGH_MONEY = 3;

    /** IP-адрес временно заблокирован из-за частых ошибок в запросах */
    private const IP_BLOCKED = 4;

    /** Неверный формат даты */
    private const INVALID_DATE = 5;

    /** Сообщение запрещено (по тексту или по имени отправителя) */
    private const MESSAGE_DENIED = 6;

    /** Неверный формат номера телефона */
    private const INVALID_PHONE = 7;

    /** Сообщение на указанный номер не может быть доставлено */
    private const CANNOT_BE_DELIVERED = 8;

    /** Отправка 2+ одинаковых сообщений или 5+ одинаковых запросов стоимости за минуту; 15+ одновременных запросов */
    private const TOO_MANY_REQUESTS = 9;
}
