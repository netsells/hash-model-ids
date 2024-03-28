<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Translation\Translator;
use Illuminate\Validation\InvokableValidationRule;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use Netsells\HashModelIds\ExistsWithHashedIdRule;
use Netsells\HashModelIds\Tests\Integration\Fixtures\Models;

class ExistsWithHashedIdRuleTest extends AbstractIntegrationTestCase
{
    public function testRuleExpectsEloquentModels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExistsWithHashedIdRule('AbstractClass');
    }

    public function testRuleExpectsHashingModels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExistsWithHashedIdRule(Models\Bar::class);
    }

    public function testRuleHandlesExpectedValueTypes(): void
    {
        $rule = InvokableValidationRule::make(
            invokable: new ExistsWithHashedIdRule(Models\Foo::class),
        )->setValidator(validator: $this->getValidator());

        $this->assertFalse($rule->passes(null, null));
        $this->assertFalse($rule->passes(null, []));
        $this->assertFalse($rule->passes(null, ''));
    }

    public function testRulePassesForExistingHashedId(): void
    {
        $foo = Models\Foo::create();

        $rule = InvokableValidationRule::make(
            invokable: new ExistsWithHashedIdRule(Models\Foo::class),
        )->setValidator(validator: $this->getValidator());

        $this->assertTrue($rule->passes(null, $foo->hashed_id));
    }

    public function testRuleFailsForNonExistentHashedId(): void
    {
        $foo = Models\Foo::create();

        $rule = InvokableValidationRule::make(
            invokable: new ExistsWithHashedIdRule(Models\Foo::class),
        )->setValidator(validator: $this->getValidator());

        $this->assertFalse($rule->passes(null, $foo->id));
    }

    public function testRuleHandlesConstraintsFluently(): void
    {
        $foo = Models\Foo::create([]);

        $rule = InvokableValidationRule::make(
            invokable: (new ExistsWithHashedIdRule(Models\Foo::class))
                ->where(function (Builder $query) use ($foo) {
                    $query->whereId($foo->hashed_id);
                }),
        )->setValidator(validator: $this->getValidator());

        $this->assertFalse($rule->passes(null, $foo->hashed_id));

        $rule = InvokableValidationRule::make(
            invokable: (new ExistsWithHashedIdRule(Models\Foo::class))
                ->where(function (Builder $query) use ($foo) {
                    $query->whereId($foo->id);
                })
                ->whereNotNull('created_at'),
        )->setValidator(validator: $this->getValidator());

        $this->assertTrue($rule->passes(null, $foo->hashed_id));
    }

    private function getValidator(): Validator
    {
        return new Validator(
            translator: $this->app->make(abstract: Translator::class),
            data: [],
            rules: [],
        );
    }
}
