<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_ControllerAdmin_Option extends \XenForo_ControllerAdmin_Option
    {
    }
}

class XenForo_ControllerAdmin_Option extends XFCP_XenForo_ControllerAdmin_Option
{

    public function actionList()
    {
        $response = parent::actionList();
        
        if ($response instanceof \XenForo_ControllerResponse_View) {
            $groupId = $this->_input->filterSingle('group_id', \XenForo_Input::STRING);
            $subgroupId = $this->_input->filterSingle('subgroup_id', \XenForo_Input::STRING);
            $moduleName = $this->_input->filterSingle('module_name', \XenForo_Input::STRING);
            
            $groupId = lcfirst($groupId);
            $subgroupId = lcfirst($subgroupId);
            $moduleName = lcfirst($moduleName);
            
            $addOnModel = $this->_getAddOnModel();
            
            $addOns = $addOnModel->getAddOnsWithIdPrefix(ucfirst($groupId));
            
            if ($addOns) {
                $addOns = $addOnModel->prepareCakeAddOns($addOns);
                
                $response->params['addOns'] = $addOns;
                
                if ($subgroupId) {
                    $addOnId = \Cake\Helper_String::camelCaseToPascalCase($groupId . '_' . $subgroupId);
                } else {
                    $addOnId = \Cake\Helper_String::camelCaseToPascalCase($groupId);
                }
                
                if ($subgroupId && !isset($addOns[$addOnId])) {
                    return $this->responseError(new \XenForo_Phrase('requested_option_group_not_found'), 404);
                }
                
                $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);
                if ($activeModules) {
                    foreach ($activeModules as $_moduleName => $active) {
                        $activeModules[$_moduleName] = array(
                            'module_name' => $_moduleName,
                            'title' => new \XenForo_Phrase(
                                \Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $_moduleName)),
                            'description' => new \XenForo_Phrase(
                                \Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $_moduleName) . '_desc'),
                            'option_count' => 0
                        );
                    }
                } else {
                    $activeModules = array();
                }
                
                if ($moduleName && !isset($activeModules[ucfirst($moduleName)])) {
                    return $this->responseError(new \XenForo_Phrase('requested_option_group_not_found'), 404);
                }
                
                $response->params['selectedAddOn'] = $addOnId;
                $response->params['selectedModuleName'] = ucfirst($moduleName);
                
                $preparedOptions = $response->params['preparedOptions'];
                
                foreach ($preparedOptions as $optionName => $preparedOption) {
                    $parts = explode('_', $optionName);
                    
                    $_moduleName = '';
                    if (count($parts) == 2) {
                        $_addOnId = ucfirst($parts[0]);
                        $_optionName = ucfirst($parts[1]);
                    } else {
                        $_addOnId = ucfirst($parts[0]) . '_' . ucfirst($parts[1]);
                        if (count($parts) == 3) {
                            $_optionName = ucfirst($parts[2]);
                        } else {
                            $_moduleName = ucfirst($parts[2]);
                            $_optionName = ucfirst($parts[3]);
                        }
                    }
                    
                    if ($_addOnId == $addOnId && $_moduleName && isset($activeModules[$_moduleName])) {
                        $activeModules[$_moduleName]['option_count']++;
                    }
                    
                    if ($_addOnId == $addOnId && $_moduleName == ucfirst($moduleName)) {
                        continue;
                    }
                    unset($preparedOptions[$optionName]);
                }
                
                foreach ($activeModules as $moduleName => $module) {
                    if (!$module['option_count']) {
                        unset($activeModules[$moduleName]);
                    }
                }
                
                $response->params['preparedOptions'] = $preparedOptions;
                
                $response->params['modules'] = $activeModules;
            }
        }
        
        return $response;
    }
}