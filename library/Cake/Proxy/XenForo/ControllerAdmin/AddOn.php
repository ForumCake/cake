<?php
namespace Cake\Proxy;

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
            
            foreach ($response->params['addOns'] as $addOnId => &$addOn) {
                if (strlen($addOnId) > 4 && substr($addOnId, 0, 4) == 'Cake') {
                    $addOn['depth'] = 1;
                    $addOn['title'] = new \XenForo_Phrase(\Cake\Helper_String::pascalCaseToCamelCase($addOnId));
                    $addOn['prefix'] = new \XenForo_Phrase('cake');
                }
            }
        }
        
        return $response;
    }
}