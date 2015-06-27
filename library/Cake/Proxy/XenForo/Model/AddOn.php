<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AddOn extends \XenForo_Model_AddOn
    {
    }
}

class XenForo_Model_AddOn extends XFCP_XenForo_Model_AddOn
{

    protected $_installedModuleCache = array();

    public function importAddOnExtraDataFromXml(\SimpleXMLElement $xml, $addOnId)
    {
        $installedModules = $this->getInstalledModulesForAddOn($addOnId);
        
        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;
        
        foreach ($installedModules as $moduleName => $installedModule) {
            $filename = $libraryDir . $moduleName . DIRECTORY_SEPARATOR . 'module-' . $moduleName . '.xml';
            if (file_exists($filename)) {
                $appendXml = \XenForo_Helper_DevelopmentXml::scanFile($filename);
                foreach ($appendXml->children() as $child) {
                    $elementName = $child->getName();
                    foreach ($child->children() as $childChild) {
                        $childChild->addAttribute('module_name_cake', $moduleName);
                        \Cake\Helper_Xml::appendXml($xml->$elementName, $childChild);
                    }
                }
            }
        }
        
        parent::importAddOnExtraDataFromXml($xml, $addOnId);
    }

    public function deleteAddOnMasterData($addOnId)
    {
        parent::deleteAddOnMasterData($addOnId);
        
        $db = $this->_getDb();
        
        $db->query('
            DELETE FROM cake_module
            WHERE addon_id = ?
        ', $addOnId);
    }

    public function getAddOnsWithIdPrefix($prefix)
    {
        return $this->fetchAllKeyed(
            '
    			SELECT *
    			FROM xf_addon
                WHERE addon_id LIKE \'' . $prefix . '%\'
    			ORDER BY title
    		', 'addon_id');
    }

    public function prepareCakeAddOns(array $addOns)
    {
        foreach ($addOns as $addOnId => &$addOn) {
            if (strlen($addOnId) > 4 && substr($addOnId, 0, 4) == 'Cake') {
                $addOn['depth'] = 1;
                $addOn['title'] = new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId));
                $addOn['prefix'] = new \XenForo_Phrase('cake');
            }
            
            $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
            
            $modules = array();
            if ($installData) {
                $modules = $installData->getModules();
                if ($modules) {
                    $addOn['moduleCount'] = count($modules);
                    $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);
                    $addOn['activeModuleCount'] = count($activeModules);
                }
            }
        }
        
        return $addOns;
    }

    public function getInstalledModulesForAddOn($addOnId)
    {
        if (!isset($this->_installedModuleCache[$addOnId])) {
            $this->_installedModuleCache[$addOnId] = $this->fetchAllKeyed(
                '
                    SELECT *
                    FROM cake_module
                    WHERE addon_id = ?
                ', 'module_name', $addOnId);
        }
        
        return $this->_installedModuleCache[$addOnId];
    }

    public function installModule(array $addOn, $moduleName)
    {
        $db = $this->_getDb();
        
        $addOnId = $addOn['addon_id'];
        
        $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $moduleName);
        
        \XenForo_Db::beginTransaction($db);
        
        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;
        
        $filename = $libraryDir . 'addon-' . $addOnId . '.xml';
        $xml = \XenForo_Helper_DevelopmentXml::scanFile($filename);
        
        $module = array(
            'module_name' => $moduleName,
            'addon_id' => $addOnId,
            'version_id' => $installData::$versionId,
            'version_string' => $installData::$version
        );
        
        $installedModules = $this->getInstalledModulesForAddOn($addOnId);
        $this->_installedModuleCache[$addOnId][$moduleName] = $module;
        
        $this->importAddOnExtraDataFromXml($xml, $addOnId);
        
        $installData->install();
        
        $installData->installModule($addOnId, $moduleName);
        
        $this->enableModule($addOn, $moduleName);
        
        \XenForo_Db::commit($db);
        
        $this->rebuildAddOnCaches();
    }

    public function enableModule(array $addOn, $moduleName)
    {
        $addOnId = $addOn['addon_id'];
        
        $modules = \Cake\Proxy::getOptionValue('modules', $addOnId);
        
        $modules[$moduleName] = 1;
        
        $dw = \XenForo_DataWriter::create('XenForo_Datawriter_Option');
        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $dw->setExistingData($preOption . '_modules');
        $dw->set('option_value', $modules);
        $dw->save();
        
        \XenForo_Application::getOptions()->set($preOption . '_modules', $modules);
    }

    public function uninstallModule(array $addOn, $moduleName)
    {
        $db = $this->_getDb();
        
        $addOnId = $addOn['addon_id'];
        
        $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $moduleName);
        
        \XenForo_Db::beginTransaction($db);
        
        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;
        
        $filename = $libraryDir . 'addon-' . $addOnId . '.xml';
        $xml = \XenForo_Helper_DevelopmentXml::scanFile($filename);
        
        $installedModules = $this->getInstalledModulesForAddOn($addOnId);
        unset($this->_installedModuleCache[$addOnId][$moduleName]);
        
        $this->importAddOnExtraDataFromXml($xml, $addOnId);
        
        $installData->uninstall();
        
        $installData->uninstallModule($addOnId, $moduleName);
        
        $this->disableModule($addOn, $moduleName);
        
        \XenForo_Db::commit($db);
        
        $this->rebuildAddOnCaches();
    }

    public function disableModule(array $addOn, $moduleName)
    {
        $addOnId = $addOn['addon_id'];
        
        $modules = \Cake\Proxy::getOptionValue('modules', $addOnId);
        
        unset($modules[$moduleName]);
        
        $dw = \XenForo_DataWriter::create('XenForo_Datawriter_Option');
        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $dw->setExistingData($preOption . '_modules');
        $dw->set('option_value', $modules);
        $dw->save();
        
        \XenForo_Application::getOptions()->set($preOption . '_modules', $modules);
    }
}