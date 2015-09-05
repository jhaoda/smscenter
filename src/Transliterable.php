<?php

namespace JhaoDa\SmsCenter;

trait Transliterable
{
    public function setTranslit($mode)
    {
        $this->params['translit'] = $mode;
    }
}
