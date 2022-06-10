<?php

namespace Netsells\HashModelIds;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ModelWithHashedIdExistsRule implements Rule
{
    private string $class;

    private $constraints = null;

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
     * Accept any additional constraints chained to a rule instance.
     *
     * @param callable $constraints
     *
     * @return self
     */
    public function where(callable $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || empty($value)) {
            return false;
        }

        return $this->class::whereHashedId($value)
            ->when($this->constraints, $this->constraints ?? fn ($query) => $query)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('hashModelIds::validation.model_not_exist_for_hashed_id', [
            'name' => class_basename($this->class),
        ]);
    }
}
