<?php namespace Arcanedev\LaravelCastable\Database\Eloquent\Concerns;

use Arcanedev\LaravelCastable\Contracts\Castable;

/**
 * Trait     WithCastableAttributes
 *
 * @package  Arcanedev\LaravelCastableModels\Database\Eloquent\Concerns
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @property  array  casts
 */
trait WithCastableAttributes
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  string  $key
     *
     * @return bool
     */
    protected function isCustomObjectCastable($key)
    {
        return array_key_exists($key, $this->casts)
            && is_a($this->casts[$key], Castable::class, true);
    }

    //----------------------------------------------------------

    /**
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return mixed
     */
    protected function castCustomAttribute($key, $value)
    {
        $type = $this->casts[$key];

        return new $type($value);
    }
}
