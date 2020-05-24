<?php

/**
 * This file is part of SmsCenter SDK package.
 *
 * © JhaoDa (https://github.com/jhaoda)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Message\Ivr;

use JhaoDa\SmsCenter\Message\IvrMenu;

final class MenuBuilder
{
    /** @var mixed */
    private $id;

    /** @var mixed */
    private $to;

    /** @var array */
    private $data = [];

    /** @var MenuItem[] */
    private $items;

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $id
     *
     * @return MenuBuilder
     */
    public static function create($to, ?string $id = null): self
    {
        return new self($to, $id);
    }

    /**
     * @param  iterable|string[]|string  $to
     * @param  string|null               $id
     */
    public function __construct($to, ?string $id = null)
    {
        $this->to = $to;
        $this->id = $id;
    }

    public function intro(string $text)
    {
        $this->data['intro'] = $text;

        return $this;
    }

    public function outro(string $text)
    {
        $this->data['outro'] = $text;

        return $this;
    }

    public function description(string $text)
    {
        $this->data['description'] = $text;

        return $this;
    }

    public function addItem(MenuItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function build(): IvrMenu
    {
        $menu = \vsprintf("%s\n\n{menu: %s\n%s}\n\n%s", [
            $this->data['intro'],
            $this->data['description'],
            \implode("\n", $this->items),
            $this->data['outro'],
        ]);

        if (\mb_strlen($menu) > 1000) {
            throw new \InvalidArgumentException();
        }

        return new IvrMenu($this->to, \urlencode($menu), $this->id);
    }
}
