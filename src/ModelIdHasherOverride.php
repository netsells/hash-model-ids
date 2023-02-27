<?php

namespace Netsells\HashModelIds;

use Illuminate\Database\Eloquent\Model;

class ModelIdHasherOverride implements ModelIdHasherInterface
{
    public function encode(Model $model, $id): string
    {
        return (string) $id;
    }

    public function decode(Model $model, $hash): string
    {
        return (string) $hash;
    }
}
