<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\StringCaster;

class StringCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\StringCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        static::assertSame('1', $this->caster->cast(1));
        static::assertSame('0', $this->caster->cast(0));

        static::assertSame('1', $this->caster->cast(true));
        static::assertSame('', $this->caster->cast(false));
        static::assertSame('', $this->caster->cast(null));
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertSame('1', $this->caster->cast('1'));
        static::assertSame('0', $this->caster->cast('0'));

        static::assertSame('true', $this->caster->cast('true'));
        static::assertSame('false', $this->caster->cast('false'));

        static::assertSame('', $this->caster->cast(null));
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
        return new StringCaster;
    }
}
