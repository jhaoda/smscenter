<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Transliterable;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

class Voice extends AbstractMessage implements TransliterableContract
{
    use Transliterable;

    const VOICE_MALE     = 'm';
    const VOICE_MALE_2   = 'm2';
    const VOICE_FEMALE   = 'w';
    const VOICE_FEMALE_2 = 'w2';
    const VOICE_FEMALE_3 = 'w3';
    const VOICE_FEMALE_4 = 'w4';

    public function __construct($phones, $message, $voice = self::VOICE_MALE)
    {
        $this->setPhones($phones);

        $this->message = $message;

        $this->params['voice'] = $voice;
    }

    public function getType()
    {
        return self::TYPE_CALL;
    }
}
