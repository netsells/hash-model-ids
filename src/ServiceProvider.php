<?php

namespace Netsells\HashModelIds;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/hash-model-ids.php',
            'hash-model-ids'
        );

        $this->app->singleton(ModelIdHasherInterface::class, function ($app) {
            return ! $this->shouldHashIds($app)
                ? new ModelIdPrefixer(config('hash-model-ids.prefix', ModelIdPrefixer::DEFAULT_PREFIX))
                : new ModelIdHasher([
                    'salt' => config('hash-model-ids.salt'),
                    'min_hash_length' => config('hash-model-ids.min_hash_length'),
                    'alphabet' => config('hash-model-ids.alphabet'),
                ]);
        });
    }

    private function shouldHashIds(Application $app): bool
    {
        return $app->isProduction() || config('hash-model-ids.enabled', true);
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'hashModelIds');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/hash-model-ids.php' => config_path('hash-model-ids.php'),
            ], 'hash-model-ids-config');

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/hashModelIds'),
            ], 'hash-model-ids-lang');
        }
    }
}
