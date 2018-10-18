<?php namespace Arcanedev\LaravelCastable\Tests\Database\Eloquent;

use Arcanedev\LaravelCastable\Tests\{
    Stubs\Models\Casts\Setting\Properties, Stubs\Models\Setting, TestCase
};

class ModelTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_cast_while_instantiated()
    {
        $setting = new Setting([
            'properties' => '{"has_zonda": 1, "acquired_at": {"date": "2011-06-09 12:00:00.000000", "timezone": "UTC"}}',
        ]);

        static::assertInstanceOf(Properties::class, $setting->properties);

        static::assertTrue($setting->properties->has_zonda);
        static::assertTrue($setting->properties['has_zonda']);

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->properties->acquired_at);
        static::assertSame('2011-06-09 12:00:00', $setting->properties->acquired_at->format('Y-m-d H:i:s'));
    }

//    /** @test */
//    public function it_can_cast_original_attributes()
//    {
//        $attributes = [
//            'properties' => '{"has_zonda": 1, "acquired_at": {"date": "2011-06-09 12:00:00.000000", "timezone": "UTC"}}',
//        ];
//
//        $class = new class($attributes) extends Setting {
//            protected $original = [
//                'properties' => '{"has_zonda": 1, "acquired_at": {"date": "2011-06-09 12:00:00.000000", "timezone": "UTC"}}',
//            ];
//        };
//
//        dd(
//            $class->getOriginal('properties'),
//            $class->getDirty()
//        );
//    }

    /** @test */
    public function it_can_cast_to_array_and_json()
    {
        $settings   = new Setting([
            'properties' => '{"has_zonda": 1, "acquired_at": {"date": "2011-06-09 12:00:00.000000", "timezone": "UTC"}}',
        ]);
        $properties = [
            'properties' => [
                'has_zonda'   => 1,
                'acquired_at' => [
                    'date'     => '2011-06-09 12:00:00.000000',
                    'timezone' => 'UTC',
                ]
            ]
        ];

        $this->assertEquals($properties, $settings->toArray());
        $this->assertJsonStringEqualsJsonString(json_encode($properties), $settings->toJson());
    }
}
