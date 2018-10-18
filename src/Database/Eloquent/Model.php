<?php namespace Arcanedev\LaravelCastable\Database\Eloquent;

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

        return $this->isCustomObjectCastable($key)
            ? $this->getAttributeFromArray($key)
            : parent::castAttribute($key, $value);
    }

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
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param  string $key
     * @param  mixed  $current
     * @return bool
     */
    protected function originalIsEquivalent($key, $current)
    {

        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $original = $this->getOriginal($key);

        if ($current === $original) {
            return true;
        }

        if (is_null($current)) {
            return false;
        }

        if ($this->isDateAttribute($key)) {
            return $this->fromDateTime($current) ===
                $this->fromDateTime($original);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $current) === $this->castAttribute($key, $original);
        }

        return is_numeric($current) && is_numeric($original)
            && strcmp((string) $current, (string) $original) === 0;
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
