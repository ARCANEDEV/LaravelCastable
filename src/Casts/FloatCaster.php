<?php namespace Arcanedev\LaravelCastable\Casts;

class FloatCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function cast($value)
    {
        return floatval($value);
    }

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function uncast($value)
    {
        return (string) $value;
    }
}
