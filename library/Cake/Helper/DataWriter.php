<?php
namespace Cake;

class Helper_DataWriter
{

    /**
     *
     * @return \XenForo_Input|boolean
     */
    public static function getInput($controllerName)
    {
        if (\XenForo_Application::isRegistered('cakeControllers')) {
            $controllers = \XenForo_Application::get('cakeControllers');

            if (isset($controllers[$controllerName])) {
                return $controllers[$controllerName]->getInput();
            }
        }

        return false;
    }

    public static function setIfShown(\XenForo_DataWriter $dw, $cakeInput, $variableName, $filterData = null, array $options = array())
    {
        if (is_string($cakeInput)) {
            $cakeInput = self::getInput($cakeInput);
        }

        if ($cakeInput instanceof \XenForo_Input) {
            if (!$filterData) {
                $filterData = self::getFilterDataForField($dw, $variableName);
            }
            $input = $cakeInput->filterSingle($variableName, $filterData, $options);
            $inputShown = $cakeInput->filterSingle($variableName . '_shown', \XenForo_Input::BOOLEAN, $options);

            if ($inputShown) {
                $dw->set($variableName, $input);
                return true;
            }
        }

        return false;
    }

    public static function getFilterDataForField(\XenForo_DataWriter $dw, $field)
    {
        if (is_string($field)) {
            $tables = $dw->getFields();
            foreach ($tables as $table => $fields) {
                if (isset($fields[$field]) && is_array($fields[$field])) {
                    $field = $fields[$field];
                }
            }
        }

        if (is_array($field)) {
            if ($field['type'] == 'serialized' && $field['type'] == 'json') {
                return \XenForo_Input::ARRAY_SIMPLE;
            } else {
                return $field['type'];
            }
        }

        return false;
    }
}