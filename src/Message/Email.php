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

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Enum\MessageType;
use JhaoDa\SmsCenter\Message\Concerns\WithAttachments;

final class Email extends AbstractMessage
{
    use WithAttachments;

    private const MAX_NUMBER_OF_FILES = 20;
    private const MAX_FILE_SIZE       = 3221225472; // 3 Mb
    private const TOTAL_SIZE_OF_FILES = 16106127360; // 15 Ьи

    /**
     * @param  iterable|string[]|string  $to
     * @param  string                    $content
     * @param  string                    $from
     * @param  string                    $subject
     * @param  string|null               $id
     */
    public function __construct($to, string $content, string $from, string $subject, ?string $id = null)
    {
        $this->to($to);
        $this->from($from);
        $this->withId($id);

        $this->content = $content;
        $this->params['subj'] = $subject;
    }

    public function getType(): MessageType
    {
        return MessageType::MAIL();
    }

    protected function getMaxFileSize(): ?int
    {
        return self::MAX_FILE_SIZE;
    }

    protected function getMaxNumberOfFiles(): int
    {
        return self::MAX_NUMBER_OF_FILES;
    }

    protected function getMaxTotalSizeOfFiles(): ?int
    {
        return self::TOTAL_SIZE_OF_FILES;
    }
}
