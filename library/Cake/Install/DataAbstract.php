<?php
namespace Cake;

abstract class Install_DataAbstract
{

    public static $version = '';

    public static $versionId = 0;

    /**
     * Factory method to get the named install data.
     * Returns false if the class does not exist.
     *
     * @param string Class to load
     *
     * @return Install_DataAbstract|boolean
     */
    public static function create($class)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if (!$createClass) {
            return false;
        }

        return new $createClass();
    }

    /**
     * Factory method to get the named install data.
     * Returns false if the class does not exist.
     *
     * @param string $addOnId
     *
     * @return Install_DataAbstract|boolean
     */
    public static function createForAddOnId($addOnId)
    {
        $namespace = str_replace('_', '\\', $addOnId);
        $installData = self::create($namespace . '\\Install_Data');

        if ($installData instanceof self) {
            return $installData;
        }

        return false;
    }

    /**
     * Factory method to get the named install data.
     * Returns false if the class does not exist.
     *
     * @param string $addOnId
     * @param string $module
     *
     * @return Install_DataAbstract|boolean
     */
    public static function createForModule($addOnId, $module)
    {
        $namespace = str_replace('_', '\\', $addOnId) . '\\' . $module;
        $installData = self::create($namespace . '\\Install_Data');

        if ($installData instanceof self) {
            return $installData;
        }

        return false;
    }

    public function install()
    {
        $tables = $this->getTables();

        if ($tables) {
            \Cake\Helper_MySql::createTables($tables);
        }

        $tableChanges = $this->getTableChanges();

        if ($tableChanges) {
            \Cake\Helper_MySql::makeTableChanges($tableChanges);
        }

        $primaryKeys = $this->getPrimaryKeys();

        if ($primaryKeys) {
            \Cake\Helper_Mysql::addPrimaryKeys($primaryKeys);
        }
    }

    public function uninstall()
    {
        $tables = $this->getTables();

        if ($tables) {
            \Cake\Helper_MySql::dropTables($tables);
        }

        $tableChanges = $this->getTableChanges();

        if ($tableChanges) {
            \Cake\Helper_MySql::undoTableChanges($tableChanges);
        }
    }

    public function installModule($addOnId, $moduleName)
    {
        $db = \XenForo_Application::getDb();

        $db->query(
            '
                INSERT INTO cake_module
                (module_name, addon_id, version_id, version_string)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE version_id = VALUES(version_id),
                    version_string = VALUES(version_string)
            ',
            array(
                $moduleName,
                $addOnId,
                $this::$versionId,
                $this::$version
            ));
    }

    public function uninstallModule($addOnId, $moduleName)
    {
        $db = \XenForo_Application::getDb();

        $db->query(
            '
                DELETE FROM cake_module
                WHERE module_name = ? AND addon_id = ?
            ', array(
                $moduleName,
                $addOnId
            ));
    }

    public function getTables()
    {
        return array();
    }

    public function getTableChanges()
    {
        return array();
    }

    public function getPrimaryKeys()
    {
        return array();
    }

    public function getModules()
    {
        $calledClass = get_called_class();

        $nonModules = $this->getNonModuleDirs();

        $modules = array();
        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $namespace = substr($calledClass, 0, $backslash);
            $addOnId = str_replace('\\', '_', $namespace);
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $path = \XenForo_Autoloader::getInstance()->getRootDir() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
            $weeds = array(
                '.',
                '..'
            );
            $directories = array_diff(scandir($path), $weeds);

            foreach ($directories as $value) {
                if (!is_dir($path . $value)) {
                    continue;
                }
                if ($namespace == 'Cake') {
                    if (file_exists($path . $value . DIRECTORY_SEPARATOR . 'addon-Cake_' . $value . '.xml')) {
                        continue;
                    }
                }
                if (in_array($value, $nonModules)) {
                    continue;
                }
                $installData = \Cake\Install_DataAbstract::createForModule($addOnId, $value);
                if (!$installData) {
                    continue;
                }
                $modules[$value] = $installData::$versionId;
            }
        }

        return $modules;
    }

    public function getNonModuleDirs()
    {
        return array_unique(
            array_merge($this->_getNonModuleDirs(),
                array(
                    'AdminSearchHandler',
                    'AlertHandler',
                    'AttachmentHandler',
                    'Authentication',
                    'BbCode',
                    'CacheRebuilder',
                    'Captcha',
                    'ContentPermission',
                    'ControllerAdmin',
                    'ControllerHelper',
                    'ControllerPublic',
                    'ControllerResponse',
                    'CronEntry',
                    'DataWriter',
                    'Deferred',
                    'Dependencies',
                    'Discussion',
                    'DiscussionMessage',
                    'EditHistoryHandler',
                    'Helper',
                    'Html',
                    'Image',
                    'Importer',
                    'Install',
                    'LikeHandler',
                    'Model',
                    'ModerationQueueHandler',
                    'ModeratorHandler',
                    'ModeratorLogHandler',
                    'NewsFeedHandler',
                    'Option',
                    'Proxy',
                    'ReportHandler',
                    'Route',
                    'SabreDav',
                    'Search',
                    'SitemapHandler',
                    'SpamHandler',
                    'StatsHandler',
                    'Template',
                    'Trait',
                    'UserUpgradeProcessor',
                    'ViewAdmin',
                    'ViewPublic',
                    'ViewRenderer',
                    'WarningHandler'
                )));
    }

    /**
     * Method designed to be overridden by child classes to add pre-save
     * behaviors.
     */
    protected function _getNonModuleDirs()
    {
        return array();
    }
}