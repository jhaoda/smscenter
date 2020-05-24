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

namespace JhaoDa\SmsCenter\Http;

final class Authenticator
{
    /** @var string */
    private $login;

    /** @var string */
    private $password;

    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    public function authenticate(RequestPayload $payload): RequestPayload
    {
        $payload->add('login', $this->login);
        $payload->add('psw', $this->password);

        return $payload;
    }
}
