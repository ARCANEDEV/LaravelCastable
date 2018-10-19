<?php namespace Arcanedev\LaravelCastable\Casts;

class IntegerCaster extends AbstractCaster
{
    /**
     * @param  mixed  $value
     *
     * @return int
     */
    public function cast($value)
    {
        return intval($value);
    }
}
