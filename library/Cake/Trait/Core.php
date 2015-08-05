<?php
namespace Cake;

trait Trait_Core
{

    public function getOptionValue($option)
    {
        $xenOptions = \XenForo_Application::getOptions();

        $calledClass = get_called_class();

        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $preOption = str_replace('\\', '_', substr($calledClass, 0, $backslash));
            $preOption = Helper_String::pascalCaseToCamelCase($preOption);
            $option = $preOption . '_' . $option;
        }

        return $xenOptions->$option;
    }

    protected function _addNamespaceToClass($class)
    {
        $calledClass = get_called_class();

        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $namespace = substr($calledClass, 0, $backslash);
            return $namespace . '\\' . $class;
        }

        return $class;
    }

    public function getModelFromCache($class)
    {
        if ((strpos($class, '\\') === false && strpos($class, '_') === false) ||
             (strlen($class) > 5 && substr($class, 6) == 'Model_')) {
            $class = $this->_addNamespaceToClass($class);
        }

        return parent::getModelFromCache($class);
    }

    public function createDataWriter($class = 'DataWriter')
    {
        if (strpos($class, '_') === false || (strlen($class) > 10 && substr($class, 0, 11) == 'DataWriter_')) {
            $class = $this->_addNamespaceToClass($class);
        }

        return \XenForo_DataWriter::create($class);
    }

    public function isModuleActive($module = null)
    {
        $calledClass = get_called_class();

        $backslash = strrpos($calledClass, '\\');
        if ($backslash !== false) {
            $namespace = substr($calledClass, 0, $backslash);

            $namespaceParts = explode('\\', $namespace);
            $addOns = \XenForo_Application::get('addOns');

            $addOnId = '';
            while ($namespaceParts) {
                $_addOnId = implode('_', $namespaceParts);
                if (isset($addOns[$_addOnId])) {
                    $addOnId = $_addOnId;
                    break;
                }
                $pop = array_pop($namespaceParts);
                if ($module === null) {
                    $module = $pop;
                }
            }

            if ($addOnId) {
                $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);

                if ($module && !empty($activeModules[$module])) {
                    return true;
                }
            }
        }

        return false;
    }
}