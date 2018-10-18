<?php namespace Arcanedev\LaravelCastable\Casts;

class TimestampCaster extends DateTimeCaster
{
    /**
     * @param  mixed  $value
     *
     * @return int
     */
    public static function cast($value)
    {
        return static::asDateTime($value)->getTimestamp();
    }

    /**
     * @param  int  $value
     *
     * @return string
     */
    public static function uncast($value)
    {
        return static::asDateTime($value)->toDateTimeString();
    }
}
