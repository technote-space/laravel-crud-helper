<?php

namespace Technote\CrudHelper\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;
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
        $this->app->afterResolving(ModelInjectionable::class, function (ModelInjectionable $request) {
            $request->setTarget($this->segmentToModel());
        });
    }

    /**
     * @return string|Model
     */
    private function segmentToModel()
    {
        return self::getApiNamespace().'\\'.Str::studly(Str::singular(request()->segment(self::getApiPrefixSegmentCount() + 1)));
    }

    /**
     * @return string
     */
    public static function getApiPrefix(): string
    {
        return config('crud-helper.prefix', 'api');
    }

    /**
     * @return string
     */
    public static function getApiNamespace(): string
    {
        return trim(config('crud-helper.namespace', 'App\\Models'), '\\');
    }

    /**
     * @return array
     */
    public static function getApiMiddleware(): array
    {
        return config('crud-helper.middleware', ['api']);
    }

    /**
     * @return int
     */
    public static function getApiPrefixSegmentCount(): int
    {
        return count(explode('/', self::getApiPrefix()));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/crud-helper.php' => config_path('crud-helper.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'technote');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        Validator::extend('katakana', '\Technote\CrudHelper\Validators\CustomValidator@validateKatakana', trans('technote::validation.katakana'));
        Validator::extend('zip_code', '\Technote\CrudHelper\Validators\CustomValidator@validateZipCode', trans('technote::validation.zip_code'));
        Validator::extend('phone', '\Technote\CrudHelper\Validators\CustomValidator@validatePhone', trans('technote::validation.phone'));
    }
}
