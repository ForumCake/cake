<?php
namespace Cake;

trait Trait_Controller
{

    use Trait_Core;

    protected function _deleteData($dataWriterName, $existingDataKeyName, $redirectLink, $redirectMessage = null,
        array $dwOptions = array())
    {
        if (strpos($dataWriterName, '_') === false ||
             (strlen($dataWriterName) > 10 && substr($dataWriterName, 0, 11) == 'DataWriter_')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $dataWriterName = $namespace . '\\' . $dataWriterName;
            }
        }

        return parent::_deleteData($dataWriterName, $existingDataKeyName, $redirectLink, $redirectMessage, $dwOptions);
    }

    protected function _getToggleResponse(array $items, $dwName, $redirectTarget, $activeFieldName = 'active', $idPrefix = '')
    {
        if (strpos($dwName, '_') === false || (strlen($dwName) > 10 && substr($dwName, 0, 11) == 'DataWriter_')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $dwName = $namespace . '\\' . $dwName;
            }
        }

        return parent::_getToggleResponse($items, $dwName, $redirectTarget, $activeFieldName, $idPrefix);
    }

    public function getHelper($class)
    {
        if (strpos($class, '_') === false || (strlen($class) > 4 && substr($class, 0, 16) == 'ControllerHelper')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $class = $namespace . '\\' . $class;
            }
        }

        $class = \XenForo_Application::resolveDynamicClass($class);

        return new $class($this);
    }

    public function responseView($viewName = '', $templateName = '', array $params = array(), array $containerParams = array())
    {
        if (strpos($viewName, '_') === false || (strlen($viewName) > 4 && substr($viewName, 0, 4) == 'View')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $viewName = $namespace . '\\' . $viewName;
            }
        }

        return parent::responseView($viewName, $templateName, $params, $containerParams);
    }

    public function responseReroute($controllerName, $action, array $containerParams = array())
    {
        if (strpos($controllerName, '_') === false ||
             (strlen($controllerName) > 10 && substr($controllerName, 0, 10) == 'Controller')) {
            $calledClass = get_called_class();

            $backslash = strrpos($calledClass, '\\');
            if ($backslash !== false) {
                $namespace = substr($calledClass, 0, $backslash);
                $controllerName = $namespace . '\\' . $controllerName;
            }
        }

        return parent::responseReroute($controllerName, $action, $containerParams);
    }
}