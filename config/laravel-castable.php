<?php

return [

    /* -----------------------------------------------------------------
     |  Casters
     | -----------------------------------------------------------------
     */

    'casters' => [

        'bool'            => Arcanedev\LaravelCastableModels\Casts\BooleanCaster::class,
        'boolean'         => Arcanedev\LaravelCastableModels\Casts\BooleanCaster::class,
        'int'             => Arcanedev\LaravelCastableModels\Casts\IntegerCaster::class,
        'integer'         => Arcanedev\LaravelCastableModels\Casts\IntegerCaster::class,
        'real'            => Arcanedev\LaravelCastableModels\Casts\FloatCaster::class,
        'float'           => Arcanedev\LaravelCastableModels\Casts\FloatCaster::class,
        'double'          => Arcanedev\LaravelCastableModels\Casts\FloatCaster::class,
        'string'          => Arcanedev\LaravelCastableModels\Casts\StringCaster::class,
        'object'          => Arcanedev\LaravelCastableModels\Casts\ObjectCaster::class,
        'array'           => Arcanedev\LaravelCastableModels\Casts\ArrayCaster::class,
        'json'            => Arcanedev\LaravelCastableModels\Casts\JsonCaster::class,
        'collection'      => Arcanedev\LaravelCastableModels\Casts\CollectionCaster::class,
        'date'            => Arcanedev\LaravelCastableModels\Casts\DateCaster::class,
        'datetime'        => Arcanedev\LaravelCastableModels\Casts\DateTimeCaster::class,
        'custom_datetime' => Arcanedev\LaravelCastableModels\Casts\CustomDateTimeCaster::class,
        'timestamp'       => Arcanedev\LaravelCastableModels\Casts\TimestampCaster::class,

    ],

];
