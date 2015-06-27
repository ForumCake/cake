<?php
namespace Cake;

abstract class Install_DataAbstract
{

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

    public function getTables()
    {
        return array();
    }

    public function getTableChanges()
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
                    $addOnId = 'Cake_' . $value;
                    if (file_exists($path . $value . DIRECTORY_SEPARATOR . 'addon-' . $addOnId . '.xml')) {
                        continue;
                    }
                }
                if (in_array($value, $nonModules)) {
                    continue;
                }
                $modules[$value] = true;
            }
        }
        
        return $modules;
    }
}