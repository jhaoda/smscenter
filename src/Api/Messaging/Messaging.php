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

namespace JhaoDa\SmsCenter\Api\Messaging;

use JhaoDa\SmsCenter\Core\Message;
use JhaoDa\SmsCenter\Api\Endpoint;

final class Messaging extends Endpoint
{
    /**
     * @param  Message  $message
     */
    public function send(Message $message)
    {
        return $this->client->send('/sys/send.php', $message, MessageResponse::class);
    }

    /**
     * @param  iterable|Message[]  $messages
     */
    public function sendMany(iterable $messages)
    {
        //
    }
}
