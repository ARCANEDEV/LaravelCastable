<?php namespace Arcanedev\LaravelCastable\Tests\Stubs\Models;

use Arcanedev\LaravelCastable\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'address_street',
        'address_zipcode',
        'address_city'
    ];
}
