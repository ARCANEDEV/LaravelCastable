<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\DecimalCaster;

class DecimalCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\DecimalCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        static::assertSame('12', $this->caster->cast(12));
        static::assertSame('12', $this->caster->cast('12'));
        static::assertSame('1234', $this->caster->cast(1234));
        static::assertSame('1234', $this->caster->cast('1234'));

        $this->caster->setFormat(2);

        static::assertSame('12.00', $this->caster->cast(12));
        static::assertSame('12.00', $this->caster->cast('12'));
        static::assertSame('1234.00', $this->caster->cast(1234));
        static::assertSame('1234.00', $this->caster->cast('1234'));
        static::assertSame('1234.57', $this->caster->cast('1234.5678'));

        $this->caster->setFormat(4);

        static::assertSame('12.0000', $this->caster->cast(12));
        static::assertSame('12.0000', $this->caster->cast('12'));
        static::assertSame('1234.0000', $this->caster->cast(1234));
        static::assertSame('1234.0000', $this->caster->cast('1234'));
        static::assertSame('1234.5678', $this->caster->cast('1234.5678'));
    }

    /** @test */
    public function it_can_cast_via_helper()
    {
        static::assertSame('12', cast('decimal', 12));
        static::assertSame('12', cast('decimal', '12'));
        static::assertSame('1234', cast('decimal', 1234));
        static::assertSame('1234', cast('decimal', '1234'));

        static::assertSame('12.00', cast('decimal:2', 12));
        static::assertSame('12.00', cast('decimal:2', '12'));
        static::assertSame('1234.00', cast('decimal:2', 1234));
        static::assertSame('1234.00', cast('decimal:2', '1234'));
        static::assertSame('1234.57', cast('decimal:2', '1234.5678'));

        static::assertSame('12.0000', cast('decimal:4', 12));
        static::assertSame('12.0000', cast('decimal:4', '12'));
        static::assertSame('1234.0000', cast('decimal:4', 1234));
        static::assertSame('1234.0000', cast('decimal:4', '1234'));
        static::assertSame('1234.5678', cast('decimal:4', '1234.5678'));
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
        return new DecimalCaster;
    }
}
