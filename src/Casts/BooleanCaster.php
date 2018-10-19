<?php namespace Arcanedev\LaravelCastable\Casts;

class BooleanCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed  $value
     *
     * @return bool
     */
    public function cast($value)
    {
        return boolval($value);
    }

    /**
     *
     * @param  bool  $value
     *
     * @return int
     */
    public function uncast($value)
    {
        return intval($value);
    }
}
