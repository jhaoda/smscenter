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

final class GenericMenuItem extends MenuItem
{
    /** @var string */
    private $data;

    public function __construct(string $key, string $data)
    {
        parent::__construct($key);

        $this->data = $data;
    }

    public function __toString(): string
    {
        return "{$this->key}: {$this->data}";
    }
}
