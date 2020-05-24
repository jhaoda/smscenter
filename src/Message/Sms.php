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

use JhaoDa\SmsCenter\Enum\SmsType;
use JhaoDa\SmsCenter\Enum\MessageType;
use JhaoDa\SmsCenter\Message\Concerns\Transliterable;
use JhaoDa\SmsCenter\Message\Exception\CouldNotCreateMessage;

final class Sms extends AbstractMessage
{
    use Transliterable;

    private const MAX_CONTENT_LENGTH = 1000;

    /** @var SmsType|null */
    private $mode;

    /** @var string|null */
    private $comment;

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function plain($to, string $content, ?string $id = null): self
    {
        return new self($to, $content, null, $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function ping($to, ?string $content = null, ?string $id = null): self
    {
        return new self($to, $content, SmsType::PING(), $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function hlr($to, ?string $content = null, ?string $id = null): self
    {
        return new self($to, $content, SmsType::HLR(), $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function push($to, string $content, ?string $id = null): self
    {
        return new self($to, $content, SmsType::PUSH(), $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function flash($to, string $content, ?string $id = null): self
    {
        return new self($to, $content, SmsType::FLASH(), $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  string|null               $id
     *
     * @return Sms
     */
    public static function social($to, string $content, ?string $id = null): self
    {
        return new self($to, $content, SmsType::SOCIAL(), $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $content
     * @param  SmsType                   $mode
     * @param  string|null               $id
     */
    public function __construct($to, ?string $content = null, ?SmsType $mode = null, ?string $id = null)
    {
        // пустыми могут быть только сообщения типа ping и hlr
        if (empty($content) && !\in_array($this->getType(), [SmsType::HLR(), SmsType::PING()], true)) {
            throw CouldNotCreateMessage::messageCanNotBeEmpty();
        }

        if (\mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            throw CouldNotCreateMessage::messageLengthLimitExceeded(self::MAX_CONTENT_LENGTH);
        }

        $this->to($to);
        $this->withId($id);

        $this->mode = $mode;
        $this->content = $content;
    }

    /**
     * Добавление комментария.
     *
     * @param  string  $comment
     *
     * @return $this
     */
    public function comment(string $comment)
    {
        if ($this->mode === null) {
            $this->comment = $comment;
        }

        return $this;
    }

    /**
     * Время «жизни» sms-сообщения.
     *
     * @param  int  $ttl  от 1 до 24 часов
     *
     * @return $this
     */
    public function withTtl(int $ttl)
    {
        $this->params['valid'] = $ttl;

        return $this;
    }

    public function toArray(): array
    {
        $params = parent::toArray();

        if ($this->mode) {
            $this->params[$this->mode->getValue()] = 1;
        }

        if ($this->mode === null && !empty($this->comment)) {
            $params['mes'] .= "\n~~~\n{$this->comment}";
        }

        return $params;
    }

    public function getType(): MessageType
    {
        return MessageType::SMS();
    }
}
