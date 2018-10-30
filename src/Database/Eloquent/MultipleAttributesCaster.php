<?php namespace Arcanedev\LaravelCastable\Database\Eloquent;

use Arcanedev\LaravelCastable\{
    Contracts\Castable, Database\Eloquent\Concerns\WithCastableAttributes
};
use ArrayAccess;
use Illuminate\Contracts\Support\{
    Arrayable, Jsonable
};
use JsonSerializable;

abstract class MultipleAttributesCaster implements Castable, Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    /* -----------------------------------------------------------------
     |  Traits
     | -----------------------------------------------------------------
     */

    use WithCastableAttributes;

    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $casts    = [];

    protected $casted   = [];

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    /**
     * Create a new fluent container instance.
     *
     * @param  array|string  $values
     *
     * @return void
     */
    public function __construct($values)
    {
        if (is_string($values))
            $values = json_decode($values, true);

        $this->casted = $this->cast($values);
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the casted value.
     *
     * @return mixed
     */
    public function getCasted()
    {
        return $this->casted;
    }

    /**
     * Get the uncasted value.
     *
     * @return mixed
     */
    public function getUncasted()
    {
        return $this->uncast($this->casted);
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getUncasted();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the Fluent instance to JSON.
     *
     * @param  int  $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->casted[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->casted[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->casted[$offset]);
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string  $key
     *
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function cast($values)
    {
        $results = [];

        foreach ($values as $key => $value) {
            $results[$key] = $this->castAttribute($key, $value);
        }

        return $results;
    }

    protected function uncast($values)
    {
        $results = [];

        foreach ($values as $key => $value) {
            $results[$key] = $this->uncastAttribute($key, $value);
        }

        return $results;
    }

    protected function get($key)
    {
        $value = $this->casted[$key];

        return $value instanceof Castable
            ? $value->getCasted()
            : $value;
    }

    protected function castAttribute($key, $value)
    {
        if (is_null($value))
            return $value;

        if ($this->isCustomObjectCastable($key))
            return $this->castCustomAttribute($key, $value);

        return cast($this->casts[$key] ?? 'null', $value);
    }

    protected function uncastAttribute($key, $value)
    {
        if ($this->isCustomObjectCastable($key)) {
            /** @var  \Arcanedev\LaravelCastable\Contracts\Castable  $type */
            $type = $this->getCasted()[$key];

            return $type->getUncasted();
        }

        return uncast($this->casts[$key] ?? 'null', $value);
    }
}
