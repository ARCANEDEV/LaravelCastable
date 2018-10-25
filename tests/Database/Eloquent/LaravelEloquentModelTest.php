<?php namespace Arcanedev\LaravelCastable\Tests\Database\Eloquent;

use Arcanedev\LaravelCastable\Database\Eloquent\Model;
use Arcanedev\LaravelCastable\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Foo\Bar\EloquentModelNamespacedStub;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\{
    Connection, ConnectionResolverInterface, Eloquent\Builder, Eloquent\Collection, Eloquent\Relations\BelongsTo,
    Eloquent\Relations\Relation, Query\Grammars\Grammar, Query\Processors\Processor
};
use Illuminate\Support\{
    Carbon, Collection as BaseCollection, InteractsWithTime
};
use Mockery as m;
use ReflectionClass;
use stdClass;

class LaravelEloquentModel extends TestCase
{
    /* -----------------------------------------------------------------
     |  Traits
     | -----------------------------------------------------------------
     */

    use InteractsWithTime;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
        Carbon::setTestNow(null);
        Model::unsetEventDispatcher();
        Carbon::resetToStringFormat();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    public function testAttributeManipulation()
    {
        $model = new EloquentModelStub;
        $model->name = 'foo';

        static::assertEquals('foo', $model->name);
        static::assertTrue(isset($model->name));

        unset($model->name);

        static::assertFalse(isset($model->name));

        // test mutation
        $model->list_items = ['name' => 'taylor'];
        static::assertEquals(['name' => 'taylor'], $model->list_items);

        $attributes = $model->getAttributes();

        static::assertEquals(json_encode(['name' => 'taylor']), $attributes['list_items']);
    }

    public function testDirtyAttributes()
    {
        $model = new EloquentModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
        $model->syncOriginal();
        $model->foo = 1;
        $model->bar = 20;
        $model->baz = 30;

        static::assertTrue($model->isDirty());
        static::assertFalse($model->isDirty('foo'));
        static::assertTrue($model->isDirty('bar'));
        static::assertTrue($model->isDirty('foo', 'bar'));
        static::assertTrue($model->isDirty(['foo', 'bar']));
    }

    public function testDirtyOnCastOrDateAttributes()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->boolAttribute = 1;
        $model->foo = 1;
        $model->bar = '2017-03-18';
        $model->dateAttribute = '2017-03-18';
        $model->datetimeAttribute = '2017-03-23 22:17:00';
        $model->syncOriginal();
        $model->boolAttribute = true;
        $model->foo = true;
        $model->bar = '2017-03-18 00:00:00';
        $model->dateAttribute = '2017-03-18 00:00:00';
        $model->datetimeAttribute = null;

