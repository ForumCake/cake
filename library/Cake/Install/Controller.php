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

        self::_updateCodeEventListeners($addOnData['addon_id'], $xml);

        self::_install($namespace, $existingAddOn, $addOnData, $xml);
    }

    protected static function _updateCodeEventListeners($addOnId, \SimpleXMLElement $xml)
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

        if ($installData) {
            $addOnNamespace = str_replace('_', '\\', $addOnId);
            $moduleName = substr($namespace, strlen($addOnNamespace) + 1);

            if ($moduleName) {
                $installData->install();

                $installData->installModule($addOnId, $moduleName);
            } else {
                // TODO allow modules inside modules?

                if ($existingAddOn) {
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
            }

            $installData->uninstall();
        }
    }
}