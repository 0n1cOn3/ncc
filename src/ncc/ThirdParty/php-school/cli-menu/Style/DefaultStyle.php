<?php

declare(strict_types=1);

namespace ncc\PhpSchool\CliMenu\Style;

use ncc\PhpSchool\CliMenu\MenuItem\MenuItemInterface;

class DefaultStyle implements ItemStyle
{
    public function hasChangedFromDefaults() : bool
    {
        return true;
    }

    public function getDisplaysExtra() : bool
    {
        return false;
    }

    public function getItemExtra() : string
    {
        return '';
    }

    public function getMarker(MenuItemInterface $item, bool $isSelected) : string
    {
        return '';
    }
}
