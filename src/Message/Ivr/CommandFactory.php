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

final class CommandFactory
{
    private const MAX_NUMBERS_FOR_CALL = 9;

    /** @var string */
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function end(): GenericMenuItem
    {
        return new GenericMenuItem($this->key, 'end');
    }

    public function ping(): GenericMenuItem
    {
        return new GenericMenuItem($this->key, 'url');
    }

    public function text(string $value): GenericMenuItem
    {
        return new GenericMenuItem($this->key, $value);
    }

    /**
     * @param  iterable|string[]|string  $phones
     *
     * @return CallMenuItem
     */
    public function call($phones): CallMenuItem
    {
        if ($phones instanceof \Traversable) {
            $phones = \iterator_to_array($phones);
        }

        if (\is_string($phones)) {
            $phones = [$phones];
        }

        if (\count($phones) > self::MAX_NUMBERS_FOR_CALL) {
            throw new \InvalidArgumentException(\sprintf('Не более %d номеров.', self::MAX_NUMBERS_FOR_CALL));
        }

        return new CallMenuItem($this->key, $phones);
    }

    public function sms(string $phone, string $sender, string $message): GenericMenuItem
    {
        return new GenericMenuItem($this->key, "sms: {$phone},{$sender},{$message}");
    }
}
