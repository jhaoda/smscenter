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

namespace JhaoDa\SmsCenter\Message\Exception;

use JhaoDa\SmsCenter\Core\Exception;

final class CouldNotCreateMessage extends \InvalidArgumentException implements Exception
{
    public static function emptyRecipientList(): self
    {
        return new static('Укажите хотя бы одного получателя.');
    }

    public static function messageCanNotBeEmpty(): self
    {
        return new static('Сообщение не может быть пустым.');
    }

    public static function messageLengthLimitExceeded(int $maxLength): self
    {
        return new static("Максимальный размер сообщения — {$maxLength} символов.");
    }

    public static function invalidMmsArguments(): self
    {
        return new static('Один из параметров "content" или "subject" не может быть пустым.');
    }

    public static function invalidEmailArguments(): self
    {
        return new static('Параметры "content", "subject" и "sender" не могут быть пустыми.');
    }
}
