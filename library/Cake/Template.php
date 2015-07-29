<?php
namespace Cake;

class Template
{

    /**
     * Constructor.
     * Use {@link create()} statically unless you know what you're doing.
     */
    public function __construct()
    {
    }

    public static function navigationTabs(&$extraTabs, $selectedTabId)
    {
        $calledClass = get_called_class();

        if (self::isModuleDisabled($calledClass)) {
            return;
        }

        $navigationTabs = self::create($calledClass, $extraTabs, $selectedTabId);
        $navigationTabs->_navigationTabs($extraTabs, $selectedTabId);
    }

    public static function isModuleDisabled($calledClass)
    {
        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $namespace = substr($calledClass, 0, $backslash);

            $namespaceParts = explode('\\', $namespace);
            $addOns = \XenForo_Application::get('addOns');

            $addOnId = '';
            $moduleName = '';
            while ($namespaceParts) {
                $_addOnId = implode('_', $namespaceParts);
                if (isset($addOns[$_addOnId])) {
                    $addOnId = $_addOnId;
                    break;
                }
                $moduleName = array_pop($namespaceParts);
            }

            if ($addOnId && $moduleName) {
                $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);

                if (empty($activeModules[$moduleName])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Factory method to get the named model.
     * The class must exist or be autoloadable
     * or an exception will be thrown.
     *
     * @param string Class to load
     *
     * @return Template
     */
    public static function create($class, $extraTabs, $selectedTabId)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if (!$createClass) {
            throw new \XenForo_Exception("Invalid template '$class' specified");
        }

        return new $createClass($extraTabs, $selectedTabId);
    }
}