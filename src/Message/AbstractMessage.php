<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Api;
use JhaoDa\SmsCenter\Contract\Transliterable;
use JhaoDa\SmsCenter\Exception;

abstract class AbstractMessage
{
    const TYPE_SMS   = 0;
    const TYPE_FLASH = 1;
    const TYPE_WAP   = 2;
    const TYPE_HLR   = 3;
    const TYPE_BIN   = 4;
    const TYPE_HEX   = 5;
    const TYPE_PING  = 6;
    const TYPE_MMS   = 7;
    const TYPE_MAIL  = 8;
    const TYPE_CALL  = 9;

    const CHARSET_UTF8 = 'utf-8';
    const CHARSET_KOI8 = 'koi8-r';
    const CHARSET_1251 = 'windows-1251';

    protected $phones;
    protected $message;

    protected $params = [];

    private $charsetsArray = [self::CHARSET_UTF8, self::CHARSET_KOI8, self::CHARSET_1251];
    private $costsArray    = [Api::COST_ONLY, Api::COST_SEND, Api::COST_BALANCE];
    private $typesArray    = [
        null, 'flash=1', 'push=1', 'hlr=1', 'bin=1', 'bin=2', 'ping=1', 'mms=1', 'mail=1', 'call=1'
    ];

    /**
     * @throws Exception
     *
     * @return int
     */
    public function getType()
    {
        throw new Exception('Необходимо указать тип сообщения.');
    }

    /**
     * @param   string  $charset
     *
     * @return  $this
     */
    public function setCharset($charset)
    {
        if (!in_array($charset, $this->charsetsArray)) {
            $charset = self::CHARSET_UTF8;
        }

        $this->params['charset'] = $charset;

        return $this;
    }

    /**
     * @param  string|array  $phones
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setPhones($phones)
    {
        if (empty($phones)) {
            throw new Exception('Параметр "phones" является обязательным.');
        }

        if (is_string($phones)) {
            $phones = [$phones];
        }

        $this->phones = array_map(Api::class.'::formatPhone', $phones);

        return $this;
    }

    public function setSender($name)
    {
        if (!empty($name)) {
            $this->params['sender'] = $name;
        }

        return $this;
    }

    public function setCostMode($mode)
    {
        if (in_array($mode, $this->costsArray)) {
            $this->params['cost'] = $mode;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $params = array_merge([
            'phones'   => join(';', $this->phones),
            'charset'  => self::CHARSET_UTF8,
            'translit' => Transliterable::TRANSLIT_NONE
        ], $this->params);

        if ($this->message) {
            $params['mes'] = $this->message;
        }

        if (($pair = $this->typesArray[$this->getType()]) !== null) {
            list($key, $value) = explode('=', $pair);
            $params[$key] = $value;
        }

        return $params;
    }
}
