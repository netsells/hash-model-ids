<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Netsells\HashModelIds\Tests\Integration\Fixtures\Models\Trashed;

class WithTrashedScopeTest extends AbstractIntegrationTestCase
{
    public function testModelsWithTrashedScopeCanBeLocated(): void
    {
        Route::middleware(SubstituteBindings::class)
            ->get('trashed/{trashed}', function (Trashed $trashed) {
                return $trashed;
            });

        $trashed = Trashed::create();
        $trashed->delete();

        $this->json('GET', url("trashed/$trashed->hashed_id"))->assertOk();
    }
}