        static::assertTrue($model->isDirty());
        static::assertTrue($model->isDirty('foo'));
        static::assertTrue($model->isDirty('bar'));
        static::assertFalse($model->isDirty('boolAttribute'));
        static::assertFalse($model->isDirty('dateAttribute'));
        static::assertTrue($model->isDirty('datetimeAttribute'));
    }

    public function testCleanAttributes()
    {
        $model = new EloquentModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
        $model->syncOriginal();
        $model->foo = 1;
        $model->bar = 20;
        $model->baz = 30;

        static::assertFalse($model->isClean());
        static::assertTrue($model->isClean('foo'));
        static::assertFalse($model->isClean('bar'));
        static::assertFalse($model->isClean('foo', 'bar'));
        static::assertFalse($model->isClean(['foo', 'bar']));
    }

    public function testCalculatedAttributes()
    {
        $model = new EloquentModelStub;
        $model->password = 'secret';
        $attributes = $model->getAttributes();

        // ensure password attribute was not set to null
        $this->assertArrayNotHasKey('password', $attributes);
        static::assertEquals('******', $model->password);

        $hash = 'e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4';

        static::assertEquals($hash, $attributes['password_hash']);
        static::assertEquals($hash, $model->password_hash);
    }

    public function testArrayAccessToAttributes()
    {
        $model = new EloquentModelStub(['attributes' => 1, 'connection' => 2, 'table' => 3]);

        unset($model['table']);

        static::assertTrue(isset($model['attributes']));
        static::assertSame(1, $model['attributes']);

        static::assertTrue(isset($model['connection']));
        static::assertSame(2, $model['connection']);

        static::assertFalse(isset($model['table']));
        static::assertSame(null, $model['table']);

        static::assertFalse(isset($model['with']));
    }

    public function testOnly()
    {
        $model = new EloquentModelStub;
        $model->first_name = 'taylor';
        $model->last_name = 'otwell';
        $model->project = 'laravel';

        static::assertEquals(['project' => 'laravel'], $model->only('project'));
        static::assertEquals(['first_name' => 'taylor', 'last_name' => 'otwell'], $model->only('first_name', 'last_name'));
        static::assertEquals(['first_name' => 'taylor', 'last_name' => 'otwell'], $model->only(['first_name', 'last_name']));
    }

    public function testNewInstanceReturnsNewInstanceWithAttributesSet()
    {
        $model    = new EloquentModelStub;
        $instance = $model->newInstance(['name' => 'taylor']);

        static::assertInstanceOf(EloquentModelStub::class, $instance);
        static::assertEquals('taylor', $instance->name);
    }

    public function testNewInstanceReturnsNewInstanceWithTableSet()
    {
        $model = new EloquentModelStub;
        $model->setTable('test');
        $newInstance = $model->newInstance();

        static::assertEquals('test', $newInstance->getTable());
    }

    public function testCreateMethodSavesNewModel()
    {
        $_SERVER['__eloquent.saved'] = false;
        $model = EloquentModelSaveStub::query()->create(['name' => 'taylor']);

        static::assertTrue($_SERVER['__eloquent.saved']);
        static::assertEquals('taylor', $model->name);
    }

    public function testMakeMethodDoesNotSaveNewModel()
    {
        $_SERVER['__eloquent.saved'] = false;
        $model = EloquentModelSaveStub::query()->make(['name' => 'taylor']);

        static::assertFalse($_SERVER['__eloquent.saved']);
        static::assertEquals('taylor', $model->name);
    }

    public function testForceCreateMethodSavesNewModelWithGuardedAttributes()
    {
        $_SERVER['__eloquent.saved'] = false;
        $model = EloquentModelSaveStub::query()->forceCreate(['id' => 21]);

        static::assertTrue($_SERVER['__eloquent.saved']);
        static::assertSame(21, $model->id);
    }

    public function testFindMethodUseWritePdo()
    {
        EloquentModelFindWithWritePdoStub::onWriteConnection()->find(1);
    }

    public function testDestroyMethodCallsQueryBuilderCorrectly()
    {
        EloquentModelDestroyStub::destroy(1, 2, 3);
    }

    public function testDestroyMethodCallsQueryBuilderCorrectlyWithCollection()
    {
        EloquentModelDestroyStub::destroy(new Collection([1, 2, 3]));
    }

    public function testWithMethodCallsQueryBuilderCorrectly()
    {
        $result = EloquentModelWithStub::with('foo', 'bar');

        static::assertEquals('foo', $result);
    }

    public function testWithoutMethodRemovesEagerLoadedRelationshipCorrectly()
    {
        $model = new EloquentModelWithoutRelationStub;
        $this->addMockConnection($model);
        $instance = $model->newInstance()->newQuery()->without('foo');

        static::assertEmpty($instance->getEagerLoads());
    }

    public function testEagerLoadingWithColumns()
    {
        $model    = new EloquentModelWithoutRelationStub;
        $instance = $model->newInstance()->newQuery()->with('foo:bar,baz', 'hadi');
        $builder  = m::mock(Builder::class);
        $builder->shouldReceive('select')->once()->with(['bar', 'baz']);

        static::assertNotNull($instance->getEagerLoads()['hadi']);
        static::assertNotNull($instance->getEagerLoads()['foo']);

        $closure = $instance->getEagerLoads()['foo'];
        $closure($builder);
    }

    public function testWithMethodCallsQueryBuilderCorrectlyWithArray()
    {
        $result = EloquentModelWithStub::with(['foo', 'bar']);

        static::assertEquals('foo', $result);
    }

    public function testUpdateProcess()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['name' => 'taylor'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.updated: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.saved: '.get_class($model), $model)->andReturn(true);
        $model->id = 1;
        $model->foo = 'bar';

        // make sure foo isn't synced so we can test that dirty attributes only are updated
        $model->syncOriginal();
        $model->name = 'taylor';
        $model->exists = true;

        static::assertTrue($model->save());
    }
    public function testUpdateProcessDoesntOverrideTimestamps()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['created_at' => 'foo', 'updated_at' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until');
        $events->shouldReceive('dispatch');
        $model->id = 1;
        $model->syncOriginal();
        $model->created_at = 'foo';
        $model->updated_at = 'bar';
        $model->exists = true;

        static::assertTrue($model->save());
    }

    public function testSaveIsCancelledIfSavingEventReturnsFalse()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery'])->getMock();
        $query = m::mock(Builder::class);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(false);
        $model->exists = true;

        static::assertFalse($model->save());
    }

    public function testUpdateIsCancelledIfUpdatingEventReturnsFalse()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery'])->getMock();
        $query = m::mock(Builder::class);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(false);
        $model->exists = true;
        $model->foo = 'bar';

        static::assertFalse($model->save());
    }

    public function testEventsCanBeFiredWithCustomEventObjects()
    {
        $model = $this->getMockBuilder(EloquentModelEventObjectStub::class)->setMethods(['newModelQuery'])->getMock();
        $query = m::mock(Builder::class);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with(m::type(EloquentModelSavingEventStub::class))->andReturn(false);
        $model->exists = true;

        static::assertFalse($model->save());
    }

    public function testUpdateProcessWithoutTimestamps()
    {
        $model = $this->getMockBuilder(EloquentModelEventObjectStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'fireModelEvent'])->getMock();
        $model->timestamps = false;
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['name' => 'taylor'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->never())->method('updateTimestamps');
        $model->expects($this->any())->method('fireModelEvent')->will($this->returnValue(true));
        $model->id = 1;
        $model->syncOriginal();
        $model->name = 'taylor';
        $model->exists = true;

        static::assertTrue($model->save());
    }

    public function testUpdateUsesOldPrimaryKey()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['id' => 2, 'foo' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.updated: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.saved: '.get_class($model), $model)->andReturn(true);
        $model->id = 1;
        $model->syncOriginal();
        $model->id = 2;
        $model->foo = 'bar';
        $model->exists = true;

        static::assertTrue($model->save());
    }

    public function testTimestampsAreReturnedAsObjects()
    {
        $model = $this->getMockBuilder(EloquentDateModelStub::class)->setMethods(['getDateFormat'])->getMock();
        $model->expects($this->any())->method('getDateFormat')->will($this->returnValue('Y-m-d'));
        $model->setRawAttributes([
            'created_at' => '2012-12-04',
            'updated_at' => '2012-12-05',
        ]);
        static::assertInstanceOf(Carbon::class, $model->created_at);
        static::assertInstanceOf(Carbon::class, $model->updated_at);
    }
    public function testTimestampsAreReturnedAsObjectsFromPlainDatesAndTimestamps()
    {
        $model = $this->getMockBuilder(EloquentDateModelStub::class)->setMethods(['getDateFormat'])->getMock();
        $model->expects($this->any())->method('getDateFormat')->will($this->returnValue('Y-m-d H:i:s'));
        $model->setRawAttributes([
            'created_at' => '2012-12-04',
            'updated_at' => $this->currentTime(),
        ]);
        static::assertInstanceOf(Carbon::class, $model->created_at);
        static::assertInstanceOf(Carbon::class, $model->updated_at);
    }
    public function testTimestampsAreReturnedAsObjectsOnCreate()
    {
        $timestamps = [
            'created_at' =>Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $model = new EloquentDateModelStub;
        Model::setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn($mockConnection = m::mock(stdClass::class));
        $mockConnection->shouldReceive('getQueryGrammar')->andReturn($mockConnection);
        $mockConnection->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $instance = $model->newInstance($timestamps);
        static::assertInstanceOf(Carbon::class, $instance->updated_at);
        static::assertInstanceOf(Carbon::class, $instance->created_at);
    }
    public function testDateTimeAttributesReturnNullIfSetToNull()
    {
        $timestamps = [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $model = new EloquentDateModelStub;
        Model::setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn($mockConnection = m::mock(stdClass::class));
        $mockConnection->shouldReceive('getQueryGrammar')->andReturn($mockConnection);
        $mockConnection->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $instance = $model->newInstance($timestamps);
        $instance->created_at = null;
        static::assertNull($instance->created_at);
    }
    public function testTimestampsAreCreatedFromStringsAndIntegers()
    {
        $model = new EloquentDateModelStub;
        $model->created_at = '2013-05-22 00:00:00';
        static::assertInstanceOf(Carbon::class, $model->created_at);
        $model = new EloquentDateModelStub;
        $model->created_at = $this->currentTime();
        static::assertInstanceOf(Carbon::class, $model->created_at);
        $model = new EloquentDateModelStub;
        $model->created_at = 0;
        static::assertInstanceOf(Carbon::class, $model->created_at);
        $model = new EloquentDateModelStub;
        $model->created_at = '2012-01-01';
        static::assertInstanceOf(Carbon::class, $model->created_at);
    }
    public function testFromDateTime()
    {
        $model = new EloquentModelStub;
        $value = Carbon::parse('2015-04-17 22:59:01');
        static::assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
        $value = new DateTime('2015-04-17 22:59:01');
        static::assertInstanceOf(DateTime::class, $value);
        static::assertInstanceOf(DateTimeInterface::class, $value);
        static::assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
        $value = new DateTimeImmutable('2015-04-17 22:59:01');
        static::assertInstanceOf(DateTimeImmutable::class, $value);
        static::assertInstanceOf(DateTimeInterface::class, $value);
        static::assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
        $value = '2015-04-17 22:59:01';
        static::assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
        $value = '2015-04-17';
        static::assertEquals('2015-04-17 00:00:00', $model->fromDateTime($value));
        $value = '2015-4-17';
        static::assertEquals('2015-04-17 00:00:00', $model->fromDateTime($value));
        $value = '1429311541';
        static::assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
    }
    public function testInsertProcess()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.created: '.get_class($model), $model);
        $events->shouldReceive('dispatch')->once()->with('eloquent.saved: '.get_class($model), $model);
        $model->name = 'taylor';
        $model->exists = false;
        static::assertTrue($model->save());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insert')->once()->with(['name' => 'taylor']);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setIncrementing(false);
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('dispatch')->once()->with('eloquent.created: '.get_class($model), $model);
        $events->shouldReceive('dispatch')->once()->with('eloquent.saved: '.get_class($model), $model);
        $model->name = 'taylor';
        $model->exists = false;
        static::assertTrue($model->save());
        static::assertNull($model->id);
        static::assertTrue($model->exists);
    }
    public function testInsertIsCancelledIfCreatingEventReturnsFalse()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock(Dispatcher::class));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(false);
        static::assertFalse($model->save());
        static::assertFalse($model->exists);
    }
    public function testDeleteProperlyDeletesModel()
    {
        $model = $this->getMockBuilder(Model::class)->setMethods(['newModelQuery', 'updateTimestamps', 'touchOwners'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($query);
        $query->shouldReceive('delete')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('touchOwners');
        $model->exists = true;
        $model->id = 1;
        $model->delete();
    }
    public function testPushNoRelations()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->name = 'taylor';
        $model->exists = false;
        static::assertTrue($model->push());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
    }
    public function testPushEmptyOneRelation()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationOne', null);
        static::assertTrue($model->push());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
        static::assertNull($model->relationOne);
    }
    public function testPushOneRelation()
    {
        $related1 = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related1'], 'id')->andReturn(2);
        $query->shouldReceive('getConnection')->once();
        $related1->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $related1->expects($this->once())->method('updateTimestamps');
        $related1->name = 'related1';
        $related1->exists = false;
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationOne', $related1);
        static::assertTrue($model->push());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
        static::assertEquals(2, $model->relationOne->id);
        static::assertTrue($model->relationOne->exists);
        static::assertEquals(2, $related1->id);
        static::assertTrue($related1->exists);
    }
    public function testPushEmptyManyRelation()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationMany', new Collection([]));
        static::assertTrue($model->push());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
        $this->assertCount(0, $model->relationMany);
    }
    public function testPushManyRelation()
    {
        $related1 = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related1'], 'id')->andReturn(2);
        $query->shouldReceive('getConnection')->once();
        $related1->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $related1->expects($this->once())->method('updateTimestamps');
        $related1->name = 'related1';
        $related1->exists = false;
        $related2 = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related2'], 'id')->andReturn(3);
        $query->shouldReceive('getConnection')->once();
        $related2->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $related2->expects($this->once())->method('updateTimestamps');
        $related2->name = 'related2';
        $related2->exists = false;
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationMany', new Collection([$related1, $related2]));
        static::assertTrue($model->push());
        static::assertEquals(1, $model->id);
        static::assertTrue($model->exists);
        $this->assertCount(2, $model->relationMany);
        static::assertEquals([2, 3], $model->relationMany->pluck('id')->all());
    }
    public function testNewQueryReturnsEloquentQueryBuilder()
    {
        $conn = m::mock(Connection::class);
        $grammar = m::mock(Grammar::class);
        $processor = m::mock(Processor::class);
        $conn->shouldReceive('getQueryGrammar')->once()->andReturn($grammar);
        $conn->shouldReceive('getPostProcessor')->once()->andReturn($processor);
        EloquentModelStub::setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn($conn);
        $model = new EloquentModelStub;
        $builder = $model->newQuery();
        static::assertInstanceOf(Builder::class, $builder);
    }
    public function testGetAndSetTableOperations()
    {
        $model = new EloquentModelStub;
        static::assertEquals('stub', $model->getTable());
        $model->setTable('foo');
        static::assertEquals('foo', $model->getTable());
    }
    public function testGetKeyReturnsValueOfPrimaryKey()
    {
        $model = new EloquentModelStub;
        $model->id = 1;
        static::assertEquals(1, $model->getKey());
        static::assertEquals('id', $model->getKeyName());
    }
    public function testConnectionManagement()
    {
        EloquentModelStub::setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $model = m::mock(EloquentModelStub::class.'[getConnectionName,connection]');
        $retval = $model->setConnection('foo');
        static::assertEquals($retval, $model);
        static::assertEquals('foo', $model->connection);
        $model->shouldReceive('getConnectionName')->once()->andReturn('somethingElse');
        $resolver->shouldReceive('connection')->once()->with('somethingElse')->andReturn('bar');
        static::assertEquals('bar', $model->getConnection());
    }
    public function testToArray()
    {
        $model = new EloquentModelStub;
        $model->name = 'foo';
        $model->age = null;
        $model->password = 'password1';
        $model->setHidden(['password']);
        $model->setRelation('names', new BaseCollection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $model->setRelation('partner', new EloquentModelStub(['name' => 'abby']));
        $model->setRelation('group', null);
        $model->setRelation('multi', new BaseCollection);
        $array = $model->toArray();
        $this->assertInternalType('array', $array);
        static::assertEquals('foo', $array['name']);
        static::assertEquals('baz', $array['names'][0]['bar']);
        static::assertEquals('boom', $array['names'][1]['bam']);
        static::assertEquals('abby', $array['partner']['name']);
        static::assertNull($array['group']);
        static::assertEquals([], $array['multi']);
        static::assertFalse(isset($array['password']));
        $model->setAppends(['appendable']);
        $array = $model->toArray();
        static::assertEquals('appended', $array['appendable']);
    }
    public function testVisibleCreatesArrayWhitelist()
    {
        $model = new EloquentModelStub;
        $model->setVisible(['name']);
        $model->name = 'Taylor';
        $model->age = 26;
        $array = $model->toArray();
        static::assertEquals(['name' => 'Taylor'], $array);
    }
    public function testHiddenCanAlsoExcludeRelationships()
    {
        $model = new EloquentModelStub;
        $model->name = 'Taylor';
        $model->setRelation('foo', ['bar']);
        $model->setHidden(['foo', 'list_items', 'password']);
        $array = $model->toArray();
        static::assertEquals(['name' => 'Taylor'], $array);
    }
    public function testGetArrayableRelationsFunctionExcludeHiddenRelationships()
    {
        $model = new EloquentModelStub;
        $class = new ReflectionClass($model);
        $method = $class->getMethod('getArrayableRelations');
        $method->setAccessible(true);
        $model->setRelation('foo', ['bar']);
        $model->setRelation('bam', ['boom']);
        $model->setHidden(['foo']);
        $array = $method->invokeArgs($model, []);
        static::assertSame(['bam' => ['boom']], $array);
    }
    public function testToArraySnakeAttributes()
    {
        $model = new EloquentModelStub;
        $model->setRelation('namesList', new BaseCollection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $array = $model->toArray();
        static::assertEquals('baz', $array['names_list'][0]['bar']);
        static::assertEquals('boom', $array['names_list'][1]['bam']);
        $model = new EloquentModelCamelStub;
        $model->setRelation('namesList', new BaseCollection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $array = $model->toArray();
        static::assertEquals('baz', $array['namesList'][0]['bar']);
        static::assertEquals('boom', $array['namesList'][1]['bam']);
    }
    public function testToArrayUsesMutators()
    {
        $model = new EloquentModelStub;
        $model->list_items = [1, 2, 3];
        $array = $model->toArray();
        static::assertEquals([1, 2, 3], $array['list_items']);
    }
    public function testHidden()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setHidden(['age', 'id']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }
    public function testVisible()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setVisible(['name', 'id']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }
    public function testDynamicHidden()
    {
        $model = new EloquentModelDynamicHiddenStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }
    public function testWithHidden()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setHidden(['age', 'id']);
        $model->makeVisible('age');
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('id', $array);
    }
    public function testMakeHidden()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'address' => 'foobar', 'id' => 'baz']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('id', $array);
        $array = $model->makeHidden('address')->toArray();
        $this->assertArrayNotHasKey('address', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayHasKey('id', $array);
        $array = $model->makeHidden(['name', 'age'])->toArray();
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
        $this->assertArrayNotHasKey('address', $array);
        $this->assertArrayHasKey('id', $array);
    }
    public function testDynamicVisible()
    {
        $model = new EloquentModelDynamicVisibleStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }
    public function testFillable()
    {
        $model = new EloquentModelStub;
        $model->fillable(['name', 'age']);
        $model->fill(['name' => 'foo', 'age' => 'bar']);
        static::assertEquals('foo', $model->name);
        static::assertEquals('bar', $model->age);
    }
    public function testQualifyColumn()
    {
        $model = new EloquentModelStub;
        static::assertEquals('stub.column', $model->qualifyColumn('column'));
    }
    public function testForceFillMethodFillsGuardedAttributes()
    {
        $model = (new EloquentModelSaveStub)->forceFill(['id' => 21]);
        static::assertEquals(21, $model->id);
    }
    public function testFillingJSONAttributes()
    {
        $model = new EloquentModelStub;
        $model->fillable(['meta->name', 'meta->price', 'meta->size->width']);
        $model->fill(['meta->name' => 'foo', 'meta->price' => 'bar', 'meta->size->width' => 'baz']);
        static::assertEquals(
            ['meta' => json_encode(['name' => 'foo', 'price' => 'bar', 'size' => ['width' => 'baz']])],
            $model->toArray()
        );
        $model = new EloquentModelStub(['meta' => json_encode(['name' => 'Taylor'])]);
        $model->fillable(['meta->name', 'meta->price', 'meta->size->width']);
        $model->fill(['meta->name' => 'foo', 'meta->price' => 'bar', 'meta->size->width' => 'baz']);
        static::assertEquals(
            ['meta' => json_encode(['name' => 'foo', 'price' => 'bar', 'size' => ['width' => 'baz']])],
            $model->toArray()
        );
    }
    public function testUnguardAllowsAnythingToBeSet()
    {
        $model = new EloquentModelStub;
        EloquentModelStub::unguard();
        $model->guard(['*']);
        $model->fill(['name' => 'foo', 'age' => 'bar']);
        static::assertEquals('foo', $model->name);
        static::assertEquals('bar', $model->age);
        EloquentModelStub::unguard(false);
    }
    public function testUnderscorePropertiesAreNotFilled()
    {
        $model = new EloquentModelStub;
        $model->fill(['_method' => 'PUT']);
        static::assertEquals([], $model->getAttributes());
    }
    public function testGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['name', 'age']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'foo' => 'bar']);
        static::assertFalse(isset($model->name));
        static::assertFalse(isset($model->age));
        static::assertEquals('bar', $model->foo);
    }
    public function testFillableOverridesGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['name', 'age']);
        $model->fillable(['age', 'foo']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'foo' => 'bar']);
        static::assertFalse(isset($model->name));
        static::assertEquals('bar', $model->age);
        static::assertEquals('bar', $model->foo);
    }
    /**
     * @expectedException \Illuminate\Database\Eloquent\MassAssignmentException
     * @expectedExceptionMessage name
     */
    public function testGlobalGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['*']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'votes' => 'baz']);
    }
    public function testUnguardedRunsCallbackWhileBeingUnguarded()
    {
        $model = Model::unguarded(function () {
            return (new EloquentModelStub)->guard(['*'])->fill(['name' => 'Taylor']);
        });
        static::assertEquals('Taylor', $model->name);
        static::assertFalse(Model::isUnguarded());
    }
    public function testUnguardedCallDoesNotChangeUnguardedState()
    {
        Model::unguard();
        $model = Model::unguarded(function () {
            return (new EloquentModelStub)->guard(['*'])->fill(['name' => 'Taylor']);
        });
        static::assertEquals('Taylor', $model->name);
        static::assertTrue(Model::isUnguarded());
        Model::reguard();
    }
    public function testUnguardedCallDoesNotChangeUnguardedStateOnException()
    {
        try {
            Model::unguarded(function () {
                throw new Exception;
            });
        } catch (Exception $e) {
            // ignore the exception
        }
        static::assertFalse(Model::isUnguarded());
    }
    public function testHasOneCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasOne(EloquentModelSaveStub::class);
        static::assertEquals('save_stub.eloquent_model_stub_id', $relation->getQualifiedForeignKeyName());
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasOne(EloquentModelSaveStub::class, 'foo');
        static::assertEquals('save_stub.foo', $relation->getQualifiedForeignKeyName());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
    }
    public function testMorphOneCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->morphOne(EloquentModelSaveStub::class, 'morph');
        static::assertEquals('save_stub.morph_id', $relation->getQualifiedForeignKeyName());
        static::assertEquals('save_stub.morph_type', $relation->getQualifiedMorphType());
        static::assertEquals(EloquentModelStub::class, $relation->getMorphClass());
    }
    public function testCorrectMorphClassIsReturned()
    {
        Relation::morphMap(['alias' => 'AnotherModel']);
        $model = new EloquentModelStub;
        try {
            static::assertEquals(EloquentModelStub::class, $model->getMorphClass());
        } finally {
            Relation::morphMap([], false);
        }
    }
    public function testHasManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasMany(EloquentModelSaveStub::class);
        static::assertEquals('save_stub.eloquent_model_stub_id', $relation->getQualifiedForeignKeyName());
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasMany(EloquentModelSaveStub::class, 'foo');
        static::assertEquals('save_stub.foo', $relation->getQualifiedForeignKeyName());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
    }
    public function testMorphManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->morphMany(EloquentModelSaveStub::class, 'morph');
        static::assertEquals('save_stub.morph_id', $relation->getQualifiedForeignKeyName());
        static::assertEquals('save_stub.morph_type', $relation->getQualifiedMorphType());
        static::assertEquals(EloquentModelStub::class, $relation->getMorphClass());
    }
    public function testBelongsToCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToStub();
        static::assertEquals('belongs_to_stub_id', $relation->getForeignKey());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToExplicitKeyStub();
        static::assertEquals('foo', $relation->getForeignKey());
    }
    public function testMorphToCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        // $this->morphTo();
        $relation = $model->morphToStub();
        static::assertEquals('morph_to_stub_id', $relation->getForeignKey());
        static::assertEquals('morph_to_stub_type', $relation->getMorphType());
        static::assertEquals('morphToStub', $relation->getRelation());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
        // $this->morphTo(null, 'type', 'id');
        $relation2 = $model->morphToStubWithKeys();
        static::assertEquals('id', $relation2->getForeignKey());
        static::assertEquals('type', $relation2->getMorphType());
        static::assertEquals('morphToStubWithKeys', $relation2->getRelation());
        // $this->morphTo('someName');
        $relation3 = $model->morphToStubWithName();
        static::assertEquals('some_name_id', $relation3->getForeignKey());
        static::assertEquals('some_name_type', $relation3->getMorphType());
        static::assertEquals('someName', $relation3->getRelation());
        // $this->morphTo('someName', 'type', 'id');
        $relation4 = $model->morphToStubWithNameAndKeys();
        static::assertEquals('id', $relation4->getForeignKey());
        static::assertEquals('type', $relation4->getMorphType());
        static::assertEquals('someName', $relation4->getRelation());
    }
    public function testBelongsToManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToMany(EloquentModelSaveStub::class);
        static::assertEquals('eloquent_model_save_stub_eloquent_model_stub.eloquent_model_stub_id', $relation->getQualifiedForeignPivotKeyName());
        static::assertEquals('eloquent_model_save_stub_eloquent_model_stub.eloquent_model_save_stub_id', $relation->getQualifiedRelatedPivotKeyName());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
        static::assertEquals(__FUNCTION__, $relation->getRelationName());
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToMany(EloquentModelSaveStub::class, 'table', 'foreign', 'other');
        static::assertEquals('table.foreign', $relation->getQualifiedForeignPivotKeyName());
        static::assertEquals('table.other', $relation->getQualifiedRelatedPivotKeyName());
        static::assertSame($model, $relation->getParent());
        static::assertInstanceOf(EloquentModelSaveStub::class, $relation->getQuery()->getModel());
    }
    public function testRelationsWithVariedConnections()
    {
        // Has one
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasOne(EloquentNoConnectionModelStub::class);
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasOne(EloquentDifferentConnectionModelStub::class);
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
        // Morph One
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->morphOne(EloquentNoConnectionModelStub::class, 'type');
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->morphOne(EloquentDifferentConnectionModelStub::class, 'type');
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
        // Belongs to
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->belongsTo(EloquentNoConnectionModelStub::class);
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->belongsTo(EloquentDifferentConnectionModelStub::class);
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
        // has many
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasMany(EloquentNoConnectionModelStub::class);
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasMany(EloquentDifferentConnectionModelStub::class);
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
        // has many through
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasManyThrough(EloquentNoConnectionModelStub::class, EloquentModelSaveStub::class);
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->hasManyThrough(EloquentDifferentConnectionModelStub::class, EloquentModelSaveStub::class);
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
        // belongs to many
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->belongsToMany(EloquentNoConnectionModelStub::class);
        static::assertEquals('non_default', $relation->getRelated()->getConnectionName());
        $model = new EloquentModelStub;
        $model->setConnection('non_default');
        $this->addMockConnection($model);
        $relation = $model->belongsToMany(EloquentDifferentConnectionModelStub::class);
        static::assertEquals('different_connection', $relation->getRelated()->getConnectionName());
    }

    public function testModelsAssumeTheirName()
    {
        require_once __DIR__.'/stubs/EloquentModelNamespacedStub.php';

        $model = new EloquentModelWithoutTableStub;
        static::assertEquals('eloquent_model_without_table_stubs', $model->getTable());
        $namespacedModel = new EloquentModelNamespacedStub;
        static::assertEquals('eloquent_model_namespaced_stubs', $namespacedModel->getTable());
    }

    public function testTheMutatorCacheIsPopulated()
    {
        $class = new EloquentModelStub;
        $expectedAttributes = [
            'list_items',
            'password',
            'appendable',
        ];
        static::assertEquals($expectedAttributes, $class->getMutatedAttributes());
    }
    public function testRouteKeyIsPrimaryKey()
    {
        $model = new EloquentModelNonIncrementingStub;
        $model->id = 'foo';
        static::assertEquals('foo', $model->getRouteKey());
    }
    public function testRouteNameIsPrimaryKeyName()
    {
        $model = new EloquentModelStub;
        static::assertEquals('id', $model->getRouteKeyName());
    }
    public function testCloneModelMakesAFreshCopyOfTheModel()
    {
        $class = new EloquentModelStub;
        $class->id = 1;
        $class->exists = true;
        $class->first = 'taylor';
        $class->last = 'otwell';
        $class->created_at = $class->freshTimestamp();
        $class->updated_at = $class->freshTimestamp();
        $class->setRelation('foo', ['bar']);
        $clone = $class->replicate();
        static::assertNull($clone->id);
        static::assertFalse($clone->exists);
        static::assertEquals('taylor', $clone->first);
        static::assertEquals('otwell', $clone->last);
        $this->assertObjectNotHasAttribute('created_at', $clone);
        $this->assertObjectNotHasAttribute('updated_at', $clone);
        static::assertEquals(['bar'], $clone->foo);
    }

    public function testModelObserversCanBeAttachedToModels()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock(Dispatcher::class));

        $events->shouldReceive('listen')->once()->with('eloquent.creating: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@creating');
        $events->shouldReceive('listen')->once()->with('eloquent.saved: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@saved');
        $events->shouldReceive('forget');
        $events->shouldReceive('dispatch');

        EloquentModelStub::observe(new EloquentTestObserverStub);
        EloquentModelStub::flushEventListeners();


    }

    public function testModelObserversCanBeAttachedToModelsWithString()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock(Dispatcher::class));

        $events->shouldReceive('listen')->once()->with('eloquent.creating: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@creating');
        $events->shouldReceive('listen')->once()->with('eloquent.saved: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@saved');
        $events->shouldReceive('forget');
        $events->shouldReceive('dispatch');

        EloquentModelStub::observe(EloquentTestObserverStub::class);
        EloquentModelStub::flushEventListeners();
    }

    public function testModelObserversCanBeAttachedToModelsThroughAnArray()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock(Dispatcher::class));

        $events->shouldReceive('listen')->once()->with('eloquent.creating: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@creating');
        $events->shouldReceive('listen')->once()->with('eloquent.saved: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@saved');
        $events->shouldReceive('forget');
        $events->shouldReceive('dispatch');

        EloquentModelStub::observe([EloquentTestObserverStub::class]);
        EloquentModelStub::flushEventListeners();
    }

    public function testModelObserversCanBeAttachedToModelsThroughCallingObserveMethodOnlyOnce()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock(Dispatcher::class));

        $events->shouldReceive('listen')->once()->with('eloquent.creating: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@creating');
        $events->shouldReceive('listen')->once()->with('eloquent.saved: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestObserverStub::class.'@saved');
        $events->shouldReceive('listen')->once()->with('eloquent.creating: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestAnotherObserverStub::class.'@creating');
        $events->shouldReceive('listen')->once()->with('eloquent.saved: Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub', EloquentTestAnotherObserverStub::class.'@saved');
        $events->shouldReceive('forget');
        $events->shouldReceive('dispatch');

        EloquentModelStub::observe([
            EloquentTestObserverStub::class,
            EloquentTestAnotherObserverStub::class,
        ]);
        EloquentModelStub::flushEventListeners();
    }

    public function testSetObservableEvents()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo']);
        $this->assertContains('foo', $class->getObservableEvents());
    }

    public function testAddObservableEvent()
    {
        $class = new EloquentModelStub;
        $class->addObservableEvents('foo');
        $this->assertContains('foo', $class->getObservableEvents());
    }

    public function testAddMultipleObserveableEvents()
    {
        $class = new EloquentModelStub;
        $class->addObservableEvents('foo', 'bar');
        $this->assertContains('foo', $class->getObservableEvents());
        $this->assertContains('bar', $class->getObservableEvents());
    }

    public function testRemoveObservableEvent()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo', 'bar']);
        $class->removeObservableEvents('bar');
        $this->assertNotContains('bar', $class->getObservableEvents());
    }

    public function testRemoveMultipleObservableEvents()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo', 'bar']);
        $class->removeObservableEvents('foo', 'bar');
        $this->assertNotContains('foo', $class->getObservableEvents());
        $this->assertNotContains('bar', $class->getObservableEvents());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelStub::incorrectRelationStub must return a relationship instance.
     */
    public function testGetModelAttributeMethodThrowsExceptionIfNotRelation()
    {
        $model = new EloquentModelStub;
        $model->incorrectRelationStub;
    }

    public function testModelIsBootedOnUnserialize()
    {
        $model = new EloquentModelBootingTestStub;
        static::assertTrue(EloquentModelBootingTestStub::isBooted());
        $model->foo = 'bar';
        $string = serialize($model);
        $model = null;
        EloquentModelBootingTestStub::unboot();
        static::assertFalse(EloquentModelBootingTestStub::isBooted());
        unserialize($string);
        static::assertTrue(EloquentModelBootingTestStub::isBooted());
    }

    public function testModelsTraitIsInitialized()
    {
        $model = new EloquentModelStubWithTrait;
        static::assertTrue($model->fooBarIsInitialized);
    }

    public function testAppendingOfAttributes()
    {
        $model = new EloquentModelAppendsStub;
        static::assertTrue(isset($model->is_admin));
        static::assertTrue(isset($model->camelCased));
        static::assertTrue(isset($model->StudlyCased));
        static::assertEquals('admin', $model->is_admin);
        static::assertEquals('camelCased', $model->camelCased);
        static::assertEquals('StudlyCased', $model->StudlyCased);
        $model->setHidden(['is_admin', 'camelCased', 'StudlyCased']);
        static::assertEquals([], $model->toArray());
        $model->setVisible([]);
        static::assertEquals([], $model->toArray());
    }

    public function testGetMutatedAttributes()
    {
        $model = new EloquentModelGetMutatorsStub;
        static::assertEquals(['first_name', 'middle_name', 'last_name'], $model->getMutatedAttributes());
        EloquentModelGetMutatorsStub::resetMutatorCache();
        EloquentModelGetMutatorsStub::$snakeAttributes = false;
        static::assertEquals(['firstName', 'middleName', 'lastName'], $model->getMutatedAttributes());
    }

    public function testReplicateCreatesANewModelInstanceWithSameAttributeValues()
    {
        $model = new EloquentModelStub;
        $model->id = 'id';
        $model->foo = 'bar';
        $model->created_at = new DateTime;
        $model->updated_at = new DateTime;
        $replicated = $model->replicate();
        static::assertNull($replicated->id);
        static::assertEquals('bar', $replicated->foo);
        static::assertNull($replicated->created_at);
        static::assertNull($replicated->updated_at);
    }

    public function testIncrementOnExistingModelCallsQueryAndSetsAttribute()
    {
        $model = m::mock(EloquentModelStub::class.'[newModelQuery]');
        $model->exists = true;
        $model->id = 1;
        $model->syncOriginalAttribute('id');
        $model->foo = 2;
        $model->shouldReceive('newModelQuery')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->andReturn($query);
        $query->shouldReceive('increment');
        $model->publicIncrement('foo', 1);
        static::assertFalse($model->isDirty());
        $model->publicIncrement('foo', 1, ['category' => 1]);
        static::assertEquals(4, $model->foo);
        static::assertEquals(1, $model->category);
        static::assertTrue($model->isDirty('category'));
    }

    public function testRelationshipTouchOwnersIsPropagated()
    {
        $relation = $this->getMockBuilder(BelongsTo::class)->setMethods(['touch'])->disableOriginalConstructor()->getMock();
        $relation->expects($this->once())->method('touch');
        $model = m::mock(EloquentModelStub::class.'[partner]');
        $this->addMockConnection($model);
        $model->shouldReceive('partner')->once()->andReturn($relation);
        $model->setTouchedRelations(['partner']);
        $mockPartnerModel = m::mock(EloquentModelStub::class.'[touchOwners]');
        $mockPartnerModel->shouldReceive('touchOwners')->once();
        $model->setRelation('partner', $mockPartnerModel);
        $model->touchOwners();
    }

    public function testRelationshipTouchOwnersIsNotPropagatedIfNoRelationshipResult()
    {
        $relation = $this->getMockBuilder(BelongsTo::class)->setMethods(['touch'])->disableOriginalConstructor()->getMock();
        $relation->expects($this->once())->method('touch');
        $model = m::mock(EloquentModelStub::class.'[partner]');
        $this->addMockConnection($model);
        $model->shouldReceive('partner')->once()->andReturn($relation);
        $model->setTouchedRelations(['partner']);
        $model->setRelation('partner', null);
        $model->touchOwners();
    }

    public function testModelAttributesAreCastedWhenPresentInCastsArray()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->intAttribute = '3';
        $model->floatAttribute = '4.0';
        $model->stringAttribute = 2.5;
        $model->boolAttribute = 1;
        $model->booleanAttribute = 0;
        $model->objectAttribute = ['foo' => 'bar'];
        $obj = new stdClass;
        $obj->foo = 'bar';
        $model->arrayAttribute = $obj;
        $model->jsonAttribute = ['foo' => 'bar'];
        $model->dateAttribute = '1969-07-20';
        $model->datetimeAttribute = '1969-07-20 22:56:00';
        $model->timestampAttribute = '1969-07-20 22:56:00';
        $this->assertInternalType('int', $model->intAttribute);
        $this->assertInternalType('float', $model->floatAttribute);
        $this->assertInternalType('string', $model->stringAttribute);
        $this->assertInternalType('boolean', $model->boolAttribute);
        $this->assertInternalType('boolean', $model->booleanAttribute);
        $this->assertInternalType('object', $model->objectAttribute);
        $this->assertInternalType('array', $model->arrayAttribute);
        $this->assertInternalType('array', $model->jsonAttribute);
        static::assertTrue($model->boolAttribute);
        static::assertFalse($model->booleanAttribute);
        static::assertEquals($obj, $model->objectAttribute);
        static::assertEquals(['foo' => 'bar'], $model->arrayAttribute);
        static::assertEquals(['foo' => 'bar'], $model->jsonAttribute);
        static::assertEquals('{"foo":"bar"}', $model->jsonAttributeValue());
        static::assertInstanceOf(Carbon::class, $model->dateAttribute);
        static::assertInstanceOf(Carbon::class, $model->datetimeAttribute);
        static::assertEquals('1969-07-20', $model->dateAttribute->toDateString());
        static::assertEquals('1969-07-20 22:56:00', $model->datetimeAttribute->toDateTimeString());
        static::assertEquals(-14173440, $model->timestampAttribute);
        $arr = $model->toArray();
        $this->assertInternalType('int', $arr['intAttribute']);
        $this->assertInternalType('float', $arr['floatAttribute']);
        $this->assertInternalType('string', $arr['stringAttribute']);
        $this->assertInternalType('boolean', $arr['boolAttribute']);
        $this->assertInternalType('boolean', $arr['booleanAttribute']);
        $this->assertInternalType('object', $arr['objectAttribute']);
        $this->assertInternalType('array', $arr['arrayAttribute']);
        $this->assertInternalType('array', $arr['jsonAttribute']);
        static::assertTrue($arr['boolAttribute']);
        static::assertFalse($arr['booleanAttribute']);
        static::assertEquals($obj, $arr['objectAttribute']);
        static::assertEquals(['foo' => 'bar'], $arr['arrayAttribute']);
        static::assertEquals(['foo' => 'bar'], $arr['jsonAttribute']);
        static::assertEquals('1969-07-20 00:00:00', $arr['dateAttribute']);
        static::assertEquals('1969-07-20 22:56:00', $arr['datetimeAttribute']);
        static::assertEquals(-14173440, $arr['timestampAttribute']);
    }

    public function testModelDateAttributeCastingResetsTime()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->dateAttribute = '1969-07-20 22:56:00';
        static::assertEquals('1969-07-20 00:00:00', $model->dateAttribute->toDateTimeString());
        $arr = $model->toArray();
        static::assertEquals('1969-07-20 00:00:00', $arr['dateAttribute']);
    }

    public function testModelAttributeCastingPreservesNull()
    {
        $model = new EloquentModelCastingStub;
        $model->intAttribute = null;
        $model->floatAttribute = null;
        $model->stringAttribute = null;
        $model->boolAttribute = null;
        $model->booleanAttribute = null;
        $model->objectAttribute = null;
        $model->arrayAttribute = null;
        $model->jsonAttribute = null;
        $model->dateAttribute = null;
        $model->datetimeAttribute = null;
        $model->timestampAttribute = null;
        $attributes = $model->getAttributes();
        static::assertNull($attributes['intAttribute']);
        static::assertNull($attributes['floatAttribute']);
        static::assertNull($attributes['stringAttribute']);
        static::assertNull($attributes['boolAttribute']);
        static::assertNull($attributes['booleanAttribute']);
        static::assertNull($attributes['objectAttribute']);
        static::assertNull($attributes['arrayAttribute']);
        static::assertNull($attributes['jsonAttribute']);
        static::assertNull($attributes['dateAttribute']);
        static::assertNull($attributes['datetimeAttribute']);
        static::assertNull($attributes['timestampAttribute']);
        static::assertNull($model->intAttribute);
        static::assertNull($model->floatAttribute);
        static::assertNull($model->stringAttribute);
        static::assertNull($model->boolAttribute);
        static::assertNull($model->booleanAttribute);
        static::assertNull($model->objectAttribute);
        static::assertNull($model->arrayAttribute);
        static::assertNull($model->jsonAttribute);
        static::assertNull($model->dateAttribute);
        static::assertNull($model->datetimeAttribute);
        static::assertNull($model->timestampAttribute);
        $array = $model->toArray();
        static::assertNull($array['intAttribute']);
        static::assertNull($array['floatAttribute']);
        static::assertNull($array['stringAttribute']);
        static::assertNull($array['boolAttribute']);
        static::assertNull($array['booleanAttribute']);
        static::assertNull($array['objectAttribute']);
        static::assertNull($array['arrayAttribute']);
        static::assertNull($array['jsonAttribute']);
        static::assertNull($array['dateAttribute']);
        static::assertNull($array['datetimeAttribute']);
        static::assertNull($array['timestampAttribute']);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\JsonEncodingException
     * @expectedExceptionMessage Unable to encode attribute [objectAttribute] for model [Arcanedev\LaravelCastable\Tests\Database\Eloquent\EloquentModelCastingStub] to JSON: Malformed UTF-8 characters, possibly incorrectly encoded.
     */
    public function testModelAttributeCastingFailsOnUnencodableData()
    {
        $model = new EloquentModelCastingStub;
        $model->objectAttribute = ['foo' => "b\xF8r"];
        $obj = new stdClass;
        $obj->foo = "b\xF8r";
        $model->arrayAttribute = $obj;
        $model->jsonAttribute = ['foo' => "b\xF8r"];
        $model->getAttributes();
    }

    public function testModelAttributeCastingWithSpecialFloatValues()
    {
        $model = new EloquentModelCastingStub;

        $model->floatAttribute = 0;
        static::assertSame(0.0, $model->floatAttribute);

        $model->floatAttribute = 'Infinity';
        static::assertSame(INF, $model->floatAttribute);

        $model->floatAttribute = INF;
        static::assertSame(INF, $model->floatAttribute);

        $model->floatAttribute = '-Infinity';
        static::assertSame(-INF, $model->floatAttribute);

        $model->floatAttribute = -INF;
        static::assertSame(-INF, $model->floatAttribute);

        $model->floatAttribute = 'NaN';
        static::assertNan($model->floatAttribute);

        $model->floatAttribute = NAN;
        static::assertNan($model->floatAttribute);
    }

    public function testUpdatingNonExistentModelFails()
    {
        static::assertFalse(
            (new EloquentModelStub)->update()
        );
    }

    public function testIssetBehavesCorrectlyWithAttributesAndRelationships()
    {
        $model = new EloquentModelStub;

        static::assertFalse(isset($model->nonexistent));

        $model->some_attribute = 'some_value';
        static::assertTrue(isset($model->some_attribute));

        $model->setRelation('some_relation', 'some_value');
        static::assertTrue(isset($model->some_relation));
    }

    public function testNonExistingAttributeWithInternalMethodNameDoesntCallMethod()
    {
        $model = m::mock(EloquentModelStub::class.'[delete,getRelationValue]');
        $model->name = 'Spark';
        $model->shouldNotReceive('delete');
        $model->shouldReceive('getRelationValue')->once()->with('belongsToStub')->andReturn('relation');

        // Can return a normal relation
        static::assertEquals('relation', $model->belongsToStub);

        // Can return a normal attribute
        static::assertEquals('Spark', $model->name);

        // Returns null for a Model.php method name
        static::assertNull($model->delete);

        $model = m::mock(EloquentModelStub::class.'[delete]');
        $model->delete = 123;

        static::assertEquals(123, $model->delete);
    }

    public function testIntKeyTypePreserved()
    {
        $model = $this->getMockBuilder(EloquentModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with([], 'id')->andReturn(1);
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));

        static::assertTrue($model->save());
        static::assertSame(1, $model->id);
    }

    public function testStringKeyTypePreserved()
    {
        $model = $this->getMockBuilder(EloquentKeyTypeModelStub::class)->setMethods(['newModelQuery', 'updateTimestamps', 'refresh'])->getMock();
        $query = m::mock(Builder::class);
        $query->shouldReceive('insertGetId')->once()->with([], 'id')->andReturn('string id');
        $query->shouldReceive('getConnection')->once();
        $model->expects($this->once())->method('newModelQuery')->will($this->returnValue($query));

        static::assertTrue($model->save());
        static::assertSame('string id', $model->id);
    }

    public function testScopesMethod()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $scopes = [
            'published',
            'category' => 'Laravel',
            'framework' => ['Laravel', '5.3'],
        ];

        static::assertInstanceOf(Builder::class, $model->scopes($scopes));
        static::assertSame($scopes, $model->scopesCalled);
    }

    public function testIsWithNull()
    {
        $firstInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance = null;

        static::assertFalse($firstInstance->is($secondInstance));
    }

    public function testIsWithTheSameModelInstance()
    {
        $firstInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance = new EloquentModelStub(['id' => 1]);
        $result = $firstInstance->is($secondInstance);

        static::assertTrue($result);
    }

    public function testIsWithAnotherModelInstance()
    {
        $firstInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance = new EloquentModelStub(['id' => 2]);
        $result = $firstInstance->is($secondInstance);

        static::assertFalse($result);
    }

    public function testIsWithAnotherTable()
    {
        $firstInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance->setTable('foo');
        $result = $firstInstance->is($secondInstance);

        static::assertFalse($result);
    }

    public function testIsWithAnotherConnection()
    {
        $firstInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance = new EloquentModelStub(['id' => 1]);
        $secondInstance->setConnection('foo');
        $result = $firstInstance->is($secondInstance);

        static::assertFalse($result);
    }

    public function testWithoutTouchingCallback()
    {
        $model = new EloquentModelStub(['id' => 1]);
        $called = false;

        EloquentModelStub::withoutTouching(function () use (&$called, $model) {
            $called = true;
        });

        static::assertTrue($called);
    }

    public function testWithoutTouchingOnCallback()
    {
        $model = new EloquentModelStub(['id' => 1]);
        $called = false;
        Model::withoutTouchingOn([EloquentModelStub::class], function () use (&$called, $model) {
            $called = true;
        });

        static::assertTrue($called);
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    protected function addMockConnection($model)
    {
        $model->setConnectionResolver($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn(m::mock(Connection::class));
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
    }
}

class EloquentTestObserverStub
{
    public function creating()
    {
        //
    }

    public function saved()
    {
        //
    }
}

class EloquentTestAnotherObserverStub
{
    public function creating()
    {
        //
    }

    public function saved()
    {
        //
    }
}

class EloquentModelStub extends Model
{
    public $connection;

    public $scopesCalled = [];

    protected $table = 'stub';

    protected $guarded = [];

    protected $morph_to_stub_type = EloquentModelSaveStub::class;

    public function getListItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setListItemsAttribute($value)
    {
        $this->attributes['list_items'] = json_encode($value);
    }

    public function getPasswordAttribute()
    {
        return '******';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = sha1($value);
    }

    public function publicIncrement($column, $amount = 1, $extra = [])
    {
        return $this->increment($column, $amount, $extra);
    }

    public function belongsToStub()
    {
        return $this->belongsTo(EloquentModelSaveStub::class);
    }

    public function morphToStub()
    {
        return $this->morphTo();
    }

    public function morphToStubWithKeys()
    {
        return $this->morphTo(null, 'type', 'id');
    }

    public function morphToStubWithName()
    {
        return $this->morphTo('someName');
    }

    public function morphToStubWithNameAndKeys()
    {
        return $this->morphTo('someName', 'type', 'id');
    }

    public function belongsToExplicitKeyStub()
    {
        return $this->belongsTo(EloquentModelSaveStub::class, 'foo');
    }

    public function incorrectRelationStub()
    {
        return 'foo';
    }

    public function getDates()
    {
        return [];
    }

    public function getAppendableAttribute()
    {
        return 'appended';
    }

    public function scopePublished(Builder $builder)
    {
        $this->scopesCalled[] = 'published';
    }

    public function scopeCategory(Builder $builder, $category)
    {
        $this->scopesCalled['category'] = $category;
    }

    public function scopeFramework(Builder $builder, $framework, $version)
    {
        $this->scopesCalled['framework'] = [$framework, $version];
    }
}

trait FooBarTrait
{
    public $fooBarIsInitialized = false;

    public function initializeFooBarTrait()
    {
        $this->fooBarIsInitialized = true;
    }
}

class EloquentModelStubWithTrait extends EloquentModelStub
{
    use FooBarTrait;
}

class EloquentModelCamelStub extends EloquentModelStub
{
    public static $snakeAttributes = false;
}

class EloquentDateModelStub extends EloquentModelStub
{
    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }
}

