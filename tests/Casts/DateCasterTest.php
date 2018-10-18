<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\DateCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Support\Carbon;

class DateCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast_carbon_date()
    {
        $casted = DateCaster::cast(
            $date = Carbon::create(2019, 1, 1, 12, 0, 0)
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertEquals($casted, $date);
    }

    /** @test */
    public function it_can_cast_datetime_class()
    {
        $casted = DateCaster::cast(
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:00:00')
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertEquals($casted->toDateTimeString(), '2019-01-01 00:00:00');
    }

    /** @test */
    public function it_can_cast_timestamp()
    {
        $casted = DateCaster::cast($timestamp = 1546344000); // '2019-01-01 12:00:00'

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->getTimestamp(), 1546300800);
    }

    /** @test */
    public function it_can_cast_standard_string_date()
    {
        $casted = DateCaster::cast($date = '2019-01-01');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->toDateString(), $date);
    }

    /** @test */
    public function it_can_cast_from_default_datetime_format()
    {
        $casted = DateCaster::cast($date = '2019-01-01 12:00:00');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->toDateTimeString(), '2019-01-01 00:00:00');
    }

    /** @test */
    public function it_can_uncast()
    {
        $uncasted = DateCaster::uncast(
            $date = Carbon::create(2019, 1, 1, 12, 0, 0)
        );

        static::assertSame('2019-01-01 00:00:00', $uncasted);
    }
}
