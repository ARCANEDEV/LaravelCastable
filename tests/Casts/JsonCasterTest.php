<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\JsonCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;

class JsonCasterTest extends TestCase
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
            JsonCaster::cast('{"foo":"bar"}')
        );
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertEquals(
            '{"foo":"bar"}',
            JsonCaster::uncast(['foo' => 'bar'])
        );
    }
}
