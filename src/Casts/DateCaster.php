<?php namespace Arcanedev\LaravelCastable\Casts;

class DateCaster extends DateTimeCaster
{
    /**
     * @param  mixed  $value
     *
     * @return \Illuminate\Support\Carbon
     */
    public function cast($value)
    {
        return parent::cast($value)->startOfDay();
    }

    /**
     * @param \Illuminate\Support\Carbon $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return $this->cast($value)->format(static::dateFormat());
    }
}
