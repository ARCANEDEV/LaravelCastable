<?php namespace Arcanedev\LaravelCastable\Tests\Laravel\Eloquent;

use Arcanedev\LaravelCastable\Database\Eloquent\Model as Eloquent;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class LaravelCastsDatabaseStringTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createTable('casting_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('array_attributes');
            $table->string('json_attributes');
            $table->string('object_attributes');
            $table->timestamps();
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->dropTable('casting_table');

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    public function testSavingCastedAttributesToDatabase()
    {
        /** @var TableForCasting $model */
        $model = TableForCasting::query()->create([
            'array_attributes' => ['key1' => 'value1'],
            'json_attributes' => ['json_key' => 'json_value'],
            'object_attributes' => ['json_key' => 'json_value'],
        ]);

        static::assertSame('{"key1":"value1"}', $model->getOriginal('array_attributes'));
        static::assertSame(['key1' => 'value1'], $model->getAttribute('array_attributes'));
        static::assertSame('{"json_key":"json_value"}', $model->getOriginal('json_attributes'));
        static::assertSame(['json_key' => 'json_value'], $model->getAttribute('json_attributes'));
        static::assertSame('{"json_key":"json_value"}', $model->getOriginal('object_attributes'));

        $stdClass = new stdClass;
        $stdClass->json_key = 'json_value';

        static::assertEquals($stdClass, $model->getAttribute('object_attributes'));
    }

    public function testSavingCastedEmptyAttributesToDatabase()
    {
        /** @var TableForCasting $model */
        $model = TableForCasting::query()->create([
            'array_attributes' => [],
            'json_attributes' => [],
            'object_attributes' => [],
        ]);

        static::assertSame('[]', $model->getOriginal('array_attributes'));
        static::assertSame([], $model->getAttribute('array_attributes'));
        static::assertSame('[]', $model->getOriginal('json_attributes'));
        static::assertSame([], $model->getAttribute('json_attributes'));
        static::assertSame('[]', $model->getOriginal('object_attributes'));
        static::assertSame([], $model->getAttribute('object_attributes'));
    }
}

/**
 * Eloquent Models...
 */
class TableForCasting extends Eloquent
{
    /**
     * @var string
     */
    protected $table = 'casting_table';
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var array
     */
    protected $casts = [
        'array_attributes' => 'array',
        'json_attributes' => 'json',
        'object_attributes' => 'object',
    ];
}
