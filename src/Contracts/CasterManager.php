<?php namespace Arcanedev\LaravelCastable\Contracts;

interface CasterManager
{
    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public function cast($type, $value);

    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public function uncast($type, $value);

    /**
     * Check if has caster.
     *
     * @param  string  $type
     *
     * @return bool
     */
    public function hasCaster($type);

    /**
     * Get the caster.
     *
     * @param  string  $type
     *
     * @return string
     */
    public function getCaster($type);
}
