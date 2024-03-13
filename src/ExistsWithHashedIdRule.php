<?php

namespace Netsells\HashModelIds;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ExistsWithHashedIdRule implements ValidationRule
{
    use ForwardsCalls;

    private string $class;

    private array $constraints = [];

    /**
     * Make a new rule instance fluently.
     *
     * @param string $class
     *
     * @return self
     */
    public static function make(string $class): self
    {
        return new self($class);
    }

    /**
     * Create a new rule instance.
     *
     * @param class-string<Model> $class
     *
     * @return void
     */
    public function __construct(string $class)
    {
        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('The given class must be an instance of '.Model::class);
        }

        if (! in_array(HashesModelIdsTrait::class, class_uses_recursive($class))) {
            throw new InvalidArgumentException('The given class must use the '.HashesModelIdsTrait::class.' trait');
        }

        $this->class = $class;
    }

    /**
     * Run the validation rule.
     *
     * @param  string|null  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(?string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            $this->fail($fail);

            return;
        }

        $doesntExist = $this->class::whereHashedId($value)
            ->tap(function (Builder $query) {
                $this->applyConstraints($query);
            })
            ->doesntExist();

        if ($doesntExist) {
            $this->fail($fail);
        }
    }

    /**
     * Fail the validation.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function fail(Closure $fail): void
    {
        $fail('hashModelIds::validation.model_not_exist_for_hashed_id')->translate([
            'name' => class_basename($this->class),
        ]);
    }

    /**
     * Add query builder constraints to the rule.
     *
     * @param string $method
     * @param mixed $parameters
     *
     * @return self
     */
    public function __call(string $method, array $parameters): self
    {
        $this->constraints[$method][] = $parameters;

        return $this;
    }

    private function applyConstraints(Builder $query): void
    {
        foreach ($this->constraints as $method => $constraint) {
            foreach ($constraint as $parameters) {
                $this->forwardCallTo($query, $method, $parameters);
            }
        }
    }
}
