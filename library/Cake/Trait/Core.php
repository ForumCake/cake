<?php
namespace Cake;

trait Trait_Core
{

    public function getOptionValue($option)
    {
        $xenOptions = \XenForo_Application::getOptions();
        
        $calledClass = get_called_class();
        
        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $preOption = str_replace('\\', '_', substr($calledClass, 0, $backslash));
            $preOption = Helper_String::pascalCaseToCamelCase($preOption);
            $option = $preOption . '_' . $option;
        }
        
        return $xenOptions->$option;
    }

    public function getModelFromCache($class)
    {
        if (strpos($class, '_') === false || (strlen($class) > 5 && substr($class, 6) == 'Model_')) {
            $calledClass = get_called_class();
            
            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $class = $namespace . '\\' . $class;
            }
        }
        
        return parent::getModelFromCache($class);
    }

    public function createDataWriter($class = 'DataWriter')
    {
        if (strpos($class, '_') === false || (strlen($class) > 10 && substr($class, 11) == 'DataWriter_')) {
            $calledClass = get_called_class();
            
            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $class = $namespace . '\\' . $class;
            }
        }
        
        return \XenForo_DataWriter::create($class);
    }
}