<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

class BooleanCaster implements Caster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @return BooleanCaster
     */
    public static function cast($value)
    {
        return boolval($value);
    }

    /**
     *
     * @return string
     */
    public static function uncast($value)
    {
        return (int) $value;
    }
}
