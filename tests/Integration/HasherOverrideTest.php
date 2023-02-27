<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Netsells\HashModelIds\Tests\Integration\fixtures\Models\Foo;

class HasherOverrideTest extends AbstractIntegrationTest
{
    private Foo $foo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->foo = Foo::create();
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('hash-model-ids.override', true);
    }

    public function testHashedIdAttributeReturnsActualId(): void
    {
        $this->assertNotNull($this->foo->getKey());
        $this->assertEquals($this->foo->getKey(), $this->foo->hashed_id);
    }

    public function testScopeWhereHashedId(): void
    {
        $hash = $this->foo->getKey();

        $foo = Foo::whereHashedId($hash)->first();

        $this->assertTrue($foo->is($this->foo));
    }
}
