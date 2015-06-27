<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AddOn extends \XenForo_Model_AddOn
    {
    }
}

class XenForo_Model_AddOn extends XFCP_XenForo_Model_AddOn
{

    public function getAddOnsWithIdPrefix($prefix)
    {
        return $this->fetchAllKeyed(
            '
    			SELECT *
    			FROM xf_addon
                WHERE addon_id LIKE \'' . $prefix . '%\'
    			ORDER BY title
    		', 'addon_id');
    }

    public function prepareCakeAddOns(array $addOns)
    {
        foreach ($addOns as $addOnId => &$addOn) {
            if (strlen($addOnId) > 4 && substr($addOnId, 0, 4) == 'Cake') {
                $addOn['depth'] = 1;
                $addOn['title'] = new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId));
                $addOn['prefix'] = new \XenForo_Phrase('cake');
            }
            
            $installData = \Cake\Install_DataAbstract::createForAddOnId($addOnId);
            
            $modules = array();
            if ($installData) {
                $modules = $installData->getModules();
                if ($modules) {
                    $addOn['moduleCount'] = count($modules);
                    $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);
                    $addOn['activeModuleCount'] = count($activeModules);
                }
            }
        }
        
        return $addOns;
    }
}