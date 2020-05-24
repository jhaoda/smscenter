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

final class CouldNotAttachFile extends \InvalidArgumentException implements Exception
{
    public static function becauseMaxNumberOfFilesExceeded(int $maxFilesNumber): self
    {
        return new static("Максимум {$maxFilesNumber} файла.");
    }

    public static function becauseMaxFileSizeExceeded(string $path, int $maxFileSize): self
    {
        return new static(
            \sprintf('Размер файла "%s" больше, чем %fМб.', $path, $maxFileSize / (1024 * 1024))
        );
    }

    public static function becauseMaxTotalSizeOfFilesExceeded(int $maxTotalSizeOfFiles): self
    {
        return new static(
            \sprintf('Размер всех файлов не должен превышать %fМб.', $maxTotalSizeOfFiles / (1024 * 1024))
        );
    }

    public static function becauseNotReadable(string $path): self
    {
        return new static("Невозможно получить доступ к файлу \"{$path}\".");
    }
}
