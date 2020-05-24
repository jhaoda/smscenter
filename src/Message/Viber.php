<?php

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Enum\MessageType;
use JhaoDa\SmsCenter\Message\Concerns\Transliterable;

final class Viber extends AbstractMessage
{
    use Transliterable;

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

    public function getType(): MessageType
    {
        return MessageType::VIBER();
    }
}
