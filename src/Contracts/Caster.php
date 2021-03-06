<?php namespace Arcanedev\LaravelCastable\Contracts;

interface Caster
{
    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function cast($value);

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function uncast($value);

    /**
     * @param  string|null  $format
     *
     * @return self
     */
    public function setFormat($format);
}
