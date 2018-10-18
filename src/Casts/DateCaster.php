<?php namespace Arcanedev\LaravelCastable\Casts;

class DateCaster extends DateTimeCaster
{
    /**
     * @param  mixed  $value
     *
     * @return \Illuminate\Support\Carbon
     */
    public static function cast($value)
    {
        return parent::cast($value)->startOfDay();
    }

    /**
     * @param \Illuminate\Support\Carbon $value
     *
     * @return string
     */
    public static function uncast($value)
    {
        return static::cast($value)->format(static::dateFormat());
    }
}
