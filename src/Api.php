<?php

namespace JhaoDa\SmsCenter;

use GuzzleHttp\Promise;
use JhaoDa\SmsCenter\Message\Sms;
use JhaoDa\SmsCenter\Message\AbstractMessage;
use JhaoDa\SmsCenter\Response\MessageResponse;
use JhaoDa\SmsCenter\Response\StatusResponse;
use JhaoDa\SmsCenter\Response\BalanceResponse;
use JhaoDa\SmsCenter\Response\OperatorResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Библиотека для работы с сервисом SMS-Центр (smsc.ru).
 *
 * @version 3.0.0-dev
 * @author  JhaoDa <jhaoda@gmail.com>
 * @link    https://github.com/jhaoda/smsc
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright 2013-2015 JhaoDa
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class Api
{
    const VERSION = '3.0.0-dev';

    const COST_NO      = 0;
    const COST_ONLY    = 1;
    const COST_SEND    = 2;
    const COST_BALANCE = 3;

    const FORMAT_PLAIN     = 0;
    const FORMAT_PLAIN_ALT = 1;
    const FORMAT_XML       = 2;
    const FORMAT_JSON      = 3;

    const STATUS_PLAIN    = 0;
    const STATUS_INFO     = 1;
    const STATUS_INFO_EXT = 2;

    const ZONE_1           = 1;
    const ZONE_2           = 2;
    const ZONE_3           = 3;
    const ZONE_RU          = 10;
    const ZONE_UA          = 20;
    const ZONE_SNG         = 30;
    const ZONE_KZ_KCELL    = 40;
    const ZONE_KZ_TELE2    = 50;
    const ZONE_KZ_BEELINE  = 60;
    const ZONE_KZ_PATHWORD = 70;

    /**
     * @type Client
     */
    protected $client;

    private $login;
    private $password;
    private $sender;
    private $secure  = false;
    private $timeout = 5;
    private $format  = self::FORMAT_JSON;

    private static $chargingZonePatterns = [
        self::ZONE_RU          => '~^\+?(79|73|74|78)~',
        self::ZONE_UA          => '~^\+?380~',
        self::ZONE_SNG         => '~^\+?(7940|374|375|995|996|370|992|993|998)~',
        self::ZONE_KZ_TELE2    => '~^\+?(7707|7747)~',
        self::ZONE_KZ_KCELL    => '~^\+?(7701|7702|7775|7778)~',
        self::ZONE_KZ_BEELINE  => '~^\+?(7777|7705|7771|7776)~',
        self::ZONE_KZ_PATHWORD => '~^\+?(7700|7717|7727|7725|7721|7718|7713|7712)~',
        //self::ZONE_1   => '~^\+?(994|213|244|376|54|93|880|973|591|387|58|84|241|233|502|852|299|20|972|91|92|62|962|964|98|353|354|855|237|1|254|357|57|242|506|965|856|231|423|352|261|389|60|960|356|52|976|971|595|503|966|381|65|421|386|66|255|216|598|63|385|382|56|94|593|372|27|1876|81)~',
        //self::ZONE_2   => '~^\+?(44|359|30|45|86|53|371|373|48|886|358|420|82)~'
    ];

    /**
     * Инициализация.
     *
     * @param  string  $login     логин
     * @param  string  $password  пароль или MD5-хэш пароля
     * @param  string  $sender    имя отправителя
     * @param  bool    $secure    использовать https или нет
     * @param  int     $timeout   таймаут запроса
     */
    public function __construct($login, $password, $sender = null, $secure = false, $timeout = 5)
    {
        $this->login    = $login;
        $this->password = $password;
        $this->sender   = $sender;
        $this->secure   = $secure;
        $this->timeout  = $timeout;
    }

    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client($this->secure, $this->timeout);
        }

        return $this->client;
    }

    /**
     * @return int
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param int $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return bool
     */
    public function isFormatJSON()
    {
        return $this->format == self::FORMAT_JSON;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    private function getDefaultParams()
    {
        $defaults = [
            'login'  => $this->login,
            'psw'    => $this->password,
            'fmt'    => $this->format,
        ];

        if ($this->sender !== null) {
            $defaults['sender'] = $this->sender;
        }

        return $defaults;
    }

    public function request($resource, $params = [])
    {
        $params = array_merge($params, $this->getDefaultParams());

        if (!isset($params['charset'])) {
            $params['charset'] = AbstractMessage::CHARSET_UTF8;
        }

        $response = $this->getClient()->request($resource, $params);

        return $response->getBody();
    }

    /**
     * Отправка сообщений.
     *
     * @param  AbstractMessage|AbstractMessage[]  $messages  массив сообщений
     *
     * @return mixed результат выполнения запроса
     */
    public function send($messages)
    {
        $promises = $responses = [];

        if ($messages instanceof AbstractMessage) {
            $messages = [$messages];
        }

        /** @type AbstractMessage $message */
        foreach ($messages as $message) {
            $params = array_merge($this->getDefaultParams(), $message->toArray());

            //print_r($params);
            $promises[] = $this->getClient()->requestAsync('send', $params);
        }

        /** @type ResponseInterface $item */
        foreach (Promise\unwrap($promises) as $item) {
            $responses[] = new MessageResponse($item->getBody());
        }

        return count($responses) == 1 ? $responses[0] : $responses;
    }

    /**
     * Запрос стоимости отправки сообщений без реальной отправки.
     *
     * @param  AbstractMessage|AbstractMessage[]  $messages  массив сообщений
     *
     * @return mixed результат выполнения запроса
     */
    public function getCost($messages)
    {
        $messages = array_map(function ($message) {
            /** @var AbstractMessage $message */
            return $message->setCostMode(self::COST_ONLY);
        }, $messages);

        return $this->send($messages);
    }

    /**
     * Проверка номеров на доступность в реальном времени.
     *
     * @param  string|array  $phones  номера телефонов
     *
     * @return mixed результат выполнения запроса
     */
    public function ping($phones)
    {
        if (is_string($phones)) {
            $phones = (array) $phones;
        }

        $items = array_map(function ($phone) {
            return new Sms($phone, null, AbstractMessage::TYPE_PING);
        }, $phones);

        return $this->send($items);
    }

    /**
     * Получение информации о номерах из базы оператора связи.
     *
     * @param  string|array  $phones  номера телефонов
     *
     * @return mixed результат выполнения запроса
     */
    public function getPhoneInfo($phones)
    {
        if (is_string($phones)) {
            $phones = (array) $phones;
        }

        $items = array_map(function ($phone) {
            return new Sms($phone, null, AbstractMessage::TYPE_HLR);
        }, $phones);

        return $this->send($items);
    }

    /**
     * Получение статуса сообщения.
     *
     * @param  string     $phone  номер телефона
     * @param  int|array  $id     один или несколько идентификаторов сообщений
     * @param  int        $mode   вид ответа: обычный, полный, расширеный
     *
     * @return string|StatusResponse статус сообщения
     */
    public function getStatus($phone, $id, $mode = self::STATUS_PLAIN)
    {
        if (is_array($id)) {
            $id = implode(',', $id);
        }

        $raw =  $this->request('status', [
            'phone' => $phone,
            'id'    => $id,
            'all'   => (int) $mode
        ]);

        return $this->isFormatJSON() ? new StatusResponse($raw) : $raw;
    }

    /**
     * Получение информации об операторе.
     *
     * @param  string  $phone  номер телефона
     *
     * @return string|OperatorResponse информация об операторе
     */
    public function getOperatorInfo($phone)
    {
        $response = $this->request('info', [
            'phone'        => $phone,
            'get_operator' => 1
        ]);

        return $this->isFormatJSON() ? new OperatorResponse($response) : $response;
    }

    /**
     * Запрос баланса.
     *
     * @return string|BalanceResponse текущий баланс
     */
    public function getBalance()
    {
        $response = $this->request('balance', [
            'cur' => 1
        ]);

        return $this->isFormatJSON() ? new BalanceResponse($response) : $response;
    }

    /**
     * Определение тарифной зоны.
     *
     * @param  string  $phone  номер телефона
     *
     * @return int номер тарифной зоны (константы self::ZONE_*)
     */
    public function getChargingZone($phone)
    {
        $phone = self::formatPhone($phone);

        foreach (self::$chargingZonePatterns as $key => $value) {
            if (preg_match($value, $phone)) {
                return $key;
            }
        }

        return self::ZONE_3;
    }

    /**
     * Форматирование номера в международный формат.
     *
     * @param  string  $phone  номер телефона
     *
     * @return string  отформатированный номер телефона
     */
    public static function formatPhone($phone)
    {
        $phone = preg_replace('~[^\d+]~', '', $phone);

        return preg_replace('~^[7|8]~', '+7', $phone);
    }
}
