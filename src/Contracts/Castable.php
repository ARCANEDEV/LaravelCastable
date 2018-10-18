<?php namespace Arcanedev\LaravelCastable\Contracts;

interface Castable
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @return mixed
     */
    public function cast($value);

    /**
     * @return mixed
     */
    public function uncast($value);

    /**
     * Get the original value.
     *
     * @return mixed
     */
    public function getOriginal();

    /**
     * Get the casted value.
     *
     * @return mixed
     */
    public function getCasted();
}