class EloquentModelSaveStub extends Model
{
    protected $table = 'save_stub';

    protected $guarded = ['id'];

    public function save(array $options = [])
    {
        $_SERVER['__eloquent.saved'] = true;
    }

    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }

    public function getConnection()
    {
        return tap(m::mock(Connection::class), function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('getQueryGrammar')->andReturn(m::mock(Grammar::class));
            $mock->shouldReceive('getPostProcessor')->andReturn(m::mock(Processor::class));
            $mock->shouldReceive('getName')->andReturn('name');
        });
    }
}

class EloquentKeyTypeModelStub extends EloquentModelStub
{
    protected $keyType = 'string';
}

class EloquentModelFindWithWritePdoStub extends Model
{
    public function newQuery()
    {
        return tap(m::mock(Builder::class), function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('useWritePdo')->once()->andReturnSelf();
            $mock->shouldReceive('find')->once()->with(1)->andReturn('foo');
        });
    }
}

class EloquentModelDestroyStub extends Model
{
    public function newQuery()
    {
        return tap(m::mock(Builder::class), function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('whereIn')->once()->with('id', [1, 2, 3])->andReturn($mock);
            $mock->shouldReceive('get')->once()->andReturn([$model = m::mock(stdClass::class)]);
            $model->shouldReceive('delete')->once();
        });
    }
}

