<?php
namespace Cake;

class Helper_Array
{

    public static function compareDisplayOrder($a, $b)
    {
        return (int) $a['display_order'] > (int) $b['display_order'];
    }
}