<?php

namespace JhaoDa\SmsCenter\Response;

/**
 * Class OperatorResponse
 *
 * @property-read  string  $tz        часовой пояс региона регистрации номера абонента
 * @property-read  string  $mnc       числовой код оператора абонента
 * @property-read  string  $mcc       числовой код страны абонента
 * @property-read  string  $region    регион регистрации номера абонента
 * @property-read  string  $country   название страны регистрации номера абонента
 * @property-read  string  $operator  мобильный оператор абонента
 *
 * @package JhaoDa\SmsCenter\Response
 */
class OperatorResponse extends AbstractResponse
{
    public function __toString()
    {
        return sprintf('%s, %s, %s (mcc: %s, mnc: %s, tz: %s)',
            $this->operator,
            $this->region,
            $this->country,
            $this->mcc,
            $this->mnc,
            $this->tz
        );
    }
}
