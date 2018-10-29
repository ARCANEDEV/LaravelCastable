<?php

return [

    /* -----------------------------------------------------------------
     |  Casters
     | -----------------------------------------------------------------
     */

    'casters' => [

        'array'           => Arcanedev\LaravelCastable\Casts\ArrayCaster::class,
        'bool'            => Arcanedev\LaravelCastable\Casts\BooleanCaster::class,
        'boolean'         => Arcanedev\LaravelCastable\Casts\BooleanCaster::class,
        'collection'      => Arcanedev\LaravelCastable\Casts\CollectionCaster::class,
        'custom_datetime' => Arcanedev\LaravelCastable\Casts\CustomDateTimeCaster::class,
        'date'            => Arcanedev\LaravelCastable\Casts\DateCaster::class,
        'datetime'        => Arcanedev\LaravelCastable\Casts\DateTimeCaster::class,
        'decimal'         => Arcanedev\LaravelCastable\Casts\DecimalCaster::class,
        'double'          => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'float'           => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'int'             => Arcanedev\LaravelCastable\Casts\IntegerCaster::class,
        'integer'         => Arcanedev\LaravelCastable\Casts\IntegerCaster::class,
        'json'            => Arcanedev\LaravelCastable\Casts\JsonCaster::class,
        'object'          => Arcanedev\LaravelCastable\Casts\ObjectCaster::class,
        'real'            => Arcanedev\LaravelCastable\Casts\FloatCaster::class,
        'string'          => Arcanedev\LaravelCastable\Casts\StringCaster::class,
        'timestamp'       => Arcanedev\LaravelCastable\Casts\TimestampCaster::class,

    ],

];
