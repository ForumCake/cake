<?php
namespace Cake;

class Helper_Controller
{
    
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