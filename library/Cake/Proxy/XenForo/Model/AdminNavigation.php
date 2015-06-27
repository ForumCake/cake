<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AdminNavigation extends \XenForo_Model_AdminNavigation
    {
    }
}

class XenForo_Model_AdminNavigation extends XFCP_XenForo_Model_AdminNavigation
{

    public function importAdminNavigationAddOnXml(\SimpleXMLElement $xml, $addOnId)
    {
        $db = $this->_getDb();
        
        parent::importAdminNavigationAddOnXml($xml, $addOnId);
        
        $xmlNav = \XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->navigation);
        
        \XenForo_Db::beginTransaction($db);
        foreach ($xmlNav as $nav) {
            $navId = (string) $nav['navigation_id'];
            
            $moduleName = (string) $nav['module_name_cake'];
            
            if ($moduleName) {
                $db->update('xf_admin_navigation', 
                    array(
                        'module_name_cake' => $moduleName
                    ), 'navigation_id = ' . $db->quote($navId));
            }
        }
        \XenForo_Db::commit($db);
    }
}