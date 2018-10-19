<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\DateCaster;
use DateTime;
use Illuminate\Support\Carbon;

class DateCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\DateCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast_carbon_date()
    {
        $actual = $this->caster->cast(
            $date = Carbon::create(2019, 1, 1, 12, 0, 0)
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertEquals($date, $actual);
    }

    /** @test */
    public function it_can_cast_datetime_class()
    {
        $actual = $this->caster->cast(
            $date = DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:00:00')
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertEquals('2019-01-01 00:00:00', $actual->toDateTimeString());
    }

    /** @test */
    public function it_can_cast_timestamp()
    {
        $actual = $this->caster->cast($timestamp = 1546344000); // '2019-01-01 12:00:00'

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertSame(1546300800, $actual->getTimestamp());
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
        $actual = $this->caster->cast($date = '2019-01-01 12:00:00');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $actual);
        static::assertSame('2019-01-01 00:00:00', $actual->toDateTimeString());
    }

    /** @test */
    public function it_can_uncast()
    {
        $actual = $this->caster->uncast(
            $date = Carbon::create(2019, 1, 1, 12, 0, 0)
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
     * @return \Arcanedev\LaravelCastable\Casts\DateCaster
     */
    protected function caster()
    {
        return new DateCaster;
    }
}
