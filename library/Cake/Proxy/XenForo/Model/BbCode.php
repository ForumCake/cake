<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_BbCode extends \XenForo_Model_BbCode
    {
    }
}

class XenForo_Model_BbCode extends XFCP_XenForo_Model_BbCode
{

    public function getBbCodes(array $conditions = array(), array $fetchOptions = array())
    {
        $bbCodes = parent::getBbCodes($conditions, $fetchOptions);
        
        if (!empty($conditions['addOnActive']))
		{
	        foreach ($bbCodes as $bbCodeId => $bbCode) {
	            if ($bbCode['module_name_cake']) {
	                $modules = \Cake\Proxy::getOptionValue('modules', $bbCode['addon_id']);
	                if (!isset($modules[$bbCode['module_name_cake']])) {
	                    unset($bbCodes[$bbCodeId]);
	                }
	            }
	        }
		}
		
        return $bbCodes;
    }
    
    public function importBbCodesAddOnXml(\SimpleXMLElement $xml, $addOnId)
    {
        $db = $this->_getDb();
        
        parent::importBbCodesAddOnXml($xml, $addOnId);
        
        $xmlBbCode = \XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->bb_code);
        
        \XenForo_Db::beginTransaction($db);
        foreach ($xmlBbCode as $bbCode) {
            $bbCodeId = (string) $bbCode['bb_code_id'];
            
            $moduleName = (string) $bbCode['module_name_cake'];
            
            if ($moduleName) {
                $db->update('xf_bb_code', 
                    array(
                        'module_name_cake' => $moduleName
                    ), 'bb_code_id = ' . $db->quote($bbCodeId));
            }
        }
        \XenForo_Db::commit($db);
    }
}