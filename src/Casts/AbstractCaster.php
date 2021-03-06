<?php namespace Arcanedev\LaravelCastable\Casts;

use Arcanedev\LaravelCastable\Contracts\Caster;

abstract class AbstractCaster implements Caster
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * @var string|null
     */
    protected $format;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function uncast($value)
    {
        return $this->cast($value);
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }
}
