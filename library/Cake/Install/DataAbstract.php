<?php
namespace Cake;

abstract class Install_DataAbstract
{

    /**
     * Factory method to get the named install data.
     * Returns false if the class does not exist.
     *
     * @param string Class to load
     *
     * @return DataAbstract|boolean
     */
    public static function create($class)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if (!$createClass) {
            return false;
        }
        
        return new $createClass();
    }

    public function getTables()
    {
        return array();
    }

    public function getModules()
    {
        return array();
    }
}