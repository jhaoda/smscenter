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

abstract class MenuItem
{
    /** @var string */
    protected $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }
}
