<?php
namespace Cake;

class Proxy
{

    public static function loadClass($class, array &$extend)
    {
        $calledClass = get_called_class();

        if (strpos($calledClass, '\Proxy')) {
            $preClass = substr($calledClass, 0, -6);

            $parts = explode('\\', $preClass);
            if (count($parts) == 1) {
                $addOnId = $parts[0];
            } else {
                if (count($parts) == 3) {
                    $module = array_pop($parts);
                }
                $addOnId = implode('_', $parts);
            }

            $activeModules = self::getOptionValue('modules', $addOnId);

            if (!isset($module) || !empty($activeModules[$module])) {
                $extend[] = $preClass . '\\Proxy\\' . $class;
            }
        }
    }

    public static function getOptionValue($option, $namespace = null)
    {
        $xenOptions = \XenForo_Application::getOptions();

        if (!$namespace) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
            }
        }

        if ($namespace) {
            $preOption = str_replace('\\', '_', $namespace);
            $preOption = Helper_String::pascalCaseToCamelCase($preOption);
            $option = $preOption . '_' . $option;
        }

        return $xenOptions->$option;
    }

    protected function _dependencies(\XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        \XenForo_Template_Helper_Core::$helperCallbacks = array_merge(\XenForo_Template_Helper_Core::$helperCallbacks,
            array(
                'cake_copyright' => \XenForo_Template_Helper_Core::$helperCallbacks['copyright'],
                'copyright' => array(
                    'Cake\Application',
                    'getCopyrightHtml'
                )
            ));
    }

    public static function dependencies(\XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        $calledClass = get_called_class();

        if (self::isModuleDisabled($calledClass)) {
            return;
        }

        $class = self::create($calledClass);
        $class->_dependencies($dependencies, $data);
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
    public static function create($class)
    {
        $createClass = \XenForo_Application::resolveDynamicClass($class);
        if (!$createClass) {
            throw new \XenForo_Exception("Invalid proxy '$class' specified");
        }

        return new $createClass();
    }
}