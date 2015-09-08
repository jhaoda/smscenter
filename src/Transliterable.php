<?php

namespace JhaoDa\SmsCenter;

use JhaoDa\SmsCenter\Message\AbstractMessage;
use JhaoDa\SmsCenter\Contract\Transliterable as TransliterableContract;

trait Transliterable
{
    /**
     * @param  int  $mode
     *
     * @return AbstractMessage
     */
    public function setTranslit($mode)
    {
        if (in_array($mode, [
            TransliterableContract::TRANSLIT_YES,
            TransliterableContract::TRANSLIT_ALT
        ])) {
            $this->params['translit'] = $mode;
        }

        return $this;
    }
}
