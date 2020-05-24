<?php

/**
 * Библиотека для работы с сервисом smsc.ru (SMS-Центр)
 *
 * @version 2.0.0
 * @author  JhaoDa <jhaoda@gmail.com>
 * @link    https://github.com/jhaoda/SMSCenter
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright 2013 JhaoDa
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

namespace SMSCenter;

class SMSCenter
{
    const VERSION = '2.0.1';

    const MSG_SMS   = 0;
    const MSG_FLASH = 1;
    const MSG_WAP   = 2;
    const MSG_HLR   = 3;
    const MSG_BIN   = 4;
    const MSG_HEX   = 5;
    const MSG_PING  = 6;

    const COST_NO      = 0;
    const COST_ONLY    = 1;
    const COST_TOTAL   = 2;
    const COST_BALANCE = 3;

    const TRANSLIT_NONE = 0;
    const TRANSLIT_YES  = 1;
    const TRANSLIT_ALT  = 2;

    const FMT_PLAIN     = 0;
    const FMT_PLAIN_ALT = 1;
    const FMT_XML       = 2;
    const FMT_JSON      = 3;

    const STATUS_PLAIN    = 0;
    const STATUS_INFO     = 1;
    const STATUS_INFO_EXT = 2;

    const CHARSET_UTF8 = 'utf-8';
    const CHARSET_KOI8 = 'koi8-r';
    const CHARSET_1251 = 'windows-1251';

    const ZONE_RU  = 10;
    const ZONE_UA  = 20;
    const ZONE_SNG = 30;
    const ZONE_1   = 1;
    const ZONE_2   = 2;
    const ZONE_3   = 3;

    private $login;
    private $password;
    private $useSSL;
    private $options = [];
    private $types = ['', 'flash=1', 'push=1', 'hlr=1', 'bin=1', 'bin=2', 'ping=1'];

    private static $chargingZonePatterns = [
        self::ZONE_RU  => '~^\+?(79|73|74|78)~',
        self::ZONE_UA  => '~^\+?380~',
        self::ZONE_SNG => '~^\+?(7940|374|375|995|77|996|370|992|993|998)~',
        self::ZONE_1   => '~^\+?(994|213|244|376|54|93|880|973|591|387|58|84|241|233|502|852|299|20|972|91|92|62|962|964|98|353|354|855|237|1|254|357|57|242|506|965|856|231|423|352|261|389|60|960|356|52|976|971|595|503|966|381|65|421|386|66|255|216|598|63|385|382|56|94|593|372|27|1876|81)~',
        self::ZONE_2   => '~^\+?(44|359|30|45|86|53|371|373|48|886|358|420|82)~'
    ];

    private static $curl;

    /**
     * Инициализация.
     *
     * @param  string  $login     логин
     * @param  string  $password  пароль
     * @param  bool    $useSSL    использовать HTTPS или нет
     * @param  array   $options   прочие параметры
     */
    public function __construct($login, $password, $useSSL = false, array $options = [])
    {
        $this->login = $login;
        $this->password = $password;
        $this->useSSL = $useSSL;

        $default = [
            'charset' => self::CHARSET_UTF8,
            'fmt'     => self::FMT_JSON
        ];

        $this->options = array_merge($default, $options);
    }

    /**
     * Отправка сообщения.
     *
     * @param  string|array  $phones   номера телефонов
     * @param  string        $message  текст сообщения
     * @param  string        $sender   имя отправителя
     * @param  array         $options  дополнительные параметры
     *
     * @throws \InvalidArgumentException если список телефонов пуст или длина сообщения больше 1000 символов
     *
     * @return bool|string|\stdClass  результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
     */
    public function send($phones, $message, $sender = null, array $options = [])
    {
        if (empty($phones)) {
            throw new \InvalidArgumentException("The 'phones' parameter is empty.");
        }

        if (is_array($phones)) {
            $phones = array_map(__CLASS__.'::clearPhone', $phones);
            $phones = implode(';', $phones);
        } else {
            $phones = self::clearPhone($phones);
        }

        if ($message !== null && empty($message)) {
            throw new \InvalidArgumentException('The message is empty.');
        }

        if (mb_strlen($message, 'UTF-8') > 800) {
            throw new \InvalidArgumentException('The maximum length of a message is 800 symbols.');
        }

        $options['phones'] = $phones;
        $options['mes']    = $message;

        if ($sender !== null) {
            $options['sender'] = $sender;
        }

        return $this->sendRequest('send', $options);
    }

    /**
     * Отправка разных сообщений на несколько номеров.
     *
     * @param  array   $list     массив [номер => сообщение] или [номер, сообщение]
     * @param  string  $sender   имя отправителя
     * @param  array   $options  дополнительные параметры
     *
     * @return bool|string|\stdClass результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
     */
    public function sendMulti(array $list, $sender = null, array $options = [])
    {
        foreach ($list as $key => $value) {
            if (is_array($value)) {
                list($key, $value) = $value;
            }

            $options['list'][] = self::clearPhone($key).':'.str_replace("\n", '\n', $value);
        }

        $options['list'] = implode("\n", $options['list']);

        if ($sender !== null) {
            $options['sender'] = $sender;
        }

        return $this->sendRequest('send', $options);
    }

    /**
     * Проверка номеров на доступность в реальном времени.
     *
     * @param  string|array  $phones  номера телефонов
     *
     * @return bool|string|\stdClass  результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
     */
    public function pingPhone($phones)
    {
        return $this->send($phones, null, null, ['type' => self::MSG_PING]);
    }

    /**
     * Получение стоимости рассылки.
     *
     * @param  string|array  $phones   номера телефонов
     * @param  string        $message  текст сообщения
     * @param  array         $options  дополнительные опции
     *
     * @return bool|string|\stdClass  стоимость рассылки в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
     */
    public function getCost($phones, $message, array $options = [])
    {
        $options['cost'] = self::COST_ONLY;

        return $this->send($phones, $message, null, $options);
    }

    /**
     * Получение стоимости рассылки разные сообщения на несколько номеров.
     *
     * @param  array  $list     массив [номер => сообщение] или [номер, сообщение]
     * @param  array  $options  дополнительные опции
     *
     * @return bool|string|\stdClass  стоимость рассылки в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
     */
    public function getCostMulti(array $list, array $options = [])
    {
        $options['cost'] = self::COST_ONLY;

        return $this->sendMulti($list, null, $options);
    }

    /**
     * Получение статуса сообщения.
     *
     * @param  string      $phone  номер телефона
     * @param  int|string  $id     идентификатор сообщения
     * @param  int         $mode   вид ответа: обычный, полный, расширеный
     *
     * @return bool|string|\stdClass  статус сообщения в виде строки, объекта (FMT_JSON) или false в случае ошибки.
     */
    public function getStatus($phone, $id, $mode = self::STATUS_PLAIN)
    {
        return $this->sendRequest('status', [
            'phone' => $phone,
            'id'    => (int) $id,
            'all'   => (int) $mode,
        ]);
    }

    /**
     * Получение информации об операторе: название и регион регистрации номера абонента.
     *
     * @param  string  $phone  номер телефона
     *
     * @return bool|string|\stdClass  информация об операторе в виде строки, объекта (FMT_JSON) или false в случае ошибки.
     */
    public function getOperatorInfo($phone)
    {
        return $this->sendRequest('info', [
            'get_operator' => '1',
            'phone'        => $phone,
        ]);
    }

    /**
     * Запрос баланса.
     *
     * @param  int  $format  формат ответа сервера (self::FMT_JSON)
     *
     * @return string  баланс в виде строки или false в случае ошибки.
     */
    public function getBalance($format = self::FMT_JSON)
    {
        $response = $this->sendRequest('balance', ['fmt' => $format]);

        if ($format === self::FMT_JSON) {
            return json_decode($response)->balance;
        }

        if ($format === self::FMT_XML) {
            return preg_replace('~</*balance>~', '', $response);
        }

        return $response;
    }

    /**
     * Определение тарифной зоны.
     *
     * @param  string  $phone  номер телефона
     *
     * @return int  номер тарифной зоны (константы self::ZONE_*)
     */
    public function getChargingZone($phone)
    {
        $phone = self::clearPhone($phone);

        foreach (self::$chargingZonePatterns as $key => $value) {
            if (preg_match($value, $phone)) {
                return $key;
            }
        }

        return self::ZONE_3;
    }

    /**
     * Самая умная функция.
     *
     * @param  string  $resource
     * @param  array   $options
     *
     * @throws \InvalidArgumentException
     *
     * @return bool|string|\stdClass
     */
    private function sendRequest($resource, array $options)
    {
        $options = array_merge($this->options, $options);

        if (in_array($resource, ['status', 'info'])) {
            if (isset($options['phone']) && !empty($options['phone'])) {
                $options['phone'] = self::clearPhone($options['phone']);
            } else {
                throw new \InvalidArgumentException("The 'phone' parameter is empty.");
            }
        }

        $params = [
            'login='.urlencode($this->login),
            'psw='.urlencode($this->password),
        ];

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'type':
                    if ($value > 0 && $value < count($this->types)) {
                        $params[] = $this->types[$value];
                    }
                    break;
                default:
                    if (!empty($value)) {
                        $params[] = $key.'='.urlencode($value);
                    }
            }
        }

        $i = 0;

        do {
            (!$i) || sleep(2);
            $ret = $this->execRequest($resource, $params);
        } while ($ret == '' && ++$i < 3);

        if (($resource == 'info' || $resource == 'status') && $options['fmt'] == self::FMT_JSON) {
            if ($options['charset'] === self::CHARSET_1251) {
                $ret = mb_convert_encoding($ret, 'UTF-8', 'WINDOWS-1251');
            } elseif ($options['charset'] === self::CHARSET_KOI8) {
                $ret = mb_convert_encoding($ret, 'UTF-8', 'KOI8-R');
            }
        }

        return !empty($ret) ? $ret : false;
    }

    /**
     * Непосредственно выполнение запроса.
     *
     * @param  string  $resource
     * @param  array   $params
     *
     * @return string  ответ сервера
     */
    private function execRequest($resource, array $params)
    {
        $url = ($this->useSSL ? 'https' : 'http').'://smsc.ru/sys/'.$resource.'.php';
        $query = implode('&', $params);
        $isPOST = $resource === 'send';

        if (function_exists('curl_init')) {
            if (!self::$curl) {
                self::$curl = curl_init();
                curl_setopt_array(self::$curl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT        => 10,
                ]);
            }

            if ($isPOST) {
                curl_setopt_array(self::$curl, [
                    CURLOPT_URL        => $url,
                    CURLOPT_POST       => true,
                    CURLOPT_POSTFIELDS => $query,
                ]);
            } else {
                curl_setopt(self::$curl, CURLOPT_URL, $url.'?'.$query);
            }

            $response = curl_exec(self::$curl);
        } else {
            $options = ['timeout' => 5];

            if ($isPOST) {
                $options = array_merge($options, [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $query,
                ]);
            } else {
                $url .= '?'.$query;
            }

            $response = file_get_contents($url, false, stream_context_create(['http' => $options]));
        }

        return $response;
    }

    /**
     * Удаляет из номера любые символы, кроме цифр.
     *
     * @param  string  $phone  номер телефона
     *
     * @return string  «чистый» номер телефона
     */
    public static function clearPhone($phone)
    {
        return preg_replace('~[^\d+]~', '', $phone);
    }
}
