<?php
namespace Cake;

class Install_Controller extends Install
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

        self::_preInstall($existingAddOn, $addOnData, $xml);

        self::_install($namespace, $existingAddOn, $addOnData, $xml);
    }

    protected static function _preInstall($existingAddOn, array $addOnData, \SimpleXMLElement $xml)
    {
        $addOnId = $addOnData['addon_id'];

        self::_preInstallAddOn($addOnId, $addOnData['version_id']);
        self::_preInstallOptions($addOnId, $xml->optiongroups);
        self::_preInstallCodeEventListeners($addOnId, $xml->code_event_listeners);

        $fileName = 'library/' . str_replace('_', '/', $addOnId) . '/addon-' . $addOnId . '.xml';

        try {
            $xmlCheck = \XenForo_Helper_DevelopmentXml::scanFile($fileName);
        } catch (\Exception $e) {
            throw new \XenForo_Exception(new \XenForo_Phrase('provided_file_was_not_valid_xml_file'), true);
        }

        if ($addOnData['addon_id'] != (string) $xmlCheck['addon_id'] ||
             $addOnData['version_id'] != (string) $xmlCheck['version_id'] ||
             $addOnData['version_string'] != (string) $xmlCheck['version_string']) {
            $error = new \XenForo_Phrase('cake_xml_file_does_not_match_uploaded_files');
            $error = $error->render(false);
            if (!$error) {
                $error = 'XML file does not match uploaded files.';
            }
            throw new \XenForo_Exception($error, true);
        }
    }

    protected static function _preInstallAddOn($addOnId, $versionId)
    {
        $addOns = \XenForo_Application::get('addOns');

        $addOns[$addOnId] = $versionId;

        \XenForo_Application::set('addOns', $addOns);
    }

    protected static function _preInstallOptions($addOnId, $xml)
    {
        $xenOptions = \XenForo_Application::getOptions();

        $options = array();
        $xmlOptions = \XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->option);
        foreach ($xmlOptions as $option) {
            $optionId = (string) $option['option_id'];
            if ($xenOptions->get($optionId) === null) {
                if ($option['data_type'] == 'array') {
                    $value = @unserialize((string) $option->default_value);
                } else {
                    $value = (string) $option->default_value;
                }
                $options[$optionId] = $value;
                \XenForo_Application::getOptions()->set($optionId, $value);
            }
        }
    }

    protected static function _preInstallCodeEventListeners($addOnId, $xml)
    {
        /* @var $codeEventModel XenForo_Model_CodeEvent */
        $codeEventModel = \XenForo_Model::create('XenForo_Model_CodeEvent');

        $codeEventModel->deleteEventListenersForAddOn($addOnId);

        $cache = $codeEventModel->getEventListenerArray();

        $xmlListeners = \XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->listener);
        foreach ($xmlListeners as $listener) {
            $hint = $listener['hint'] ? (string) $listener['hint'] : '_';
            $cache[(string) $listener['event_id']][$hint][] = array(
                (string) $listener['callback_class'],
                (string) $listener['callback_method']
            );
        }

        \XenForo_CodeEvent::setListeners($cache, false);
    }

    protected static function _install($namespace, $existingAddOn, array $addOnData, \SimpleXMLElement $xml)
    {
        $db = \XenForo_Application::getDb();

        $addOnId = $addOnData['addon_id'];

        $xenOptions = \XenForo_Application::getOptions();

        if ($addOnId == 'Cake') {
            foreach ($xml->optiongroups->option as $option) {
                /* @var $option SimpleXMLElement */
                if ($option['option_id'] == 'cake_currentXenForoVersionId') {
                    $option->default_value = $xenOptions->currentVersionId;
                }
            }
        }

        /* @var $addOnModel \XenForo_Model_AddOn */
        $addOnModel = \XenForo_Model::create('XenForo_Model_AddOn');

        $installData = Install_DataAbstract::create($namespace . '\Install_Data');

        if (!$installData) {
            throw new \XenForo_Exception(new \XenForo_Phrase('cake_install_data_is_missing'), true);
        }

        $addOnNamespace = str_replace('_', '\\', $addOnId);
        $moduleName = substr($namespace, strlen($addOnNamespace) + 1);

        if ($moduleName) {
            $installData->preInstall(false);

            $installData->install();

            $installData->installModule($addOnId, $moduleName);
        } else {
            // TODO allow modules inside modules?

            $installData->preInstall();

            if ($existingAddOn && method_exists($addOnModel, 'getInstalledModulesForAddOn')) {
                $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
            }

            $modules = $installData->getModules();

            if (!$modules && $addOnId != 'Cake') {
                throw new \XenForo_Exception(new \XenForo_Phrase('cake_you_must_upload_at_least_one_module'), true);
            }

            $installData->install();

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

        $addOnId = $addOnData['addon_id'];

        /* @var $addOnModel \XenForo_Model_AddOn */
        $addOnModel = \XenForo_Model::create('XenForo_Model_AddOn');

        $installData = Install_DataAbstract::create($namespace . '\Install_Data');

        if ($installData) {
            $addOnNamespace = str_replace('_', '\\', $addOnId);
            $moduleName = substr($namespace, strlen($addOnNamespace) + 1);

            if (!$moduleName) {
                $installData->preUninstall(false);

                if (!method_exists($addOnModel, 'getInstalledModulesForAddOn')) {
                    $error = new \XenForo_Phrase('cake_uninstallation_requires_the_cake_addon_to_be_enabled');
                    $error = $error->render(false);
                    if (!$error) {
                        $error = 'Uninstallation requires the Cake add-on to be installed.';
                    }
                    throw new \XenForo_Exception($error, true);
                }

                // TODO allow modules inside modules?

                $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);

                $modules = $installData->getModules();

                foreach ($modules as $moduleName => $enabled) {
                    if (!isset($installedModules[$moduleName])) {
                        continue;
                    }
                    self::_uninstall($namespace . '\\' . $moduleName, $addOnData);
                }
            } else {
                $installData->preUninstall(false);
            }

            $installData->uninstall();
        }
    }
}