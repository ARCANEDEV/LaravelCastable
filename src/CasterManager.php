<?php namespace Arcanedev\LaravelCastable;

use Arcanedev\LaravelCastable\Contracts\Caster;
use Arcanedev\LaravelCastable\Contracts\CasterManager as CasterManagerContract;
use Illuminate\Config\Repository;

class CasterManager implements CasterManagerContract
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Illuminate\Contracts\Config\Repository  */
    protected $config;

    protected $casters = [];

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
        if (str_contains($type, ':'))
            list($type, $format) = explode(':', $type, 2);

        return $this->hasCaster($type)
            ? $this->getCaster($type)->setFormat($format ?? null)->cast($value)
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
        if (str_contains($type, ':'))
            list($type, $format) = explode(':', $type, 2);

        return $this->hasCaster($type)
            ? $this->getCaster($type)->setFormat($format ?? null)->uncast($value)
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
        return isset($this->casters[$type])
            || $this->config->has("laravel-castable.casters.$type");
    }

    /**
     * Get the caster.
     *
     * @param  string  $key
     *
     * @return \Arcanedev\LaravelCastable\Contracts\Caster
     */
    public function getCaster($key)
    {
        if (isset($this->casters[$key]))
            return $this->casters[$key];

        return tap($this->buildCaster($key), function (Caster $caster) use ($key) {
            $this->registerCaster($key, $caster);
        });
    }

    /**
     * @param  string  $key
     *
     * @return \Arcanedev\LaravelCastable\Contracts\Caster
     */
    private function buildCaster($key)
    {
        return app()->make($this->config->get("laravel-castable.casters.$key"));
    }

    /**
     * Register the caster.
     *
     * @param  string                                       $key
     * @param  \Arcanedev\LaravelCastable\Contracts\Caster  $caster
     *
     * @return $this
     */
    protected function registerCaster($key, Caster $caster)
    {
        $this->casters[$key] = $caster;

        return $this;
    }
}
