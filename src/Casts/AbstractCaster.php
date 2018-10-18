<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

abstract class AbstractCaster implements Caster
{
    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return static::cast($value);
    }
}
