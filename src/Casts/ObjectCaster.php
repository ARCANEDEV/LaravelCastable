<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

class ObjectCaster implements Caster
{
    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return json_decode($value, false);
    }

    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return json_encode($value);
    }
}
