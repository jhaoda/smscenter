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
 * Способ транслитерации.
 *
 * @method static Transliteration NO()
 * @method static Transliteration YES()
 * @method static Transliteration ALT()
 */
final class Transliteration extends Enum
{
    private const NO  = null;
    private const YES = 1;
    private const ALT = 2;
}
