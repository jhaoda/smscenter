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

namespace JhaoDa\SmsCenter\Core;

final class GenericRequest implements Arrayable
{
    /** @var array */
    private $data = [];

    public static function create(iterable $data): self
    {
        return new self($data);
    }

    public function __construct(iterable $data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Arrayable) {
                $this->data[$key] = $value->toArray();
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
