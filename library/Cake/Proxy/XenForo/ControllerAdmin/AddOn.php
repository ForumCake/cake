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

            $response->params['addOns'] = $this->_getAddOnModel()->prepareCakeAddOns($response->params['addOns'], true,
                true);
        }

        return $response;
    }

    public function actionModules()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);

        $addOnModel = $this->_getAddOnModel();

        if ($addOnId) {
            $addOn = $this->_getAddOnOrError($addOnId);

            $viewParams = $this->_getModulesForAddOn($addOn);

            return $this->responseView('Cake\ViewAdmin_AddOn_Modules', 'cake_addon_modules', $viewParams);
        }

        $addOns = $addOnModel->getAllAddOns();

        $moduleCount = 0;

        foreach ($addOns as $addOnId => $addOn) {
            $modules = $this->_getModulesForAddOn($addOn);

            $installedModules[$addOnId] = $modules['installedModules'];

            $moduleCount += count($modules['installedModules']);
        }

        $viewParams = array(
            'addOns' => $addOns,
            'installedModules' => $installedModules,
            'moduleCount' => $moduleCount
        );

        return $this->responseView('Cake\ViewAdmin_AddOn_Modules_List', 'cake_addon_modules_list', $viewParams);
    }

    protected function _getModulesForAddOn(array $addOn)
    {
        $addOnModel = $this->_getAddOnModel();

        $addOnId = $addOn['addon_id'];

        $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);

        $modules = array();
        if ($installData) {
            $modules = $installData->getModules();
        }

        $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);
        $activeModules = Proxy::getOptionValue('modules', $addOnId);

        $availableModules = array();
        $outdatedModules = array();
        foreach ($modules as $moduleName => $versionId) {
            $installed = isset($installedModules[$moduleName]);
            if ($installed) {
                $outdated = $installedModules[$moduleName]['version_id'] < $versionId;
                if ($outdated) {
                    $outdatedModules[] = $moduleName;
                }
            }
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
        return array(
            'addOn' => $addOn,
            'installedModules' => $installedModules,
            'availableModules' => $availableModules,
            'outdatedModules' => $outdatedModules
        );
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
                return $this->responseError(new \XenForo_Phrase('cake_module_files_missing'));
            }

            $addOnModel = $this->_getAddOnModel();

            $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);

            if (isset($installedModules[$moduleName])) {
                return $this->responseError(new \XenForo_Phrase('cake_module_already_installed'));
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
                return $this->responseError(new \XenForo_Phrase('cake_module_files_missing'));
            }

            $addOnModel = $this->_getAddOnModel();

            $installedModules = $addOnModel->getInstalledModulesForAddOn($addOnId);

            if (!isset($installedModules[$moduleName])) {
                return $this->responseError(new \XenForo_Phrase('cake_module_not_installed'));
            }

            if (count($installedModules) == 1) {
                return $this->responseError(
                    new \XenForo_Phrase('cake_it_is_not_possible_to_uninstall_the_only_installed_module'));
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

    public function actionModulesrebuild()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);

        $addOn = $this->_getAddOnOrError($addOnId);

        if ($this->isConfirmedPost()) {
            $addOnModel = $this->_getAddOnModel();

            $addOnModel->rebuildModulesForAddOn($addOn);

            return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS,
                \XenForo_Link::buildAdminLink('add-ons/modules',
                    array(
                        'addon_id' => $addOnId
                    )));
        } else {
            $viewParams = array(
                'addOn' => $addOn
            );

            return $this->responseView('ViewAdmin_AddOn_Modules_Rebuild', 'cake_addon_rebuild', $viewParams);
        }
    }

    public function actionModulesreset()
    {
        $addOnId = $this->_input->filterSingle('addon_id', \XenForo_Input::STRING);
        $addOn = $this->_getAddOnOrError($addOnId);

        if ($this->isConfirmedPost()) {
            $dw = \XenForo_DataWriter::create('XenForo_DataWriter_Option');
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

        if ($addOnId) {
            $addOn = $this->_getAddOnOrError($addOnId);

            $this->_toggleModulesForAddOn($addOn, $idExists, $ids);

            return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS,
                \XenForo_Link::buildAdminLink('add-ons/modules',
                    array(
                        'addon_id' => $addOnId
                    )));
        }

        $addOnIds = array();
        $addOnIdExists = array();
        foreach (array_keys($idExists) as $fullModuleName) {
            $underscore = strrpos($fullModuleName, '_');
            $addOnId = substr($fullModuleName, 0, $underscore);
            $moduleName = substr($fullModuleName, $underscore + 1);
            $addOnIdExists[$addOnId][$moduleName] = 1;
            if (!empty($ids[$fullModuleName])) {
                $addOnIds[$addOnId][$moduleName] = 1;
            } else {
                $addOnIds[$addOnId][$moduleName] = 0;
            }
        }

        $addOns = $this->_getAddOnModel()->getAllAddOns();

        foreach ($addOns as $addOnId => $addOn) {
            if (isset($addOnIdExists[$addOnId])) {
                $this->_toggleModulesForAddOn($addOn, $addOnIdExists[$addOnId], $addOnIds[$addOnId]);
            }
        }

        return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS,
            \XenForo_Link::buildAdminLink('add-ons/modules'));
    }

    protected function _toggleModulesForAddOn(array $addOn, array $idExists, array $ids = array())
    {
        $addOnId = $addOn['addon_id'];

        $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);

        $modules = array();
        if ($installData) {
            $modules = $installData->getModules();
        }

        $xenOptions = \XenForo_Application::getOptions();

        $preOption = \Cake\Helper_String::pascalCaseToCamelCase($addOnId);
        $optionName = $preOption . '_modules';

        $existingValue = $xenOptions->$optionName;

        $ids = array_filter($ids);

        if ($existingValue != $ids) {
            $dw = \XenForo_DataWriter::create('XenForo_DataWriter_Option');
            $dw->setExistingData($optionName);
            $dw->set('option_value', $ids);
            $dw->save();

            $xenOptions->set($optionName, $ids);

            $this->_getAddOnModel()->rebuildAddOnCachesAfterActiveSwitch($addOn);
        }
    }

    public function actionRebuildCakeAddOns()
    {
        $xenOptions = \XenForo_Application::getOptions();

        $dw = \XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $dw->setExistingData('cake_currentXenForoVersionId');
        $dw->set('option_value', $xenOptions->currentVersionId);
        $dw->save();

        /* @var $modificationModel XenForo_Model_TemplateModification */
        $modificationModel = $this->getModelFromCache('XenForo_Model_TemplateModification');
        $modificationModel->rebuildCakeTemplatesAfterUpgrade();

        /* @var $modificationModel XenForo_Model_AdminTemplateModification */
        $modificationModel = $this->getModelFromCache('XenForo_Model_AdminTemplateModification');
        $modificationModel->rebuildCakeTemplatesAfterUpgrade();

        /* @var $modificationModel XenForo_Model_EmailTemplateModification */
        $modificationModel = $this->getModelFromCache('XenForo_Model_EmailTemplateModification');
        $modificationModel->rebuildCakeTemplatesAfterUpgrade();

        return $this->responseRedirect(\XenForo_ControllerResponse_Redirect::SUCCESS,
            \XenForo_Link::buildAdminLink('index'));
    }
}