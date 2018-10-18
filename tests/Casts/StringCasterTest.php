<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\StringCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;

class StringCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast()
    {
        static::assertSame('1', StringCaster::cast(1));
        static::assertSame('0', StringCaster::cast(0));

        static::assertSame('1', StringCaster::cast(true));
        static::assertSame('', StringCaster::cast(false));
        static::assertSame('', StringCaster::cast(null));
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertSame('1', StringCaster::cast('1'));
        static::assertSame('0', StringCaster::cast('0'));

        static::assertSame('true', StringCaster::cast('true'));
        static::assertSame('false', StringCaster::cast('false'));

        static::assertSame('', StringCaster::cast(null));
    }
}
