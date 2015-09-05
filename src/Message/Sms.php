<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Transliterable;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

class Sms extends AbstractMessage implements TransliterableContract
{
    use Transliterable;

    public $comment;

    private $type;

    public function __construct($phones, $message = null, $type = self::TYPE_SMS)
    {
        $this->setPhones($phones);

        $this->message = $message;
        $this->type    = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
