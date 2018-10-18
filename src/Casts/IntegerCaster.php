<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

class IntegerCaster implements Caster
{
    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return intval($value);
    }

    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return static::cast($value);
    }
}
