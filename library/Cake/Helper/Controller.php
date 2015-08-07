<?php
namespace Cake;

class Helper_Controller
{

    /**
     *
     * @param string $controllerName
     * @return \XenForo_Controller
     */
    public static function getController($controllerName)
    {
        if (\XenForo_Application::isRegistered('cakeControllers')) {
            $controllers = \XenForo_Application::get('cakeControllers');
        } else {
            $controllers = array();
        }

        if (isset($controllers[$controllerName])) {
            return $controllers[$controllerName];
        }
    }

    public static function setController($controllerName, \XenForo_Controller $controller)
    {
        if (\XenForo_Application::isRegistered('cakeControllers')) {
            $controllers = \XenForo_Application::get('cakeControllers');
        } else {
            $controllers = array();
        }

        $controllers[$controllerName] = $controller;

        \XenForo_Application::set('cakeControllers', $controllers);
    }
}