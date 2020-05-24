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

use JhaoDa\SmsCenter\Enum\VoiceType;
use JhaoDa\SmsCenter\Enum\MessageType;
use JhaoDa\SmsCenter\Message\Concerns\WithAttachments;

final class Call extends AbstractMessage
{
    use WithAttachments;

    /** @var int */
    private $tries = 8;

    /** @var int */
    private $timeout = 25;

    /** @var int */
    private $retryAfter = 10;

    /** @var VoiceType|null */
    private $voice;

    protected $maxFiles = 4;
    protected $maxFileSize = 3145728; // 3 Mb

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $id
     *
     * @return CallCode
     */
    public static function confirmationCode($to, ?string $id = null): CallCode
    {
        return new CallCode($to, $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string                    $content
     * @param  string|null               $id
     */
    public function __construct($to, string $content, ?string $id = null)
    {
        $this->to($to);
        $this->withId($id);

        $this->content = $content;
    }

    public function voice(VoiceType $voice)
    {
        $this->voice = $voice;

        return $this;
    }

    public function timeout(int $value)
    {
        // $value = 10 > $value ? 10 : 35 < $value ? 35 : $value;
        $this->timeout = $value;

        return $this;
    }

    public function tries(int $value)
    {
        // $value = 1 > $value ? 1 : 9 < $value ? 9 : $value;
        $this->tries = $value;

        return $this;
    }

    public function retryAfter(int $value)
    {
        // $value = 10 > $value ? 10 : 3600 < $value ? 3600 : $value;
        $this->retryAfter = $value;

        return $this;
    }

    public function toArray(): array
    {
        $this->params['voice'] = $this->voice;
        $this->params['param'] = "{$this->timeout},{$this->retryAfter},{$this->tries}";

        return parent::toArray();
    }

    public function getType(): MessageType
    {
        return MessageType::CALL();
    }

    protected function getMaxFileSize(): ?int
    {
        // TODO: Implement getMaxFileSize() method.
    }

    protected function getMaxNumberOfFiles(): int
    {
        // TODO: Implement getMaxNumberOfFiles() method.
    }

    protected function getMaxTotalSizeOfFiles(): ?int
    {
        // TODO: Implement getMaxTotalSizeOfFiles() method.
    }
}
