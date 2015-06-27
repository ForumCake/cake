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
        
        self::_install($namespace);
    }

    protected static function _install($namespace)
    {
        $data = Install_DataAbstract::create($namespace . '\Install_Data');
        
        if ($data) {
            $tables = $data->getTables();
            
            if ($tables) {
                \Cake\Helper_MySql::createTables($tables);
            }
            
            $modules = $data->getModules();
            
            foreach ($modules as $moduleNamespace => $enabled) {
                if ($enabled) {
                    self::_install($namespace . '\\' . $moduleNamespace);
                }
            }
        }
    }
}