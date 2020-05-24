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

namespace JhaoDa\SmsCenter\Tests\Http;

use PHPUnit\Framework\TestCase;
use JhaoDa\SmsCenter\Http\Authenticator;
use JhaoDa\SmsCenter\Core\GenericRequest;
use JhaoDa\SmsCenter\Http\RequestPayload;

class AuthenticatorTest extends TestCase
{
    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(Authenticator::class, new Authenticator('foo', 'bar'));
    }

    public function test_it_can_autenticate_payload(): void
    {
        $stream = (new Authenticator('foo', 'bar'))
            ->authenticate(new RequestPayload(GenericRequest::create([])))
            ->toStream();

        $this->assertEquals(
            \implode("\r\n", [
                "--{$stream->getBoundary()}",
                'Content-Disposition: form-data; name="login"',
                'Content-Length: 3',
                '',
                "foo",
                "--{$stream->getBoundary()}",
                'Content-Disposition: form-data; name="psw"',
                'Content-Length: 3',
                '',
                "bar",
                "--{$stream->getBoundary()}--",
                ''
            ]),
            $stream->getContents()
        );
    }
}
