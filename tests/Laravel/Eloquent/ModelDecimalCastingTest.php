<?php namespace Arcanedev\LaravelCastable\Tests\Laravel\Eloquent;

use Arcanedev\LaravelCastable\Database\Eloquent\Model;
use Arcanedev\LaravelCastable\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;

class ModelDecimalCastingTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp()
    {
        parent::setUp();

        $this->createTable('test_model1', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('decimal_field_2', 8, 2)->nullable();
            $table->decimal('decimal_field_4', 8, 4)->nullable();
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->dropTable('test_model1');

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_decimals_are_castable()
    {
        $user = TestModel1::query()->create([
            'decimal_field_2' => '12',
            'decimal_field_4' => '1234',
        ]);

        static::assertEquals('12.00', $user->toArray()['decimal_field_2']);
        static::assertEquals('1234.0000', $user->toArray()['decimal_field_4']);

        $user->decimal_field_2 = 12;
        $user->decimal_field_4 = '1234';

        static::assertEquals('12.00', $user->toArray()['decimal_field_2']);
        static::assertEquals('1234.0000', $user->toArray()['decimal_field_4']);
        static::assertFalse($user->isDirty());

        $user->decimal_field_4 = '1234.1234';

        static::assertTrue($user->isDirty());
    }
}

class TestModel1 extends Model
{
    public $table = 'test_model1';

    public $timestamps = false;

    protected $guarded = ['id'];

    public $casts = [
        'decimal_field_2' => 'decimal:2',
        'decimal_field_4' => 'decimal:4',
    ];
}
