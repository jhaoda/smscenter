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

namespace JhaoDa\SmsCenter;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use GuzzleHttp\ClientInterface;
use JhaoDa\SmsCenter\Http\ApiClient;
use JhaoDa\SmsCenter\Http\Authenticator;
use JhaoDa\SmsCenter\Api\Messaging\Messaging;

final class Client implements LoggerAwareInterface
{
    /** @var ApiClient */
    private $client;

    public function __construct(string $login, string $secret, ClientInterface $httpClient)
    {
        $this->client = new ApiClient(new Authenticator($login, $secret), $httpClient, new NullLogger());
    }

    public function messaging(): Messaging
    {
        return new Messaging($this->client);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param  LoggerInterface  $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->client->setLogger($logger);
    }
}
