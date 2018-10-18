<?php namespace Arcanedev\LaravelCastable\Tests;

use Arcanedev\LaravelCastable\LaravelCastableServiceProvider;

class LaravelCastableServiceProviderTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var \Arcanedev\LaravelCastable\LaravelCastableServiceProvider */
    private $provider;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(LaravelCastableServiceProvider::class);
    }

    protected function tearDown()
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_provides()
    {
        $expected = [
            \Arcanedev\LaravelCastable\Contracts\CasterManager::class,
        ];

        static::assertSame($expected, $this->provider->provides());
    }
}
