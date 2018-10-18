<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\ArrayCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;

class ArrayCasterTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_cast()
    {
        static::assertEquals(
            ['foo' => 'bar'],
            ArrayCaster::cast('{"foo":"bar"}')
        );
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertEquals(
            '{"foo":"bar"}',
            ArrayCaster::uncast(['foo' => 'bar'])
        );
    }
}
