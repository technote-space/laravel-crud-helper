<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Technote\CrudHelper\Models\Contracts\Crudable;
use Technote\CrudHelper\Providers\CrudHelperServiceProvider;
use Composer\Autoload\ClassLoader;

Route::group([
    'prefix'     => CrudHelperServiceProvider::getApiPrefix(),
    'middleware' => CrudHelperServiceProvider::getApiMiddleware(),
], function (Router $router) {
    $targetNamespace = CrudHelperServiceProvider::getApiNamespace();
    collect(spl_autoload_functions())->filter(function ($function) {
        return is_array($function) && $function[0] instanceof ClassLoader;
    })->flatMap(function ($function) {
        /** @var ClassLoader $loader */
        $loader = $function[0];

        return $loader->getPrefixesPsr4();
    })->filter(function ($dirs, $namespace) use ($targetNamespace) {
        $namespace = trim(preg_quote($namespace), '\\');

        return preg_match("#\A{$namespace}#", $targetNamespace) > 0;
    })->flatMap(function ($dirs, $namespace) use ($targetNamespace) {
        $namespace = trim($namespace, '\\');
        $relative  = str_replace('\\', DIRECTORY_SEPARATOR, trim(Str::replaceFirst($namespace, '', $targetNamespace), '\\'));

        return collect($dirs)->flatMap(function ($dir) use ($relative) {
            if (! is_dir($dir.DIRECTORY_SEPARATOR.$relative)) {
                return [];
            }

            return File::files($dir.DIRECTORY_SEPARATOR.$relative);
        })->map(function (\Symfony\Component\Finder\SplFileInfo $info) use ($targetNamespace) {
            return $targetNamespace.str_replace([DIRECTORY_SEPARATOR, '/'], '\\', $info->getRelativePath()).'\\'.pathinfo($info->getFilename(), PATHINFO_FILENAME);
        })->filter(function ($class) {
            return class_exists($class) && is_subclass_of($class, Crudable::class);
        });
    })->each(function ($class) use ($router) {
        /** @var Crudable|Model|Eloquent $class */
        $router->apiResource($class::newModelInstance()->getTable(), '\Technote\CrudHelper\Http\Controllers\Api\CrudController');
    });
});
