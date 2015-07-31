<?php
namespace Cake;

class FileHealthCheck
{

    public static function fileHealthCheck(\XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        self::getFileHashes($hashes);
    }

    public static function getFileHashes(array &$hashes = array())
    {
        $addOns = \XenForo_Application::get('addOns');

        foreach ($addOns as $addOnId => $versionString) {
            $namespace = str_replace('_', '\\', $addOnId);

            $modules = \Cake\Proxy::getOptionValue('modules', $addOnId);

            if (!is_array($modules)) {
                continue;
            }

            $addOnCheck = \Cake\Install_FileHealthCheck::create($namespace . '\\Install_FileHealthCheck');

            if ($addOnCheck instanceof Install_FileHealthCheckBase) {
                $hashes = array_merge($hashes, $addOnCheck->getFileHashes());

                $moduleNames = array_keys($modules);

                foreach ($moduleNames as $moduleName) {
                    $moduleCheck = \Cake\Install_FileHealthCheck::create(
                        $namespace . '\\' . $moduleName . '\\Install_FileHealthCheck');

                    if ($moduleCheck instanceof Install_FileHealthCheckBase) {
                        $hashes = array_merge($hashes, $addOnCheck->getFileHashes());
                    }
                }
            }
        }
    }
}