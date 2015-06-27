<?php
namespace Cake;

class Install_Controller
{

    /**
     *
     * @param array|boolean $existingAddOn
     * @param array $addOnData
     * @param \SimpleXMLElement $xml
     */
    public static function install($existingAddOn, array $addOnData, \SimpleXMLElement $xml)
    {
        if (\XenForo_Application::$versionId < 1030070) {
            // note: this can't be phrased
            throw new \XenForo_Exception('This add-on requires XenForo 1.3.0 or higher.', true);
        }
        
        if (PHP_VERSION_ID < 50400) {
            // note: this can't be phrased
            throw new \XenForo_Exception('This add-on requires PHP 5.4.0 or higher.', true);
        }
        
        $namespace = str_replace('_', '\\', $addOnData['addon_id']);
        
        self::_install($namespace, $existingAddOn, $addOnData, $xml);
    }

    protected static function _install($namespace, $existingAddOn, array $addOnData, \SimpleXMLElement $xml)
    {
        $db = \XenForo_Application::getDb();
        
        $installData = Install_DataAbstract::create($namespace . '\Install_Data');
        
        /* @var $addOnModel \XenForo_Model_AddOn */
        $addOnModel = \XenForo_Model::create('XenForo_Model_AddOn');
        
        if ($installData) {
            $installData->install();
            
            $addOnId = $addOnData['addon_id'];
            $addOnNamespace = str_replace('_', '\\', $addOnId);
            $moduleName = substr($namespace, strlen($addOnNamespace) + 1);
            
            if ($moduleName) {
                $installData->installModule($addOnId, $moduleName);
            } else {
                // TODO allow modules inside modules?
                
                if ($existingAddOn) {
                    $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
                }
                
                $modules = $installData->getModules();
                
                foreach ($modules as $moduleName => $enabled) {
                    if ($existingAddOn) {
                        if (!isset($installedModules[$moduleName])) {
                            continue;
                        }
                    }
                    if ($enabled) {
                        self::_install($namespace . '\\' . $moduleName, $existingAddOn, $addOnData, $xml);
                    }
                }
            }
        }
    }

    /**
     *
     * @param array|boolean $existingAddOn
     * @param array $addOnData
     * @param \SimpleXMLElement $xml
     */
    public static function uninstall(array $addOnData)
    {
        $namespace = str_replace('_', '\\', $addOnData['addon_id']);
        
        self::_uninstall($namespace, $addOnData);
    }

    protected static function _uninstall($namespace, array $addOnData)
    {
        $db = \XenForo_Application::getDb();
        
        $installData = Install_DataAbstract::create($namespace . '\Install_Data');
        
        /* @var $addOnModel \XenForo_Model_AddOn */
        $addOnModel = \XenForo_Model::create('XenForo_Model_AddOn');
        
        if ($installData) {
            $addOnId = $addOnData['addon_id'];
            $addOnNamespace = str_replace('_', '\\', $addOnId);
            $moduleName = substr($namespace, strlen($addOnNamespace) + 1);
            
            if (!$moduleName) {
                // TODO allow modules inside modules?
                
                $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
                
                $modules = $installData->getModules();
                
                foreach ($modules as $moduleName => $enabled) {
                    if (!isset($installedModules[$moduleName])) {
                        continue;
                    }
                    self::_uninstall($namespace . '\\' . $moduleName, $addOnData);
                }
            }
            
            $installData->uninstall();
        }
    }
}