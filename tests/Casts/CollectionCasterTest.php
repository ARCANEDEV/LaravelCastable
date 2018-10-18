<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\CollectionCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Support\Collection;

class CollectionCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast()
    {
        $casted = CollectionCaster::cast('["foo","bar","baz"]');

        static::assertInstanceOf(Collection::class, $casted);
        static::assertEquals(['foo', 'bar', 'baz'], $casted->toArray());

        $casted = CollectionCaster::cast('{"foo":"bar","baz":"qux"}');

        static::assertInstanceOf(Collection::class, $casted);
        static::assertEquals(['foo' => 'bar', 'baz' => 'qux'], $casted->toArray());
    }

    /** @test */
    public function it_can_uncast()
    {
        $uncasted = CollectionCaster::uncast(new Collection(['foo', 'bar', 'baz']));

        static::assertJson($uncasted);
        static::assertSame('["foo","bar","baz"]', $uncasted);

        $uncasted = CollectionCaster::uncast(new Collection(['foo' => 'bar', 'baz' => 'qux']));

        static::assertJson($uncasted);
        static::assertSame('{"foo":"bar","baz":"qux"}', $uncasted);
    }
}
