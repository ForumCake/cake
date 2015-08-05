<?php
namespace Cake;

trait Trait_RoutePrefix
{

    use Trait_Core;

    public function getRouteMatch(\XenForo_Router $router, $controllerName = '', $action = false, $majorSection = '',
        $minorSection = '')
    {
        if (!$this->isModuleActive()) {
            return;
        }

        if (strpos($controllerName, '_') === false ||
             (strlen($controllerName) > 10 && substr($controllerName, 0, 10) == 'Controller')) {
            $controllerName = $this->_addNamespaceToClass($controllerName);
        }

        return $router->getRouteMatch($controllerName, $action, $majorSection, $minorSection);
    }
}