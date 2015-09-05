<?php

namespace JhaoDa\SmsCenter\Response;

/**
 * Class BalanceResponse
 *
 * @property-read  string  $credit    текущее состояние установленного кредита
 * @property-read  string  $balance   текущее состояние баланса
 * @property-read  string  $currency  валюта клиента
 *
 * @package JhaoDa\SmsCenter\Response
 */
class BalanceResponse extends AbstractResponse
{
    public function __toString()
    {
        return $this->balance.' '.$this->currency;
    }
}
