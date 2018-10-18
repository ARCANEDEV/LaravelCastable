<?php namespace Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts\Setting;

use Arcanedev\LaravelCastable\Database\Eloquent\MultipleAttributesCaster;
use Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts\CarbonDate;

/**
 * Class     Properties
 *
 * @package  Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @property  bool                        has_zonda
 * @property  \Illuminate\Support\Carbon  acquired_at
 */
class Properties extends MultipleAttributesCaster
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $casts  = [
        'has_zonda'   => 'boolean',
        'acquired_at' => CarbonDate::class,
    ];
}
