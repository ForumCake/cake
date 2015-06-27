<?php
namespace Cake;

trait Trait_Model
{

    public function getOption($option)
    {
        $xenOptions = \XenForo_Application::getOptions();
        
        $calledClass = get_called_class();
        
        $proxy = strpos($calledClass, '_Proxy');
        if ($proxy !== false) {
            $preOption = substr($calledClass, 0, $proxy);
            $preOption = Helper_String::pascalCaseToCamelCase($preOption);
            $option = $preOption . '_' . $option;
        }
        
        return $xenOptions->$option;
    }
}