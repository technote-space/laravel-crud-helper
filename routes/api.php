<?php
use Illuminate\Database\Eloquent\Model;
use Technote\CrudHelper\Models\Contracts\Crudable;

$namespace = rtrim(config('crud-helper.namespace', 'App\\Models'), '\\');
collect(File::files(app_path(Str::replaceFirst(app()->getNamespace(), '', $namespace))))->map(function (\Symfony\Component\Finder\SplFileInfo $info) use ($namespace) {
    return $namespace.str_replace([DIRECTORY_SEPARATOR, '/'], '\\', $info->getRelativePath()).'\\'.pathinfo($info->getFilename(), PATHINFO_FILENAME);
})->filter(function ($class) {
    return class_exists($class) && is_subclass_of($class, Crudable::class);
})->map(function ($class) {
    /** @var Crudable|Model|Eloquent $class */
    Route::apiResource($class::newModelInstance()->getTable(), '\Technote\CrudHelper\Http\Controllers\Api\CrudController');
});
