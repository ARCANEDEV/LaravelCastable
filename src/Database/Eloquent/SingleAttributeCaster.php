<?php namespace Arcanedev\LaravelCastable\Database\Eloquent;

use Arcanedev\LaravelCastable\Contracts\Castable;

abstract class SingleAttributeCaster implements Castable
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $casted;

    protected $original;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    public function __construct($value)
    {
        $this->original = $value;
        $this->casted   = $this->cast($value);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the original value.
     *
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Get the casted value.
     *
     * @return mixed
     */
    public function getCasted()
    {
        return $this->casted;
    }

    public function uncast($value)
    {
        return $this->getOriginal();
    }
}
