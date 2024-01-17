<?php

namespace Netsells\HashModelIds;

use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * @property-read string $hashed_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHashedId(string $hash)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHashedIds(array $hashes)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNotHashedIds(array $hashes)
 */
trait HashesModelIdsTrait
{
    public function getRouteKeyName(): string
    {
        return 'hashed_id';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (str_ends_with($field ?? $this->getRouteKeyName(), 'hashed_id')) {
            return $query->whereHashedId($value);
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    protected function getHashedIdAttribute(): string
    {
        return $this->getIdHasher()->encode($this, $this->getKey());
    }

    public function scopeWhereHashedId(Builder $query, string $hash): Builder
    {
        return $query->where(
            $this->qualifyColumn($this->getKeyName()),
            $this->getIdHasher()->decode($this, $hash)
        );
    }

    public function scopeWhereHashedIds(Builder $query, array $hashes): Builder
    {
        return $query->whereIn($this->qualifyColumn($this->getKeyName()), $this->getDecodedHashes($hashes));
    }

    public function scopeWhereNotHashedIds(Builder $query, array $hashes): Builder
    {
        return $query->whereNotIn($this->qualifyColumn($this->getKeyName()), $this->getDecodedHashes($hashes));
    }

    private function getIdHasher(): ModelIdHasherInterface
    {
        return app(ModelIdHasherInterface::class);
    }

    private function getDecodedHashes(array $hashes): array
    {
        $hasher = $this->getIdHasher();

        return array_map(function (string $hash) use ($hasher) {
            return $hasher->decode($this, $hash);
        }, $hashes);
    }
}
