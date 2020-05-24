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

use JhaoDa\SmsCenter\Enum\MessageType;

interface Message extends Arrayable
{
    /**
     * Идентификатор сообщения.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Тип сообщения.
     *
     * @return MessageType
     */
    public function getType(): MessageType;
}