class EloquentModelHydrateRawStub extends Model
{
    public static function hydrate(array $items, $connection = null)
    {
        return 'hydrated';
    }

    public function getConnection()
    {
        return tap(m::mock(Connection::class), function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('select')->once()->with('SELECT ?', ['foo'])->andReturn([]);
        });
    }
}

class EloquentModelWithStub extends Model
{
    public function newQuery()
    {
        return tap(m::mock(Builder::class), function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('with')->once()->with(['foo', 'bar'])->andReturn('foo');
        });
    }
}

class EloquentModelWithoutRelationStub extends Model
{
    public $with = ['foo'];

    protected $guarded = [];

    public function getEagerLoads()
    {
        return $this->eagerLoads;
    }
}

class EloquentModelWithoutTableStub extends Model
{
    //
}

class EloquentModelBootingTestStub extends Model
{
    public static function unboot()
    {
        unset(static::$booted[static::class]);
    }

    public static function isBooted()
    {
        return array_key_exists(static::class, static::$booted);
    }
}

class EloquentModelAppendsStub extends Model
{
    protected $appends = ['is_admin', 'camelCased', 'StudlyCased'];

    public function getIsAdminAttribute()
    {
        return 'admin';
    }

    public function getCamelCasedAttribute()
    {
        return 'camelCased';
    }

