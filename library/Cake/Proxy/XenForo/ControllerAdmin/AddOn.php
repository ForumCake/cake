<?php
namespace Cake\Proxy;

use Cake\Proxy;
if (false) {

    class XFCP_XenForo_ControllerAdmin_AddOn extends \XenForo_ControllerAdmin_AddOn
    {
    }
}

class XenForo_ControllerAdmin_AddOn extends XFCP_XenForo_ControllerAdmin_AddOn
{

    public function actionIndex()
    {
        $response = parent::actionIndex();
        
        if ($response instanceof \XenForo_ControllerResponse_View) {
            
            $response->params['addOns'] = $this->_getAddOnModel()->prepareCakeAddOns($response->params['addOns']);
        }
        
        return $response;
    }

    public function actionModules()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
        
        $addOnModel = $this->_getAddOnModel();
        
        if (!$addOnId) {
            $addOns = $addOnModel->getAllAddOns();
            $addOns = $this->_getAddOnModel()->prepareCakeAddOns($addOns);
            
            $viewParams = array(
                'addOns' => $addOns
            );
            
            return $this->responseView('Cake\ViewAdmin_AddOn_Modules_List', 'cake_addon_modules_list', $viewParams);
        }
        
        $addOn = $this->_getAddOnOrError($addOnId);
        
        $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
        
        $modules = array();
        if ($installData) {
            $modules = $installData->getModules();
        }
        
        $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
        $activeModules = Proxy::getOptionValue('modules', $addOnId);
        
        $availableModules = array();
        foreach ($modules as $moduleName => $default) {
            $installed = isset($installedModules[$moduleName]);
            $module = array(
                'title' => new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $moduleName)),
                'description' => new \XenForo_Phrase(
                    \Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $moduleName) . '_desc'),
                'active' => !empty($activeModules[$moduleName])
            );
            if ($installed) {
                $installedModules[$moduleName] = $module;
            } else {
                $availableModules[$moduleName] = $module;
            }
        }
        
        $viewParams = array(
            'addOn' => $addOn,
            'installedModules' => $installedModules,
            'availableModules' => $availableModules
        );
        
        return $this->responseView('Cake\ViewAdmin_AddOn_Modules', 'cake_addon_modules', $viewParams);
    }

    public function actionModulesinstall()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
        
        $addOn = $this->_getAddOnOrError($addOnId);
        
        $moduleName = $this->_input->filterSingle('module_name', \XenForo_Input::STRING);
        
        if ($this->isConfirmedPost()) {
            $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
            
            $modules = array();
            if ($installData) {
                $modules = $installData->getModules();
            }
            
            if (!isset($modules[$moduleName])) {
                // TODO friendly error
                return $this->responseNoPermission();
            }
            
            $addOnModel = $this->_getAddOnModel();
            
            $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
            
            if (isset($installedModules[$moduleName])) {
                // TODO friendly error
                return $this->responseNoPermission();
            }
            
            $addOnModel->installModule($addOn, $moduleName);
            
            return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS, 
                \XenForo_Link::buildAdminLink('add-ons/modules', 
                    array(
                        'addon_id' => $addOnId
                    )));
        } else {
            $module = array(
                'module_name' => $moduleName,
                'title' => new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $moduleName))
            );
            
            $viewParams = array(
                'addOn' => $addOn,
                'module' => $module
            );
            
            return $this->responseView('ViewAdmin_AddOn_Modules_Install', 'cake_module_install', $viewParams);
        }
    }
    
    public function actionModulesuninstall()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
    
        $addOn = $this->_getAddOnOrError($addOnId);
    
        $moduleName = $this->_input->filterSingle('module_name', \XenForo_Input::STRING);
    
        if ($this->isConfirmedPost()) {
            $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
    
            $modules = array();
            if ($installData) {
                $modules = $installData->getModules();
            }
    
            if (!isset($modules[$moduleName])) {
                // TODO friendly error
                return $this->responseNoPermission();
            }
    
            $addOnModel = $this->_getAddOnModel();
    
            $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
    
            if (!isset($installedModules[$moduleName])) {
                // TODO friendly error
                return $this->responseNoPermission();
            }
    
            $addOnModel->uninstallModule($addOn, $moduleName);
    
            return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS,
                \XenForo_Link::buildAdminLink('add-ons/modules',
                    array(
                        'addon_id' => $addOnId
                    )));
        } else {
            $module = array(
                'module_name' => $moduleName,
                'title' => new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId . '_' . $moduleName))
            );
    
            $viewParams = array(
                'addOn' => $addOn,
                'module' => $module
            );
    
            return $this->responseView('ViewAdmin_AddOn_Modules_Uninstall', 'cake_module_uninstall', $viewParams);
        }
    }

    public function actionModulesreset()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);
        
        if ($this->isConfirmedPost()) {
            $dw = \XenForo_DataWriter::create('XenForo_Datawriter_Option');
            $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
            $dw->setExistingData($preOption . '_modules');
            $dw->set('option_value', $dw->get('default_value'));
            $dw->save();
            
            $this->_getAddOnModel()->rebuildAddOnCachesAfterActiveSwitch($addOn);
            
            return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS, 
                \XenForo_Link::buildAdminLink('add-ons/modules', 
                    array(
                        'addon_id' => $addOnId
                    )));
        }
        
        $viewParams = array(
            'addOn' => $addOn
        );
        
        return $this->responseView('Cake\ViewAdmin_AddOn_Modules_Reset', 'cake_addon_modules_reset', $viewParams);
    }

    public function actionModulesToggle()
    {
        $this->_assertPostOnly();
        
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);
        
        $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
        
        $modules = array();
        if ($installData) {
            $modules = $installData->getModules();
        }
        
        $idExists = $this->_input->filterSingle('exists', 
            array(
                \XenForo_Input::UINT,
                'array' => true
            ));
        $ids = $this->_input->filterSingle('id', 
            array(
                \XenForo_Input::UINT,
                'array' => true
            ));
        
        $dw = \XenForo_DataWriter::create('XenForo_Datawriter_Option');
        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $dw->setExistingData($preOption . '_modules');
        $dw->set('option_value', $ids);
        $dw->save();
        
        \XenForo_Application::getOptions()->set($preOption . '_modules', $ids);
        
        $this->_getAddOnModel()->rebuildAddOnCachesAfterActiveSwitch($addOn);
        
        return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS, 
            \XenForo_Link::buildAdminLink('add-ons/modules', 
                array(
                    'addon_id' => $addOnId
                )));
    }
}