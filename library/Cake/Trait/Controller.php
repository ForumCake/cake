<?php
namespace Cake;

trait Trait_Controller
{
    
    use Trait_Core;
    
    public function responseView($viewName = '', $templateName = '', array $params = array(), array $containerParams = array())
    {
        if (strpos($viewName, '_') === false || (strlen($viewName) > 4 && substr($viewName, 4) == 'View')) {
            $calledClass = get_called_class();
            
            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $viewName = $namespace . '\\' . $viewName;
            }
        }
        
        return parent::responseView($viewName, $templateName, $params, $containerParams);
    }

    public function responseReroute($controllerName, $action, array $containerParams = array())
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
        
        return parent::responseReroute($controllerName, $action, $containerParams);
    }
}