    public function getStudlyCasedAttribute()
    {
        return 'StudlyCased';
    }
}

class EloquentModelGetMutatorsStub extends Model
{
    public static function resetMutatorCache()
    {
        static::$mutatorCache = [];
    }

    public function getFirstNameAttribute()
    {
        //
    }

    public function getMiddleNameAttribute()
    {
        //
    }

    public function getLastNameAttribute()
    {
        //
    }

    public function doNotgetFirstInvalidAttribute()
    {
        //
    }

    public function doNotGetSecondInvalidAttribute()
    {
        //
    }

    public function doNotgetThirdInvalidAttributeEither()
    {
        //
    }

    public function doNotGetFourthInvalidAttributeEither()
    {
        //
    }
}

class EloquentModelCastingStub extends Model
{
    protected $casts = [
        'intAttribute'       => 'int',
        'floatAttribute'     => 'float',
        'stringAttribute'    => 'string',
        'boolAttribute'      => 'bool',
        'booleanAttribute'   => 'boolean',
        'objectAttribute'    => 'object',
        'arrayAttribute'     => 'array',
        'jsonAttribute'      => 'json',
        'dateAttribute'      => 'date',
        'datetimeAttribute'  => 'datetime',
        'timestampAttribute' => 'timestamp',
    ];

    public function jsonAttributeValue()
    {
        return $this->attributes['jsonAttribute'];
    }
}

class EloquentModelDynamicHiddenStub extends Model
{
    protected $table = 'stub';

    protected $guarded = [];

    public function getHidden()
    {
        return ['age', 'id'];
    }
}

class EloquentModelDynamicVisibleStub extends Model
{
    protected $table = 'stub';

    protected $guarded = [];

    public function getVisible()
    {
        return ['name', 'id'];
    }
}

class EloquentModelNonIncrementingStub extends Model
{
    protected $table = 'stub';

    protected $guarded = [];

    public $incrementing = false;
}

class EloquentNoConnectionModelStub extends EloquentModelStub
{
    //
}

class EloquentDifferentConnectionModelStub extends EloquentModelStub
{
    public $connection = 'different_connection';
}

class EloquentModelSavingEventStub
{
    //
}

class EloquentModelEventObjectStub extends Model
{
    protected $dispatchesEvents = [
        'saving' => EloquentModelSavingEventStub::class,
    ];
}
