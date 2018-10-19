<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\CollectionCaster;
use Illuminate\Support\Collection;

class CollectionCasterTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Arcanedev\LaravelCastable\Contracts\Caster::class,
            \Arcanedev\LaravelCastable\Casts\CollectionCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        $actual = $this->caster->cast('["foo","bar","baz"]');

        static::assertInstanceOf(Collection::class, $actual);
        static::assertEquals(['foo', 'bar', 'baz'], $actual->toArray());

        $actual = $this->caster->cast('{"foo":"bar","baz":"qux"}');

        static::assertInstanceOf(Collection::class, $actual);
        static::assertEquals(['foo' => 'bar', 'baz' => 'qux'], $actual->toArray());
    }

    /** @test */
    public function it_can_uncast()
    {
        $actual = $this->caster->uncast(new Collection(['foo', 'bar', 'baz']));

        static::assertJson($actual);
        static::assertSame('["foo","bar","baz"]', $actual);

        $actual = $this->caster->uncast(new Collection(['foo' => 'bar', 'baz' => 'qux']));

        static::assertJson($actual);
        static::assertSame('{"foo":"bar","baz":"qux"}', $actual);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the caster.
     *
     * @return \Arcanedev\LaravelCastable\Casts\CollectionCaster
     */
    protected function caster()
    {
        return new CollectionCaster;
    }
}

