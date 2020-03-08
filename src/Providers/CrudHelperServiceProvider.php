<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;
use Technote\CrudHelper\Services\CrudOptions;
use Technote\CrudHelper\Services\RoutesHelper;
use Validator;

/**
 * Class CrudHelperServiceProvider
 * @package Technote\CrudHelper\Providers
 */
class CrudHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CrudOptions::class, function (Application $app) {
            return new CrudOptions($app['config']['crud-helper']);
        });
        $this->app->bind(RoutesHelper::class, function (Application $app) {
            return new RoutesHelper($app->make(CrudOptions::class));
        });

        $this->app->afterResolving(ModelInjectionable::class, function (ModelInjectionable $request) {
            $request->setTarget($this->app->make(RoutesHelper::class)->segmentToModel());
        });

        $this->mergeConfigFrom(__DIR__.'/../../config/crud-helper.php', 'crud-helper');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'technote');
    }

    /**
     * @param  CrudHelperRouteRegistrar  $router
     */
    public function boot(CrudHelperRouteRegistrar $router): void
    {
        if (! $this->app->routesAreCached()) {
            $router->register();
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/crud-helper.php' => $this->app->configPath('crud-helper.php'),
            ], 'crud-helper');
        }

        Validator::extend('katakana', '\Technote\CrudHelper\Validators\CustomValidator@validateKatakana', trans('technote::validation.katakana'));
        Validator::extend('zip_code', '\Technote\CrudHelper\Validators\CustomValidator@validateZipCode', trans('technote::validation.zip_code'));
        Validator::extend('phone', '\Technote\CrudHelper\Validators\CustomValidator@validatePhone', trans('technote::validation.phone'));
        Validator::extend('time', '\Technote\CrudHelper\Validators\CustomValidator@validateTime', trans('technote::validation.time'));
    }
}
