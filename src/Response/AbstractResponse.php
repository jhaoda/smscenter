<?php

namespace JhaoDa\SmsCenter\Response;

use JhaoDa\SmsCenter\Exception;

abstract class AbstractResponse
{
    private $response;

    public function __construct($raw)
    {
        $this->response = json_decode($raw, true);

        if (isset($this->response['error'])) {
            throw new Exception($this->response['error'], $this->response['error_code']);
        }
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->response;
    }

    public function __get($name)
    {
        if (isset($this->response[$name])) {
            return $this->response[$name];
        }

        return null;
    }
}
