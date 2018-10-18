<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\BooleanCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;

class BooleanCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast()
    {
        static::assertSame(true, BooleanCaster::cast(1));
        static::assertSame(true, BooleanCaster::cast('1'));

        static::assertSame(false, BooleanCaster::cast(0));
        static::assertSame(false, BooleanCaster::cast('0'));
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertSame(1, BooleanCaster::uncast(1));
        static::assertSame(1, BooleanCaster::uncast('1'));

        static::assertSame(0, BooleanCaster::uncast(0));
        static::assertSame(0, BooleanCaster::uncast('0'));
    }
}
