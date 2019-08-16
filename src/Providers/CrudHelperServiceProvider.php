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
        return $this->getNamespace().'\\'.Str::studly(Str::singular(request()->segment(2)));
    }

    /**
     * @return string
     */
    private function getNamespace()
    {
        return rtrim(config('crud-helper.namespace', 'App\\Models'), '\\');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::resolver(function ($translator, $data, $rules, $messages, $attributes) {
            return new CustomValidator($translator, $data, $rules, $messages, $attributes);
        });
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'technote');
        $this->publishes([
            __DIR__.'/../../config/crud-helper.php' => config_path('crud-helper.php'),
        ], 'config');
    }
}
