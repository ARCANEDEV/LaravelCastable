<?php namespace Arcanedev\LaravelCastable\Tests\Casts;

use Arcanedev\LaravelCastable\Casts\JsonCaster;

class JsonCasterTest extends TestCase
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
            \Arcanedev\LaravelCastable\Casts\JsonCaster::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->caster);
        }
    }

    /** @test */
    public function it_can_cast()
    {
        static::assertEquals(
            ['foo' => 'bar'],
            $this->caster->cast('{"foo":"bar"}')
        );
    }

    /** @test */
    public function it_can_uncast()
    {
        static::assertEquals(
            '{"foo":"bar"}',
            $this->caster->uncast(['foo' => 'bar'])
        );
    }

    /**
     * Get the caster.
     *
     * @return \Arcanedev\LaravelCastable\Contracts\Caster
     */
    protected function caster()
    {
        return new JsonCaster;
    }
}
