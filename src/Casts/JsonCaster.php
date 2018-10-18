<?php namespace Arcanedev\LaravelCastable\Casts;

class JsonCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  string  $value
     *
     * @return array
     */
    public static function cast($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param  array  $value
     *
     * @return string
     */
    public static function uncast($value)
    {
        return json_encode($value);
    }
}
