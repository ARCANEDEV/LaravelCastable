<?php namespace Arcanedev\LaravelCastable\Casts;

class BooleanCaster extends AbstractCaster
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
        return intval($value);
    }
}
