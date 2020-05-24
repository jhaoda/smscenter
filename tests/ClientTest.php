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

namespace JhaoDa\SmsCenter\Tests;

use Psr\Log\NullLogger;
use JhaoDa\SmsCenter\Client;
use JhaoDa\SmsCenter\Api\Messaging\Messaging;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use GuzzleHttp\Client as HttpClient;

class ClientTest extends TestCase
{
    public function test_client_is_instantiable(): void
    {
        $this->assertInstanceOf(Client::class, $this->createClient());

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->createClient()->setLogger(new NullLogger()));
    }

    public function test_can_get_endpoint(): void
    {
        $client = $this->createClient();

        $this->assertInstanceOf(Messaging::class, $client->messaging());
    }

    private function createClient(): Client
    {
        /** @var HttpClient|MockObject $httpClient */
        $httpClient = $this->createMock(HttpClient::class);

        return new Client('foo', 'bar', $httpClient);
    }
}
