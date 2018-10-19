<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\ObjectCaster;

class ObjectCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\ObjectCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        static::assertNull($this->caster->cast("['foo','bar']"));

        $actual = $this->caster->cast('{"foo":"bar","baz":"qux"}');

        static::assertInternalType('object', $actual);
        static::assertSame('bar', $actual->foo);
        static::assertSame('qux', $actual->baz);
    }

    /** @test */
    public function it_can_uncast()
    {
        $obj = (object) ['foo' => 'bar', 'baz' => 'qux'];

        static::assertSame('{"foo":"bar","baz":"qux"}', $this->caster->uncast($obj));
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the caster.
     *
     * @return \Arcanedev\LaravelCastable\Contracts\Caster
     */
    protected function caster()
    {
        return new ObjectCaster;
    }
}
