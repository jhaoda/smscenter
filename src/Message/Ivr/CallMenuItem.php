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

final class CallMenuItem extends MenuItem
{
    /** @var array */
    private $phones;

    /** @var int */
    private $wait;

    /** @var string */
    private $onError;

    /** @var bool */
    private $finish;

    public function __construct(string $key, array $phones)
    {
        parent::__construct($key);

        $this->phones = $phones;
    }

    public function wait(int $value)
    {
        // TODO: min = 0, max = 120, default = 120
        $this->wait = $value;

        return $this;
    }

    public function onError(string $label)
    {
        $this->onError = $label;

        return $this;
    }

    public function finishAfterCall()
    {
        $this->finish = true;

        return $this;
    }

    public function __toString(): string
    {
        return
            'call: '.\implode(',', $this->phones)
            .($this->wait ? " wait={$this->wait}" : null)
            .($this->finish ? ' ok:end' : null)
            .($this->onError ? " err:{$this->onError}" : null)
        ;
    }
}
