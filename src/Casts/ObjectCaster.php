<?php namespace Arcanedev\LaravelCastable\Casts;

class ObjectCaster extends AbstractCaster
{
    /**
     * @return mixed
     */
    public static function cast($value)
    {
        return json_decode($value);
    }

    /**
     * @return mixed
     */
    public static function uncast($value)
    {
        return json_encode($value);
    }
}
