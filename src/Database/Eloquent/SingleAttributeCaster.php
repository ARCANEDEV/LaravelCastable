<?php namespace Arcanedev\LaravelCastable\Database\Eloquent;

use Arcanedev\LaravelCastable\Contracts\Castable;

abstract class SingleAttributeCaster implements Castable
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  mixed */
    protected $casted = null;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    public function __construct($value)
    {
        $this->casted = $this->cast($value);
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

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    abstract protected function cast($value);

    abstract protected function uncast($value);
}
