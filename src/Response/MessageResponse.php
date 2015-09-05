<?php

namespace JhaoDa\SmsCenter\Response;

/**
 * Class MessageResponse
 *
 * @property-read  int    $id       идентификатор сообщения
 * @property-read  int    $cnt      количество фактически отправленных сообщений
 * @property-read  float  $cost     стоимость всех сообщений
 * @property-read  float  $balance  новый баланс
 *
 * @package JhaoDa\SmsCenter\Response
 */
class MessageResponse extends AbstractResponse
{
    public function __toString()
    {
        return $this->id.': '.$this->cnt;
    }
}
