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

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use JhaoDa\SmsCenter\Core\Arrayable;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode as guzzle_json_decode;

final class ApiClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const RESPONSE_FORMAT = 3;

    private const API_URL = 'https://smsc.ru/';

    /** @var ClientInterface */
    private $httpClient;

    /** @var Authenticator */
    private $authenticator;

    public function __construct(Authenticator $authenticator, ClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->authenticator = $authenticator;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function send(string $path, Arrayable $data)
    {
        try {
            $response = $this->httpClient->send($this->buildHttpRequest($path, $data));

            $content = $this->getResponseContent($response);

            if (isset($content['error_code'])) {
                throw new \RuntimeException($content['error'], (int) $content['error_code']);
            }

            return $content;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    private function buildHttpRequest(string $path, Arrayable $data): RequestInterface
    {
        $payload = $this->authenticator->authenticate(new RequestPayload($data));

        $payload->add('charset', 'utf-8');
        $payload->add('fmt', self::RESPONSE_FORMAT);

        return (new Request('POST', self::API_URL.$path))->withBody($payload->toStream());
    }

    private function getResponseContent(ResponseInterface $response): array
    {
        return guzzle_json_decode((string) $response->getBody(), true);
    }
}
