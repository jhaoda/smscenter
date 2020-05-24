<?php

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Api;

use JhaoDa\SmsCenter\Http\ApiClient;

abstract class Endpoint
{
    /** @var ApiClient */
    protected $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }
}
