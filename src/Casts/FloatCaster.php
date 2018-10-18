<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

class FloatCaster implements Caster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return floatval($value);
    }

    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return (string) $value;
    }
}
