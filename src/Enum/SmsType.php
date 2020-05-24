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
 * Тип SMS.
 *
 * @method static SmsType HLR()
 * @method static SmsType BIN()
 * @method static SmsType HEX()
 * @method static SmsType PUSH()
 * @method static SmsType PING()
 * @method static SmsType FLASH()
 * @method static SmsType SOCIAL()
 */
final class SmsType extends Enum
{
    private const HLR    = 'hlr';
    private const BIN    = 'bin';
    private const HEX    = 'hex';
    private const PUSH   = 'push';
    private const PING   = 'ping';
    private const FLASH  = 'flash';
    private const SOCIAL = 'soc';
}
