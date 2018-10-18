<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\DateTimeCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Support\Carbon;

class DateTimeCasterTest extends TestCase
{
    /** @test */
    public function it_can_cast_carbon_date()
    {
        $casted = DateTimeCaster::cast(
            $date = Carbon::create(2019, 1, 1, 0, 0, 0)
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertEquals($casted, $date);
    }

    /** @test */
    public function it_can_cast_datetime_class()
    {
        $casted = DateTimeCaster::cast(
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 00:00:00')
        );

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertEquals($casted, $date);
    }

    /** @test */
    public function it_can_cast_timestamp()
    {
        $casted = DateTimeCaster::cast($timestamp = 1514764800); // '2019-01-01 00:00:00'

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->getTimestamp(), $timestamp);
    }

    /** @test */
    public function it_can_cast_standard_string_date()
    {
        $casted = DateTimeCaster::cast($date = '2019-01-01');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->toDateString(), $date);
    }

    /** @test */
    public function it_can_cast_from_default_datetime_format()
    {
        $casted = DateTimeCaster::cast($date = '2019-01-01 00:00:00');

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $casted);
        static::assertSame($casted->toDateTimeString(), $date);
    }

    /** @test */
    public function it_can_uncast()
    {
        $uncasted = DateTimeCaster::uncast(
            $date = Carbon::create(2019, 1, 1, 0, 0, 0)
        );

        static::assertSame('2019-01-01 00:00:00', $uncasted);
    }
}
