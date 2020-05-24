<?php

/**
 * This file is part of SmsCenter SDK package.
 *
 * © JhaoDa (https://github.com/jhaoda)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Message\Ivr;

final class MenuItemFactory
{
    public static function back(): GenericMenuItem
    {
        return new GenericMenuItem('*', 'back');
    }

    public static function start(): GenericMenuItem
    {
        return new GenericMenuItem('#', 'start');
    }

    public static function repeat(): GenericMenuItem
    {
        return new GenericMenuItem('0', 'repeat');
    }

    public static function error(string $text): GenericMenuItem
    {
        return new GenericMenuItem('err', $text);
    }

    public static function label(string $label, string $text): GenericMenuItem
    {
        return new GenericMenuItem($label, $text);
    }

    public static function key(string $key): CommandFactory
    {
        return new CommandFactory($key);
    }
}
