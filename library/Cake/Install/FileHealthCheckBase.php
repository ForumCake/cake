<?php
namespace Cake;

class Install_FileHealthCheckBase extends Install
{

    public function getFileHashes()
    {
        $class = get_called_class();

        return array(
            $class => ''
        );
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
        // TODO add $fakeBase support
        $createClass = \XenForo_Application::resolveDynamicClass($class, '');
        if (!$createClass) {
            $createClass = '\\' . $class;
            $nsSplit = strrpos($class, '\\');
            $ns = substr($class, 0, $nsSplit);
            $namespaceEval = "namespace $ns; ";
            $class = substr($class, $nsSplit + 1);

            eval($namespaceEval . 'class ' . $class . ' extends \Cake\Install_FileHealthCheckBase {}');
        }

        return new $createClass();
    }
}