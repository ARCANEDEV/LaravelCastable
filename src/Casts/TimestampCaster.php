<?php namespace Arcanedev\LaravelCastable\Casts;

class TimestampCaster extends DateTimeCaster
{
    /**
     * @param  mixed  $value
     *
     * @return int
     */
    public function cast($value)
    {
        return static::asDateTime($value)->getTimestamp();
    }

    /**
     * @param  int  $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return static::asDateTime($value)->format(static::dateFormat());
    }
}
