<?php namespace Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts;

use Arcanedev\LaravelCastable\Contracts\Castable;
use Arcanedev\LaravelCastable\Database\Eloquent\SingleAttributeCaster;
use Illuminate\Support\Carbon as IlluminateCarbon;

/**
 * Class     CarbonDate
 *
 * @package  Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CarbonDate extends SingleAttributeCaster implements Castable
{
    public function cast($value)
    {
        return new IlluminateCarbon(
            $value['date'],
            $value['timezone']
        );
    }
}
