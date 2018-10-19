<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

abstract class AbstractCaster implements Caster
{
    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function uncast($value)
    {
        return $this->cast($value);
    }
}
