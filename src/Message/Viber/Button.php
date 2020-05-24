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

namespace JhaoDa\SmsCenter\Message\Viber;

final class Button
{
    /** @var string */
    private $url;

    /** @var string */
    private $text;

    public static function create(string $url, string $text): self
    {
        return new self(...\func_get_args());
    }

    public function __construct(string $url, string $text)
    {
        // TODO: поддержка телефонов (через tel:)
        $this->url = $url;

        // TODO: 10 символов для кириллицы и 20 символов для латиницы
        $this->text = $text;
    }

    public function __toString(): string
    {
        return \sprintf('{button,%s,%s}', $this->url, $this->text);
    }
}
