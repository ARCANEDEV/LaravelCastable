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
     * @return float
     */
    public function cast($value)
    {
        return floatval($value);
    }

    /**
     * @param  float  $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return (string) $value;
    }
}
