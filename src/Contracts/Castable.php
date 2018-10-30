<?php namespace Arcanedev\LaravelCastable\Contracts;

interface Castable
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the casted value.
     *
     * @return mixed
     */
    public function getCasted();

    /**
     * Get the uncasted value.
     *
     * @return mixed
     */
    public function getUncasted();
}
