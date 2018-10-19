<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\DateTimeCaster;
use Illuminate\Support\Carbon;

class DateTimeCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\DateTimeCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast_carbon_date()
    {
        $actual = $this->caster->cast(
            $date = Carbon::create(2019, 1, 1, 0, 0, 0)
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertEquals($date, $actual);
    }

    /** @test */
    public function it_can_cast_datetime_class()
    {
        $actual = $this->caster->cast(
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 00:00:00')
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertEquals($date, $actual);
    }

    /** @test */
    public function it_can_cast_timestamp()
    {
        $actual = $this->caster->cast($timestamp = 1514764800); // '2019-01-01 00:00:00'

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertSame($timestamp, $actual->getTimestamp());
    }

    /** @test */
    public function it_can_cast_standard_string_date()
    {
        $actual = $this->caster->cast($date = '2019-01-01');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertSame($date, $actual->toDateString());
    }

    /** @test */
    public function it_can_cast_from_default_datetime_format()
    {
        $actual = $this->caster->cast($date = '2019-01-01 00:00:00');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertSame($date, $actual->toDateTimeString());
    }

    /** @test */
    public function it_can_uncast()
    {
        $actual = $this->caster->uncast(
            $date = Carbon::create(2019, 1, 1, 0, 0, 0)
        );

        static::assertSame('2019-01-01 00:00:00', $actual);
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
        return new DateTimeCaster;
    }
}
