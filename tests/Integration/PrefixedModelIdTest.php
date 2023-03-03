<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Netsells\HashModelIds\ModelIdPrefixer;
use Netsells\HashModelIds\Tests\Integration\Fixtures\Models\Foo;

class PrefixedModelIdTest extends AbstractIntegrationTest
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

        $app['config']->set('hash-model-ids.enabled', false);
    }

    public function testHashedIdAttributeReturnsActualIdWithPrefix(): void
    {
        $this->assertNotNull($this->foo->getKey());
        $this->assertSame($this->getPrefixedId(), $this->foo->hashed_id);
    }

    public function testScopeWhereHashedId(): void
    {
        $hashedId = $this->getPrefixedId();

        $foo = Foo::whereHashedId($hashedId)->first();

        $this->assertTrue($foo->is($this->foo));
    }

    private function getPrefixedId(): string
    {
        return ModelIdPrefixer::DEFAULT_PREFIX . $this->foo->getKey();
    }
}
