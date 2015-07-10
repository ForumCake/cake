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
}