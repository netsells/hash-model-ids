<?php

namespace Netsells\HashModelIds;

use Illuminate\Database\Eloquent\Model;

class ModelIdPrefixer implements ModelIdHasherInterface
{
    public const DEFAULT_PREFIX = 'id_';

    public function __construct(private readonly string $prefix = self::DEFAULT_PREFIX)
    {
        //
    }

    public function encode(Model $model, $id): string
    {
        return "{$this->prefix}{$id}";
    }

    public function decode(Model $model, $hash): string
    {
        return substr($hash, strlen($this->prefix));
    }
}
