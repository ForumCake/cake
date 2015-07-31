<?php
namespace Cake;

class Install_FileHealthCheckBase
{

    public function getFileHashes()
    {
        return array();
    }

    /**
     * Factory method to get the named file health check.
     * The class must exist or be autoloadable or an exception will be thrown.
     *
     * @param string Class to load
     *
     * @return Install_FileHealthCheckBase
     */
    public static function create($class)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class, '', 'Cake\Install_FileHealthCheckBase');
        if (!$createClass) {
            $createClass = 'Cake\Install_FileHealthCheckBase';
        }

        return new $createClass();
    }
}