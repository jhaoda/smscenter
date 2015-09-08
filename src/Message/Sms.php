<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Transliterable;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

class Sms extends AbstractMessage implements TransliterableContract
{
    use Transliterable;

    /**
     * @type string комментарий в sms-сообщении
     */
    private $comment;

    /**
     * Тип сообщения: sms, mms, ping и т.д.
     *
     * @type int
     */
    private $type;

    public function __construct($phones, $message = null, $type = self::TYPE_SMS)
    {
        $this->setPhones($phones);

        $this->message = $message;
        $this->type    = $type;
    }

    /**
     * Добавление комментария.
     *
     * @param  string  $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        if ($this->type == AbstractMessage::TYPE_SMS) {
            $this->comment = $comment;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $params = parent::toArray();

        if ($this->type == AbstractMessage::TYPE_SMS && $this->comment) {
            $params['mes'] .= "\n~~~\n".$this->comment;
        }

        return $params;
    }

    public function getType()
    {
        return $this->type;
    }
}
