<?php

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Message\Concerns;

use JhaoDa\SmsCenter\Enum\Transliteration;

trait Transliterable
{
    /**
     * Cпособ транслитерации.
     *
     * @param  Transliteration  $mode
     *
     * @return $this
     */
    public function transliterate(Transliteration $mode)
    {
        $this->params['translit'] = $mode->getValue();

        return $this;
    }
}
