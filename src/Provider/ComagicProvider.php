<?php

namespace AtLab\Comagic\Provider;

use AtLab\Comagic\Api;
use Illuminate\Support\ServiceProvider;

class ComagicProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishesPackages();
    }

    private function publishesPackages(): void
    {
        $this->publishes([
            __DIR__.'/../Config/package_config.php' => config_path('comagic.php'),
        ], 'comagic-config');
    }

    /**
     * Register bindings in the container.
     */
    private function registerBindings(): void
    {
        $this->app->singleton(Api::class, function (){
            return new Api();
        });
        $this->app->alias(Api::class, 'comagic');
    }
    public function provides()
    {
        return [Api::class, 'comagic'];
    }
}
