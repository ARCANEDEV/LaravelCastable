<?php namespace Arcanedev\LaravelCastable\Tests\Database\Eloquent;

use Arcanedev\LaravelCastable\Database\Eloquent\MultipleAttributesCaster;
use Arcanedev\LaravelCastable\Database\Eloquent\SingleAttributeCaster;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Arcanedev\LaravelCastable\Database\Eloquent\Model;

class ModelTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp()
    {
        parent::setUp();

        $this->createTable('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->json('properties');
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->dropTable('settings');

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_cast_on_create()
    {
        $setting = static::createSetting();

        static::assertInstanceOf(SettingProperties::class, $setting->properties);

        static::assertTrue($setting->properties->has_zonda);
        static::assertTrue($setting->properties['has_zonda']);

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->properties->acquired_at);
        static::assertSame('2011-06-09 12:00:00', $setting->properties->acquired_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_cast_on_refresh()
    {
        $setting = static::createSetting()->refresh();

        static::assertInstanceOf(SettingProperties::class, $setting->properties);

        static::assertTrue($setting->properties->has_zonda);
        static::assertTrue($setting->properties['has_zonda']);

        static::assertInstanceOf(\Illuminate\Support\Carbon::class, $setting->properties->acquired_at);
        static::assertSame('2011-06-09 12:00:00', $setting->properties->acquired_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_cast_to_array_and_json()
    {
        $setting  = static::createSetting();
        $expected = [
            'id'         => $setting->id,
            'properties' => [
                'has_zonda'   => 1,
                'acquired_at' => [
                    'date'     => '2011-06-09 12:00:00',
                    'timezone' => 'UTC',
                ]
            ]
        ];

        static::assertEquals($expected, $setting->toArray());
        static::assertJsonStringEqualsJsonString(json_encode($expected), $setting->toJson());
    }

//    /** @test */
//    public function it_can_get_dirty_attributes_on_casted()
//    {
//        //
//    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Create a setting model.
     *
     * @return Setting|mixed
     */
    private static function createSetting()
    {
        return Setting::query()->create([
            'properties' => [
                'has_zonda'   => true,
                'acquired_at' => [
                    'date'     => '2011-06-09 12:00:00',
                    'timezone' => 'UTC'
                ],
            ],
        ]);
    }
}

/**
 * @property  int                id
 * @property  SettingProperties  properties
 */
class Setting extends Model
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $fillable = ['properties'];

    protected $casts = [
        'id'         => 'integer',
        'properties' => SettingProperties::class
    ];

    public $timestamps = false;
}

/**
 * @property  bool                        has_zonda
 * @property  \Illuminate\Support\Carbon  acquired_at
 */
class SettingProperties extends MultipleAttributesCaster
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $casts  = [
        'has_zonda'   => 'boolean',
        'acquired_at' => CarbonDateProperty::class,
    ];
}

class CarbonDateProperty extends SingleAttributeCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function cast($value)
    {
        return new \Illuminate\Support\Carbon(
            $value['date'],
            $value['timezone']
        );
    }

    /**
     * @param  \Illuminate\Support\Carbon  $value
     *
     * @return array
     */
    protected function uncast($value)
    {
        return [
            'date'     => $value->format('Y-m-d H:i:s'),
            'timezone' => $value->timezoneName,
        ];
    }
}
