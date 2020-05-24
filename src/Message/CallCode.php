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

final class CallCode extends AbstractMessage
{
    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $id
     */
    public function __construct($to, ?string $id = null)
    {
        $this->to($to);
        $this->withId($id);

        $this->content = 'code';
    }

    public function getType(): MessageType
    {
        return MessageType::CALL();
    }
}
