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
use JhaoDa\SmsCenter\Message\Concerns\Transliterable;
use JhaoDa\SmsCenter\Message\Concerns\WithAttachments;
use JhaoDa\SmsCenter\Message\Exception\CouldNotCreateMessage;

final class Mms extends AbstractMessage
{
    use Transliterable;
    use WithAttachments;

    private const MAX_NUMBER_OF_FILES = 20;
    private const TOTAL_SIZE_OF_FILES = 314572800; // 300 Kb

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $subject
     * @param  string|null               $id
     */
    public function __construct($to, ?string $content, ?string $subject, ?string $id = null)
    {
        if (empty($content) && empty($subject)) {
            throw CouldNotCreateMessage::invalidMmsArguments();
        }

        if (! empty($subject)) {
            $this->params['subj'] = $subject;
        }

        $this->to($to);
        $this->withId($id);

        $this->content = $content;
    }

    public function getType(): MessageType
    {
        return MessageType::MMS();
    }

    protected function getMaxFileSize(): ?int
    {
        return null;
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
