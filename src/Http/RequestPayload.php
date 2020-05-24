<?php

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Http;

use GuzzleHttp\Psr7\MultipartStream;
use JhaoDa\SmsCenter\Core\Arrayable;

final class RequestPayload implements Arrayable
{
    /** @var array */
    private $data;

    public function __construct(Arrayable $data)
    {
        $this->data = $data->toArray();
    }

    public function add(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function toStream(): MultipartStream
    {
        return new MultipartStream(
            \iterator_to_array($this->toMultipart())
        );
    }

    public function toArray(): array
    {
        return $this->serialize($this->data);
    }

    private function toMultipart(): \Generator
    {
        foreach ($this->serialize($this->data) as $key => $value) {
            if ($value instanceof \SplFileInfo) {
                yield ['name' => "file{$key}", 'contents' => \fopen($value->getPathname(), 'rb')];
            } else {
                yield ['name' => $key, 'contents' => (string) $value];
            }
        }
    }

    private function serialize(array $data): array
    {
        return \array_map(function ($value) {
            if (\is_object($value) && $value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if (\is_array($value)) {
                return $this->serialize($value);
            }

            return $value;
        }, \array_filter($data));
    }
}
