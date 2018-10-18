<?php namespace Arcanedev\LaravelCastable\Casts;

class StringCaster extends AbstractCaster
{
    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function cast($value)
    {
        return (string) $value;
    }
}
