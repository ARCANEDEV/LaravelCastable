<?php namespace Arcanedev\LaravelCastable\Contracts;

interface Caster
{
    /**
     * @return mixed
     */
    public static function cast($value);

    /**
     * @return mixed
     */
    public static function uncast($value);
}
