<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SMSCenter\Exception;
use JhaoDa\SmsCenter\Transliterable;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

class Mms extends AbstractMessageWithAttachments implements TransliterableContract
{
    use Transliterable;

    public $subject;

    protected $maxFiles    = 3;
    protected $maxFileSize = 524288; // 0.5Mb

    public function __construct($phones, $message = null, $subject = null)
    {
        if (empty($message) && empty($subject)) {
            throw new Exception('Необходимо заполнить "$message" или "$subject"');
        }

        $this->setPhones($phones);

        $this->message = $message;
        $this->subject = $subject;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $params = parent::toArray();

        if ($this->subject) {
            $params['subj'] = $this->subject;
        }

        return $params;
    }

    public function getType()
    {
        return self::TYPE_MMS;
    }
}
