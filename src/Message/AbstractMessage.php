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

namespace JhaoDa\SmsCenter\Message;

use Ramsey\Uuid\Uuid;
use JhaoDa\SmsCenter\Core\Message as MessageContract;
use JhaoDa\SmsCenter\Message\Concerns\WithAttachments;
use JhaoDa\SmsCenter\Message\Exception\CouldNotCreateMessage;

abstract class AbstractMessage implements MessageContract
{
    /** @var string|null */
    protected $id;

    /** @var string[] */
    protected $to;

    /** @var string|null */
    protected $content;

    /** @var array */
    protected $params = [];

    public function from(string $name)
    {
        $this->params['sender'] = $name;

        return $this;
    }

    /**
     * @param  iterable|string[]|string  $to
     *
     * @return $this
     */
    protected function to($to)
    {
        if (\is_string($to)) {
            $to = [$to];
        }

        if ($to instanceof \Traversable) {
            $to = \iterator_to_array($to);
        }

        if (empty($to)) {
            throw CouldNotCreateMessage::emptyRecipientList();
        }

        if (! $this instanceof Email) {
            $to = \array_map([$this, 'formatPhone'], $to);
        }

        $this->to = $to;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param  string|int  $id
     *
     * @return $this
     */
    protected function withId($id)
    {
        if ($id) {
            $this->id = (string) $id;
        } else {
            $this->id = Uuid::uuid4()->toString();
        }

        return $this;
    }

    public function useUrlShortener(bool $value)
    {
        $this->params['tinyurl'] = (int) $value;

        return $this;
    }

//    public function sendAt(\DateTimeInterface $sendAt = null)
//    {
//        if ($sendAt) {
//            $msk = \DateTime::createFromFormat(\DATE_ATOM, $sendAt->format(\DATE_ATOM))->setTimezone(new \DateTimeZone('Europe/Moscow'));
//
//            \dd($sendAt, $msk);
////            $mskTimezone = new \DateTimeZone('Europe/Moscow');
////            \dd($mskTimezone, $sendAt, $mskTimezone->getOffset($sendAt) / 3600, (new \DateTimeZone('UTC'))->getOffset($sendAt) / 3600);
//
//            //$this->params['tz'] = $sendAt->getTimezone()->getOffset($mskSendAt);
//            //$this->params['time'] = '0'.$sendAt->getTimestamp();
//        } else {
//            unset($this->params['tz'], $this->params['time']);
//        }
//
//        return $this;
//    }
//
//    private $availableParams = [
//        'period', 'freq', 'pp', 'op', 'maxsms'
//    ];
//
//    /**
//     * Установка дополнительных параметров.
//     *
//     * @param  string $name
//     * @param  mixed  $value
//     *
//     * @return $this
//     */
//    public function setParameter($name, $value)
//    {
//        if (\in_array($name, $this->availableParams, true)) {
//            $this->params[$name] = $value;
//        }
//
//        return $this;
//    }
//
    public function toArray(): array
    {
        $params = \array_merge($this->params, [
            'id'      => $this->getId(),
            'phones'  => \implode(';', $this->to),
        ]);

        if ($this->content) {
            $params['mes'] = $this->content;
        }

        if (\in_array(WithAttachments::class, \class_uses(static::class))) {
            /** @var WithAttachments $this */
            $params = \array_merge($params, $this->files);
        }

        return $params;
    }

    private function formatPhone(string $phone): string
    {
        // + — отключает автоисправление номера
        // G — префикс для группы номеров (https://smsc.ru/api/http/send/group/)
        if (\strpos($phone, '+') === 0 || \strpos($phone, 'G') === 0) {
            return $phone;
        }

        return \preg_replace('~^[7|8]~', '7', \preg_replace('~[^\d+]~', '', $phone));
    }
}
