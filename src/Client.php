<?php

namespace JhaoDa\SmsCenter;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private $baseUri = '://smsc.ru/sys/';
    private $timeout = 5;

    /**
     * @type GuzzleClient
     */
    private $guzzle;

    public function __construct($secure = false, $timeout = 5)
    {
        $this->timeout = $timeout;

        $this->guzzle = new GuzzleClient([
            'base_uri' => ($secure ? 'https' : 'http').$this->baseUri
        ]);
    }

    /**
     * @param  string  $resource
     * @param  array   $params
     *
     * @return ResponseInterface
     */
    public function request($resource, $params = [])
    {
        $async  = (isset($params['__async']) && ($params['__async'] == true));
        $method = $async ? 'postAsync' : 'post';
        unset($params['__async']);

        list($key, $params) = $this->createPayload($params);

        $response = $this->guzzle->{$method}($resource.'.php', [
            'connect_timeout' => $this->timeout,
            'timeout'         => $this->timeout,
            $key              => $params,
        ]);

        return $response;
    }

    /**
     * @param  string  $resource
     * @param  array   $params
     *
     * @return ResponseInterface
     */
    public function requestAsync($resource, $params = [])
    {
        $params['__async'] = true;

        return $this->request($resource, $params);
    }

    /**
     * @param  array  $params
     *
     * @return array
     */
    private function createPayload($params)
    {
        return ['form_params', $params];
    }
}
