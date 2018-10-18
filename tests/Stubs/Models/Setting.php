<?php namespace Arcanedev\LaravelCastable\Tests\Stubs\Models;

use Arcanedev\LaravelCastable\Database\Eloquent\Model;
use Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts\Setting\Properties;

/**
 * Class     Setting
 *
 * @package  Arcanedev\LaravelCastableModels\Tests\Stubs\Models
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 *
 * @property  \Arcanedev\LaravelCastable\Tests\Stubs\Models\Casts\Setting\Properties  properties
 */
class Setting extends Model
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $fillable = ['properties'];

    protected $casts = [
        'properties' => Properties::class
    ];
}
