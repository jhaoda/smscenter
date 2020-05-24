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
 * Тип сообщения.
 *
 * @method static MessageType SMS()
 * @method static MessageType MMS()
 * @method static MessageType MAIL()
 * @method static MessageType CALL()
 * @method static MessageType CODE()
 * @method static MessageType VIBER()
 */
final class MessageType extends Enum
{
    private const SMS   = null;
    private const MMS   = 'mms';
    private const MAIL  = 'mail';
    private const CALL  = 'call';
    private const CODE  = 'call';
    private const VIBER = 'viber';
}
