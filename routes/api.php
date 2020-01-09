<?php

use Illuminate\Routing\Router;
use Technote\CrudHelper\Providers\CrudHelperServiceProvider;
use Technote\CrudHelper\Services\RoutesHelper;

Route::group([
    'prefix'     => CrudHelperServiceProvider::getApiPrefix(),
    'middleware' => CrudHelperServiceProvider::getApiMiddleware(),
], function (Router $router) {
    $helper  = new RoutesHelper();
    $classes = $helper->getCrudableClasses(spl_autoload_functions(), CrudHelperServiceProvider::getApiNamespace());
    $helper->registerCrudableClasses($classes, $router);
});
