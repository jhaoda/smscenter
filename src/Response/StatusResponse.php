<?php

namespace JhaoDa\SmsCenter\Response;

/**
 * Class StatusResponse
 *
 * @property-read  string  $err             код ошибки
 * @property-read  string  $status          код статуса
 * @property-read  string  $status_name     название статуса
 * @property-read  string  $last_date       дата последнего изменения статуса (DD.MM.YYYY hh:mm:ss)
 * @property-read  string  $last_timestamp  timestamp времени последнего изменения статуса
 * @property-read  string  $send_date       дата отправки сообщения (DD.MM.YYYY hh:mm:ss)
 * @property-read  string  $send_timestamp  timestamp времени отправки сообщения
 * @property-read  string  $phone           номер телефона абонента
 * @property-read  string  $cost            стоимость сообщения
 * @property-read  string  $sender          имя отправителя
 * @property-read  string  $message         текст сообщения
 * @property-read  string  $country         название страны регистрации номера абонента
 * @property-read  string  $operator        название оператора абонента
 * @property-read  string  $region          регион регистрации номера абонента
 *
 * @property-read  string  $imsi            уникальный код IMSI SIM-карты абонента (HLR)
 * @property-read  string  $msc             номер сервис-центра оператора, в сети которого находится абонент (HLR)
 * @property-read  string  $mcc             числовой код страны абонента (HLR)
 * @property-read  string  $mnc             числовой код оператора абонента (HLR)
 * @property-read  string  $cn              название страны регистрации абонента (HLR)
 * @property-read  string  $net             название оператора регистрации абонента (HLR)
 * @property-read  string  $rcn             название роуминговой страны абонента при нахождении в чужой сети (HLR)
 * @property-read  string  $rnet            название роумингового оператора абонента при нахождении в чужой сети (HLR)
 *
 * @package JhaoDa\SmsCenter\Response
 */
class StatusResponse extends AbstractResponse
{
    public function __toString()
    {
        if ($this->phone) {
            return $this->phone.': '.($this->status_name ?: $this->status);
        }

        return (string) $this->status;
    }
}
