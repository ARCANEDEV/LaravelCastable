<?php

use Arcanedev\LaravelCastable\Contracts\CasterManager;

if ( ! function_exists('caster')) {
    /**
     * Get the caster manager instance.
     *
     * @return CasterManager
     */
    function caster() {
        return app(CasterManager::class);
    }
}

if ( ! function_exists('cast')) {
    /**
     * Cast the value based on the given type.
     *
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    function cast($type, $value) {
        return caster()->cast($type, $value);
    }
}

if ( ! function_exists('uncast')) {
    /**
     * Uncast the value based on the given type.
     *
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return mixed
     */
    function uncast($type, $value) {
        return caster()->uncast($type, $value);
    }
}
