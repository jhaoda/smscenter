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

namespace JhaoDa\SmsCenter\Api\Messaging;

class MessageResponse// extends AbstractResponse
{
    private $data;

    /**
     * идентификатор сообщения
     * @return string
     */
    public function getId(): string
    {
        return $this->data['id'];
    }

    /**
     * количество фактически отправленных сообщений
     * @return int
     */
    public function count(): int
    {
        return $this->data['count'];
    }

    /**
     * стоимость всех сообщений
     * @return float
     */
    public function getCost(): float
    {
        return $this->data['cost'];
    }

    /**
     * новый баланс
     * @return float
     */
    public function getBalance(): float
    {
        return $this->data['balance'];
    }
}
