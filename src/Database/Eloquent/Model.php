<?php namespace Arcanedev\LaravelCastable\Database\Eloquent;

use Arcanedev\LaravelCastable\Contracts\Castable;
use Arcanedev\LaravelCastable\Database\Eloquent\Concerns\WithCastableAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as IlluminateModel;

abstract class Model extends IlluminateModel
{
    /* -----------------------------------------------------------------
     |  Traits
     | -----------------------------------------------------------------
     */

    use WithCastableAttributes;

    /* -----------------------------------------------------------------
     |  Overridden Methods
     | -----------------------------------------------------------------
     */

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ( ! $this->isCustomObjectCastable($key))
            return parent::setAttribute($key, $value);

        $this->attributes[$key] = $this->castCustomAttribute($key, $value);

        return $this;
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return mixed
     */
    public function castAttribute($key, $value)
    {
        if (is_null($value))
            return $value;

        if ($this->isCustomObjectCastable($key)) {
            $value = $this->getAttributeFromArray($key);

            if ( ! $value instanceof Castable)
                return $this->castCustomAttribute($key, $value);
        }

        return cast($this->getCasts()[$key], $value);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, parent::attributesToArray());
    }
}
