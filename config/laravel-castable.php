<?php

return [

    /* -----------------------------------------------------------------
     |  Casters
     | -----------------------------------------------------------------
     */

    'casters' => [

        'bool'            => Arcanedev\LaravelCastable\Casts\BooleanCaster::class,
        'boolean'         => Arcanedev\LaravelCastable\Casts\BooleanCaster::class,
        'int'             => Arcanedev\LaravelCastable\Casts\IntegerCaster::class,
        'integer'         => Arcanedev\LaravelCastable\Casts\IntegerCaster::class,
        'real'            => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'float'           => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'double'          => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'string'          => Arcanedev\LaravelCastable\Casts\StringCaster::class,
        'object'          => Arcanedev\LaravelCastable\Casts\ObjectCaster::class,
        'array'           => Arcanedev\LaravelCastable\Casts\ArrayCaster::class,
        'json'            => Arcanedev\LaravelCastable\Casts\JsonCaster::class,
        'collection'      => Arcanedev\LaravelCastable\Casts\CollectionCaster::class,
        'date'            => Arcanedev\LaravelCastable\Casts\DateCaster::class,
        'datetime'        => Arcanedev\LaravelCastable\Casts\DateTimeCaster::class,
        'custom_datetime' => Arcanedev\LaravelCastable\Casts\CustomDateTimeCaster::class,
        'timestamp'       => Arcanedev\LaravelCastable\Casts\TimestampCaster::class,

    ],

];
