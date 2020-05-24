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
use JhaoDa\SmsCenter\Message\Ivr\MenuBuilder;

final class IvrMenu extends AbstractMessage
{
    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $id
     *
     * @return MenuBuilder
     */
    public static function builder($to, ?string $id = null): MenuBuilder
    {
        return MenuBuilder::create($to, $id);
    }

    public static function create($to, string $content, ?string $id = null): self
    {
        return new self(...\func_get_args());
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

    public function getType(): MessageType
    {
        return MessageType::CALL();
    }
}
