<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Services;

use Composer\Autoload\ClassLoader;
use Eloquent;
use File;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Str;
use Symfony\Component\Finder\SplFileInfo;
use Technote\CrudHelper\Models\Contracts\Crudable;

/**
 * Class Routes
 * @package Technote\CrudHelper\Services
 */
class RoutesHelper
{
    /** @var CrudOptions */
    protected $options;

    /**
     * RoutesHelper constructor.
     *
     * @param CrudOptions $options
     */
    public function __construct(CrudOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $autoloadFunctions
     * @param string $targetNamespace
     *
     * @return Collection
     */
    public function getCrudableClasses(array $autoloadFunctions, string $targetNamespace): Collection
    {
        return collect($autoloadFunctions)->filter(function ($function) {
            return is_array($function) && $function[0] instanceof ClassLoader;
        })->flatMap(function ($function) {
            /** @var ClassLoader $loader */
            $loader = $function[0];

            return $loader->getPrefixesPsr4();
        })->filter(function (/** @noinspection PhpUnusedParameterInspection */ $dirs, $namespace) use ($targetNamespace) {
            $namespace = trim(preg_quote($namespace), '\\');

            return preg_match("#\A{$namespace}#", $targetNamespace) > 0;
        })->flatMap(function ($dirs, $namespace) use ($targetNamespace) {
            $namespace = trim($namespace, '\\');
            $relative = str_replace('\\', DIRECTORY_SEPARATOR, trim(Str::replaceFirst($namespace, '', $targetNamespace), '\\'));

            return collect($dirs)->flatMap(function ($dir) use ($relative) {
                if (!is_dir($dir . DIRECTORY_SEPARATOR . $relative)) {
                    return [];
                }

                return File::files($dir . DIRECTORY_SEPARATOR . $relative);
            })->map(function (SplFileInfo $info) use ($targetNamespace) {
                return $targetNamespace . str_replace([DIRECTORY_SEPARATOR, '/'], '\\', $info->getRelativePath()) . '\\' . pathinfo($info->getFilename(), PATHINFO_FILENAME);
            });
        });
    }

    /**
     * @param Collection $classes
     * @param Router $router
     *
     * @return void
     */
    public function registerCrudableClasses(Collection $classes, Router $router): void
    {
        $classes->filter(function ($class) {
            return class_exists($class) && is_subclass_of($class, Crudable::class);
        })->each(function ($class) use ($router) {
            /** @var Crudable|Model|Eloquent $class */
            $router->apiResource($class::newModelInstance()->getTable(), '\Technote\CrudHelper\Http\Controllers\Api\CrudController');
        });
    }

    /**
     * @return string|Model
     */
    public function segmentToModel()
    {
        return $this->getApiNamespace() . '\\' . \Illuminate\Support\Str::studly(Str::singular(request()->segment($this->getApiPrefixSegmentCount() + 1)));
    }

    /**
     * @return string
     */
    public function getApiPrefix(): string
    {
        return $this->options->getPrefix();
    }

    /**
     * @return string
     */
    public function getApiNamespace(): string
    {
        return trim($this->options->getNamespace(), '\\');
    }

    /**
     * @return array
     */
    public function getApiMiddleware(): array
    {
        return $this->options->getMiddleware();
    }

    /**
     * @return int
     */
    public function getApiPrefixSegmentCount(): int
    {
        return count(explode('/', $this->getApiPrefix()));
    }
}
