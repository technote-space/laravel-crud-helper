<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Providers;

use Illuminate\Routing\Router;
use Illuminate\Contracts\Routing\Registrar;
use Technote\CrudHelper\Services\CrudOptions;
use Technote\CrudHelper\Services\RoutesHelper;

class CrudHelperRouteRegistrar
{
    /** @var CrudOptions $options */
    protected $options;

    /** @var RoutesHelper $helper */
    protected $helper;

    /** @var Registrar */
    protected $router;

    /**
     * CrudHelperRouteRegistrar constructor.
     *
     * @param CrudOptions $options
     * @param Registrar $router
     */
    public function __construct(CrudOptions $options, RoutesHelper $helper, Registrar $router)
    {
        $this->options = $options;
        $this->helper = $helper;
        $this->router = $router;
    }

    /**
     * register
     */
    public function register()
    {
        $this->router->group([
            'prefix' => $this->helper->getApiPrefix(),
            'middleware' => $this->helper->getApiMiddleware(),
        ], function (Router $router) {
            $classes = $this->helper->getCrudableClasses(spl_autoload_functions(), $this->helper->getApiNamespace());
            $this->helper->registerCrudableClasses($classes, $router);
        });
    }
}
