<?php namespace Arcanedev\LaravelCastable;

use Arcanedev\LaravelCastable\Contracts\CasterManager;
use Illuminate\Config\Repository;

class Caster implements CasterManager
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Illuminate\Contracts\Config\Repository  */
    private $config;

    /* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public function cast($type, $value)
    {
        return $this->hasCaster($type)
            ? ($this->getCaster($type))::cast($value)
            : $value;
    }

    /**
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    public function uncast($type, $value)
    {
        return $this->hasCaster($type)
            ? ($this->getCaster($type))::uncast($value)
            : $value;
    }

    /**
     * Check if has caster.
     *
     * @param  string  $type
     *
     * @return bool
     */
    public function hasCaster($type)
    {
        return $this->config->has("laravel-castable.casters.$type");
    }

    /**
     * Get the caster.
     *
     * @param  string  $type
     *
     * @return string
     */
    public function getCaster($type)
    {
        return $this->config->get("laravel-castable.casters.$type");
    }
}
