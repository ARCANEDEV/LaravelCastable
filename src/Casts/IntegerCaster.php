<?php namespace Arcanedev\LaravelCastable\Casts;

class IntegerCaster extends AbstractCaster
{
    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return intval($value);
    }
}
