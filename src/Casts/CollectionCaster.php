<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;
use Illuminate\Support\Collection;

class CollectionCaster implements Caster
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
    public static function cast($value)
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
    public static function uncast($value)
    {
        return $value->toJson();
    }
}
