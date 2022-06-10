<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Netsells\HashModelIds\ModelWithHashedIdExistsRule;
use Netsells\HashModelIds\Tests\Integration\fixtures\Models;

class ModelWithHashedIdExistsRuleTest extends AbstractIntegrationTest
{
    private ModelWithHashedIdExistsRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new ModelWithHashedIdExistsRule(Models\Foo::class);
    }

    public function testRuleExpectsEloquentModels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ModelWithHashedIdExistsRule('AbstractClass');
    }

    public function testRuleExpectsHashingModels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ModelWithHashedIdExistsRule(Models\Bar::class);
    }

    public function testRuleHandlesExpectedValueTypes(): void
    {
        $this->assertFalse($this->rule->passes(null, null));
        $this->assertFalse($this->rule->passes(null, []));
        $this->assertFalse($this->rule->passes(null, ''));
    }

    public function testRulePassesForExistingHashedId(): void
    {
        $foo = Models\Foo::create();

        $this->assertTrue($this->rule->passes(null, $foo->hashed_id));
    }

    public function testRuleFailsForNonExistentHashedId(): void
    {
        $foo = Models\Foo::create();

        $this->assertFalse($this->rule->passes(null, $foo->id));
    }

    public function testRuleHandlesAdditionalConstraints(): void
    {
        $foo = Models\Foo::create();

        $this->assertFalse(
            $this->rule->where(function (Builder $query) use ($foo) {
                $query->whereId($foo->hashed_id);
            })->passes(null, $foo->hashed_id)
        );

        $this->assertTrue(
            $this->rule->where(function (Builder $query) use ($foo) {
                $query->whereId($foo->id);
            })->passes(null, $foo->hashed_id)
        );
    }
}
