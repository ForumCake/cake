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

    public function getAllAddOns()
    {
        $addOns = parent::getAllAddOns();

        return $this->prepareCakeAddOns($addOns);
    }

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
                    foreach ($child->children() as $grandChild) {
                        if (!$grandChild->attributes()->count()) {
                            foreach ($grandChild->children() as $greatGrandChild) {
                                $greatGrandChild->addAttribute('module_name_cake', $moduleName);
                            }
                        } else {
                            $grandChild->addAttribute('module_name_cake', $moduleName);
                        }
                        \Cake\Helper_Xml::appendXml($xml->$elementName, $grandChild);
                    }
                }
            }
        }

        parent::importAddOnExtraDataFromXml($xml, $addOnId);
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

    public function prepareCakeAddOns(array $addOns, $prefix = false, $count = false)
    {
        foreach ($addOns as $addOnId => &$addOn) {
            $addOn = $this->prepareCakeAddOn($addOn, $prefix);

            if ($count) {
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
        }

        return $addOns;
    }

    public function prepareCakeAddOn(array $addOn, $prefix = false)
    {
        $addOnId = $addOn['addon_id'];

        if (strlen($addOnId) > 4 && substr($addOnId, 0, 4) == 'Cake') {
            $addOn['title'] = new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId));
            $addOn['depth'] = 1;
            if ($prefix) {
                $addOn['prefix'] = new \XenForo_Phrase('cake');
            } else {
                $addOn['title'] = new \XenForo_Phrase('cake_addon_title_x',
                    array(
                        'title' => $addOn['title']
                    ));
            }
        }

        return $addOn;
    }

    public function getInstalledModulesForAddOns(array $addOnIds)
    {
        $fetchAddOnIds = array();
        foreach ($addOnIds as $addOnId) {
            if (!isset($this->_installedModuleCache[$addOnId])) {
                $fetchAddOnIds[] = $addOnId;
            }
        }
        if ($fetchAddOnIds) {
            $fetchedModules = $this->_getDb()->fetchAll(
                '
                    SELECT *
                    FROM cake_module
                    WHERE addon_id IN (' .
                     $this->_getDb()
                        ->quote($fetchAddOnIds) . ')
                ');
            foreach ($fetchAddOnIds as $addOnId) {
                $this->_installedModuleCache[$addOnId] = array();
            }
            foreach ($fetchedModules as $module) {
                $this->_installedModuleCache[$module['addon_id']][$module['module_name']] = $module;
            }
        }

        $installedModules = array();
        foreach ($addOnIds as $addOnId) {
            $installedModules[$addOnId] = $this->_installedModuleCache[$addOnId];
        }

        return $installedModules;
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

    public function getCakeAddOns()
    {
        return $this->_getDb()->fetchCol(
            '
                SELECT DISTINCT(addon_id)
                FROM cake_module
            ');
    }

    public function rebuildModulesForAddOn(array $addOn)
    {
        $db = $this->_getDb();

        $addOnId = $addOn['addon_id'];

        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;

        $installedModules = $this->getInstalledModulesForAddOn($addOnId);

        foreach ($installedModules as $installedModule) {
            $moduleName = $installedModule['module_name'];

            $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $moduleName);

            $installData->preInstall();

            \XenForo_Db::beginTransaction($db);

            $installData->install();

            \XenForo_Db::commit($db);

            $installData->installModule($addOnId, $moduleName);

            $installData->postInstall();
        }

        $filename = $libraryDir . 'addon-' . $addOnId . '.xml';
        if (file_exists($filename)) {
            $xml = \XenForo_Helper_DevelopmentXml::scanFile($filename);

            $this->importAddOnExtraDataFromXml($xml, $addOnId);

            $this->rebuildAddOnCaches();
        } else {
            $this->rebuildAddOnCachesAfterActiveSwitch($addOn);
        }
    }

    public function installModule(array $addOn, $moduleName)
    {
        $db = $this->_getDb();

        $addOnId = $addOn['addon_id'];

        $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $moduleName);

        $installData->preInstall();

        \XenForo_Db::beginTransaction($db);

        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;

        $rebuildAddOnCaches = false;

        $filename = $libraryDir . 'addon-' . $addOnId . '.xml';
        $moduleFilename = $libraryDir . $moduleName . DIRECTORY_SEPARATOR . 'module-' . $moduleName . '.xml';
        if (file_exists($moduleFilename) && file_exists($filename)) {
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

            $rebuildAddOnCaches = true;
        }

        $installData->install();

        $installData->installModule($addOnId, $moduleName);

        $this->enableModule($addOn, $moduleName);

        \XenForo_Db::commit($db);

        if ($rebuildAddOnCaches) {
            $this->rebuildAddOnCaches();
        } else {
            $this->rebuildAddOnCachesAfterActiveSwitch($addOn);
        }

        $installData->postInstall();
    }

    public function enableModule(array $addOn, $moduleName)
    {
        $addOnId = $addOn['addon_id'];

        $modules = \Cake\Proxy::getOptionValue('modules', $addOnId);

        if (!is_array($modules)) {
            return false;
        }

        $modules[$moduleName] = 1;

        $dw = \XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $dw->setExistingData($preOption . '_modules');
        $dw->set('option_value', $modules);
        $dw->save();

        \XenForo_Application::getOptions()->set($preOption . '_modules', $modules);

        return true;
    }

    public function uninstallModule(array $addOn, $moduleName)
    {
        $db = $this->_getDb();

        $addOnId = $addOn['addon_id'];

        $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $moduleName);

        $installData->preUninstall();

        \XenForo_Db::beginTransaction($db);

        $libraryDir = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR;
        $libraryDir .= str_replace('_', DIRECTORY_SEPARATOR, $addOnId) . DIRECTORY_SEPARATOR;

        $rebuildAddOnCaches = false;

        $filename = $libraryDir . 'addon-' . $addOnId . '.xml';
        $moduleFilename = $libraryDir . $moduleName . DIRECTORY_SEPARATOR . 'module-' . $moduleName . '.xml';
        if (file_exists($moduleFilename) && file_exists($filename)) {
            $xml = \XenForo_Helper_DevelopmentXml::scanFile($filename);

            $installedModules = $this->getInstalledModulesForAddOn($addOnId);
            unset($this->_installedModuleCache[$addOnId][$moduleName]);

            $this->importAddOnExtraDataFromXml($xml, $addOnId);

            $rebuildAddOnCaches = true;
        }

        $installData->uninstall();

        $installData->uninstallModule($addOnId, $moduleName);

        $this->disableModule($addOn, $moduleName);

        \XenForo_Db::commit($db);

        if ($rebuildAddOnCaches) {
            $this->rebuildAddOnCaches();
        } else {
            $this->rebuildAddOnCachesAfterActiveSwitch($addOn);
        }

        $installData->postUninstall();
    }

    public function disableModule(array $addOn, $moduleName)
    {
        $addOnId = $addOn['addon_id'];

        $modules = \Cake\Proxy::getOptionValue('modules', $addOnId);

        unset($modules[$moduleName]);

        $dw = \XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $dw->setExistingData($preOption . '_modules');
        $dw->set('option_value', $modules);
        $dw->save();

        \XenForo_Application::getOptions()->set($preOption . '_modules', $modules);
    }
}