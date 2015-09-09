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
     * Выполнение синхронного POST-запроса.
     *
     * @param  string  $resource
     * @param  array   $params
     * @param  array   $files
     * @param  bool    $async
     *
     * @return ResponseInterface
     */
    public function request($resource, $params = [], $files = [], $async = false)
    {
        $method = $async ? 'postAsync' : 'post';

        list($key, $params) = $this->createPayload($params, $files);

        $response = $this->guzzle->{$method}($resource.'.php', [
            'connect_timeout' => $this->timeout,
            'timeout'         => $this->timeout,
            $key              => $params,
        ]);

        return $response;
    }

    /**
     * Выполнение асинхронного POST-запроса.
     *
     * @param  string  $resource
     * @param  array   $params
     * @param  array   $files
     *
     * @return ResponseInterface
     */
    public function requestAsync($resource, $params = [], $files = [])
    {
        return $this->request($resource, $params, $files, true);
    }

    /**
     * @param  array  $params
     * @param  array  $files
     *
     * @return array
     */
    private function createPayload($params, $files = [])
    {
        if (empty($files)) {
            return ['form_params', $params];
        }

        $items = [];

        foreach ($params as $key => $value) {
            $items[] = ['name' => $key, 'contents' => (string) $value];
        }

        foreach ($files as $key => $file) {
            $items[] = ['name' => 'file'.$key, 'contents' => fopen($file, 'r')];
        }

        return ['multipart', $items];
    }
}
