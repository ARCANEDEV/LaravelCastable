<?php namespace Arcanedev\LaravelCastable\Casts;

class DateCaster extends DateTimeCaster
{
    public static function cast($value)
    {
        return parent::cast($value)->startOfDay();
    }
}
