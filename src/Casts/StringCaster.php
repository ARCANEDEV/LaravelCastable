<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

class StringCaster implements Caster
{
    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return (string) $value;
    }

    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return (string) $value;
    }
}
