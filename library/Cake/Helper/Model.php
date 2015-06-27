<?php
namespace Cake;

class Helper_Model
{

    /**
     *
     * @return \XenForo_Controller|boolean
     */
    public static function getController($controllerName)
    {
        if (\XenForo_Application::isRegistered('cakeControllers')) {
            $controllers = \XenForo_Application::get('cakeControllers');
            
            if (isset($controllers[$controllerName])) {
                return $controllers[$controllerName];
            }
        }
        
        return false;
    }
}