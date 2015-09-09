<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Exception;

class Email extends AbstractMessageWithAttachments
{
    protected $maxFiles    = 4;
    protected $maxFileSize = 1048576; // 1Mb

    public function __construct($phones, $message, $sender, $subject)
    {
        $this->setPhones($phones);

        $this->message = $message;

        $this->params['sender'] = $sender;
        $this->params['subj']   = $subject;
    }

    public function setPhones($phones)
    {
        if (empty($phones)) {
            throw new Exception('Параметр "phones" является обязательным.');
        }

        if (is_string($phones)) {
            $phones = [$phones];
        }

        $this->phones = $phones;

        return $this;
    }

    public function getType()
    {
        return self::TYPE_MAIL;
    }
}
