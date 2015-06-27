<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Route_PrefixAdmin_Options extends \XenForo_Route_PrefixAdmin_Options
    {
    }
}

class XenForo_Route_PrefixAdmin_Options extends XFCP_XenForo_Route_PrefixAdmin_Options
{

    public function match($routePath, \Zend_Controller_Request_Http $request, \XenForo_Router $router)
    {
        if (strpos($routePath, '/') !== false) {
            $parts = explode('/', $routePath);
            if (strpos($parts[0], '-option') === false && count($parts) > 2) {
                $request->setParam('subgroup_id', $parts[2]);
                if (count($parts) > 3) {
                    $request->setParam('module_name', $parts[3]);
                }
            }
        }
        
        return parent::match($routePath, $request, $router);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (is_array($data)) {
            \XenForo_Link::prepareExtensionAndAction($extension, $action);
            
            if (strpos($action, '-option') === false) {
                if (!isset($data['group_id']) && isset($data['addon_id'])) {
                    $addOnIdParts = explode('_', $data['addon_id'], 2);
                    foreach ($addOnIdParts as &$addOnPart) {
                        $addOnPart = lcfirst($addOnPart);
                    }
                    $groupId = implode('/', $addOnIdParts);
                    
                    if (isset($extraParams['module_name'])) {
                        $moduleName = lcfirst($extraParams['module_name']);
                        unset($extraParams['module_name']);
                        return "$outputPrefix/$action/$groupId/$moduleName$extension";
                    }
                    return "$outputPrefix/$action/$groupId$extension";
                }
            }
        }
        
        return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
    }
}