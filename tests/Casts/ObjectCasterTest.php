<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\ObjectCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;

class ObjectCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast()
    {
        static::assertNull(ObjectCaster::cast("['foo','bar']"));

        $casted = ObjectCaster::cast('{"foo":"bar","baz":"qux"}');

        static::assertInternalType('object', $casted);
        static::assertSame('bar', $casted->foo);
        static::assertSame('qux', $casted->baz);
    }

    /** @test */
    public function it_can_uncast()
    {
        $obj = (object) ['foo' => 'bar', 'baz' => 'qux'];

        static::assertSame('{"foo":"bar","baz":"qux"}', ObjectCaster::uncast($obj));
    }
}
