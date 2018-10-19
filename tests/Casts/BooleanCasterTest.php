<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\BooleanCaster;

class BooleanCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\BooleanCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        static::assertSame(true, $this->caster->cast(1));
        static::assertSame(true, $this->caster->cast('1'));

        static::assertSame(false, $this->caster->cast(0));
        static::assertSame(false, $this->caster->cast('0'));
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertSame(1, $this->caster->uncast(1));
        static::assertSame(1, $this->caster->uncast('1'));

        static::assertSame(0, $this->caster->uncast(0));
        static::assertSame(0, $this->caster->uncast('0'));
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the caster.
     *
     * @return \Arcanedev\LaravelCastable\Casts\BooleanCaster
     */
    protected function caster()
    {
        return new BooleanCaster;
    }
}
