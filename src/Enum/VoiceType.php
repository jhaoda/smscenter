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
 * Голос, используемый для озвучивания текста.
 *
 * @method static VoiceType MALE()
 * @method static VoiceType MALE_2()
 * @method static VoiceType FEMALE()
 * @method static VoiceType FEMALE_2()
 */
final class VoiceType extends Enum
{
    private const MALE     = 'm';
    private const MALE_2   = 'm2';
    private const FEMALE   = 'w';
    private const FEMALE_2 = 'w2';
}
