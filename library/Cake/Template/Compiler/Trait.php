<?php
namespace Cake;

trait Template_Compiler_Trait
{

    public function addFunctionHandler($function, \XenForo_Template_Compiler_Function_Interface $handler)
    {
        $class = get_class($handler);
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if ($class != $createClass) {
            $this->_functionHandlers[strtolower($function)] = new $createClass();
            return $this;
        }
        
        return parent::addFunctionHandler($function, $handler);
    }

    public function addTagHandler($tag, \XenForo_Template_Compiler_Tag_Interface $handler)
    {
        $class = get_class($handler);
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if ($class != $createClass) {
            $this->_tagHandlers[strtolower($tag)] = new $createClass();
            return $this;
        }
        
        return parent::addTagHandler($tag, $handler);
    }
}