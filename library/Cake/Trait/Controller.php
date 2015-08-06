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
            $dataWriterName = $this->_addNamespaceToClass($dataWriterName);
        }

        return parent::_deleteData($dataWriterName, $existingDataKeyName, $redirectLink, $redirectMessage, $dwOptions);
    }

    protected function _getToggleResponse(array $items, $dwName, $redirectTarget, $activeFieldName = 'active', $idPrefix = '')
    {
        if (strpos($dwName, '_') === false || (strlen($dwName) > 10 && substr($dwName, 0, 11) == 'DataWriter_')) {
            $dwName = $this->_addNamespaceToClass($dwName);
        }

        return parent::_getToggleResponse($items, $dwName, $redirectTarget, $activeFieldName, $idPrefix);
    }

    public function getHelper($class)
    {
        if (strpos($class, '_') === false || (strlen($class) > 4 && substr($class, 0, 16) == 'ControllerHelper')) {
            $class = $this->_addNamespaceToClass($class);
        }

        $class = \XenForo_Application::resolveDynamicClass($class);

        return new $class($this);
    }

    public function responseView($viewName = '', $templateName = '', array $params = array(), array $containerParams = array())
    {
        if (strpos($viewName, '_') === false || (strlen($viewName) > 4 && substr($viewName, 0, 4) == 'View')) {
            $viewName = $this->_addNamespaceToClass($viewName);
        }

        return parent::responseView($viewName, $templateName, $params, $containerParams);
    }

    public function responseReroute($controllerName, $action, array $containerParams = array())
    {
        if (strpos($controllerName, '_') === false ||
             (strlen($controllerName) > 10 && substr($controllerName, 0, 10) == 'Controller')) {
            $controllerName = $this->_addNamespaceToClass($controllerName);
        }

        return parent::responseReroute($controllerName, $action, $containerParams);
    }
}