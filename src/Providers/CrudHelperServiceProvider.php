<?php

namespace Technote\CrudHelper\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;
use Technote\CrudHelper\Validators\CustomValidator;
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
    public function register()
    {
        $this->app->bind(CustomValidator::class);
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
    public static function getApiPrefixSegmentCount()
    {
        return count(explode('/', self::getApiPrefix()));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/crud-helper.php' => config_path('crud-helper.php'),
        ], 'config');

        Validator::resolver(function ($translator, $data, $rules, $messages, $attributes) {
            return new CustomValidator($translator, $data, $rules, $messages, $attributes);
        });
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'technote');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
