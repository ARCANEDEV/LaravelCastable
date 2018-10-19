<?php namespace Arcanedev\LaravelCastable\Casts;

use Illuminate\Support\Collection;

class CollectionCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  string  $value
     *
     * @return \Illuminate\Support\Collection
     */
    public function cast($value)
    {
        return new Collection(
            json_decode($value, true)
        );
    }

    /**
     * @param  \Illuminate\Support\Collection  $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return $value->toJson();
    }
}
