<?php
namespace Cake;

class Template_Compiler
{

    public static function create($class, $text)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if (!$createClass) {
            throw new \XenForo_Exception("Invalid compiler '$class' specified");
        }

        return new $createClass($text);
    }
}