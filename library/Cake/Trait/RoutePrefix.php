<?php
namespace Cake;

trait Trait_RoutePrefix
{

    use Trait_Core;

    public function getRouteMatch(\XenForo_Router $router, $controllerName = '', $action = false, $majorSection = '', $minorSection = '')
    {
        if (strpos($controllerName, '_') === false ||
             (strlen($controllerName) > 10 && substr($controllerName, 10) == 'Controller')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $controllerName = $namespace . '\\' . $controllerName;
            }
        }

        return $router->getRouteMatch($controllerName, $action, $majorSection, $minorSection);
    }
}