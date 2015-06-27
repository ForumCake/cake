<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AdminNavigation extends \XenForo_Model_AdminNavigation
    {
    }
}

class XenForo_Model_AdminNavigation extends XFCP_XenForo_Model_AdminNavigation
{

    protected $_adminNavigationModuleNames = array();

    public function getAdminNavigationEntriesInAddOn($addOnId, array $fetchOptions = array())
    {
        $adminNavigationEntries = parent::getAdminNavigationEntriesInAddOn($addOnId, $fetchOptions);
        
        foreach ($adminNavigationEntries as $navId => $nav) {
            $this->_adminNavigationModuleNames[$navId] = $nav['module_name_cake'];
        }
        
        return $adminNavigationEntries;
    }

    public function appendAdminNavigationAddOnXml(\DOMElement $rootNode, $addOnId)
    {
        parent::appendAdminNavigationAddOnXml($rootNode, $addOnId);
        
        foreach ($rootNode->getElementsByTagName('navigation') as $navNode) {
            $navId = (string) $navNode->getAttribute('navigation_id');
            $moduleNameCake = $this->_adminNavigationModuleNames[$navId];
            $navNode->setAttribute('module_name_cake', $moduleNameCake);
        }
    }

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
                $db->update('xf_admin_navigation', array(
                    'module_name_cake' => $moduleName
                ), 'navigation_id = ' . $db->quote($navId));
            }
        }
        \XenForo_Db::commit($db);
    }
}