<?php namespace Arcanedev\LaravelCastable\Casts;

class StringCaster extends AbstractCaster
{
    /**
     * @param  mixed  $value
     *
     * @return string
     */
    public function cast($value)
    {
        return (string) $value;
    }
}
