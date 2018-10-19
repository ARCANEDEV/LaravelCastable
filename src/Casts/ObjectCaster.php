<?php namespace Arcanedev\LaravelCastable\Casts;

class ObjectCaster extends AbstractCaster
{
    /**
     * @param  string  $value
     *
     * @return object
     */
    public function cast($value)
    {
        return json_decode($value);
    }

    /**
     * @param  object $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return json_encode($value);
    }
}
