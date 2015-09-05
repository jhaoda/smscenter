<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SMSCenter\Exception;
use JhaoDa\SmsCenter\Transliterable;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

class Mms extends AbstractMessage implements TransliterableContract
{
    use Transliterable;

    public $subject;

    public function __construct($phones, $message = null, $subject = null)
    {
        if (empty($message) && empty($subject)) {
            throw new Exception('Необходимо заполнить "$message" или "$subject"');
        }

        $this->setPhones($phones);

        $this->message = $message;
        $this->subject = $subject;
    }

    public function getType()
    {
        return self::TYPE_MMS;
    }
}
