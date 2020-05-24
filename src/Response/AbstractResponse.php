<?php

namespace JhaoDa\SmsCenter\Response;

abstract class AbstractResponse
{
    /** @var array */
    protected $response;

    public function __construct($raw)
    {
        $this->response = \json_decode($raw, true);

        if (isset($this->response['error'])) {
            throw new \RuntimeException($this->response['error'], $this->response['error_code']);
        }
    }

    public function all(): array
    {
        return $this->response;
    }

    public function __get($name)
    {
        return $this->response[$name] ?? null;
    }
}
