<?php namespace Arcanedev\LaravelCastable;

class Caster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public static function cast($type, $value)
    {
        return static::hasCaster($type)
            ? (static::getCaster($type))::cast($value)
            : $value;
    }

    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public static function uncast($type, $value)
    {
        return static::hasCaster($type)
            ? (static::getCaster($type))::uncast($value)
            : $value;
    }

    /**
     * Check if has caster.
     *
     * @param  string  $type
     *
     * @return bool
     */
    public static function hasCaster($type)
    {
        return config()->has("laravel-castable.casters.$type");
    }

    /**
     * Get the caster.
     *
     * @param  string  $type
     *
     * @return string
     */
    public static function getCaster($type)
    {
        return config()->get("laravel-castable.casters.$type");
    }
}
