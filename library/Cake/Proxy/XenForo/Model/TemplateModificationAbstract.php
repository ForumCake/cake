<?php
namespace Cake\Proxy;

trait XenForo_Model_TemplateModificationAbstract
{

    public function getActiveModificationsForTemplate($title)
    {
        $modifications = parent::getActiveModificationsForTemplate($title);
        
        if ($modifications) {
            foreach ($modifications as $key => $modification) {
                $addOnId = $modification['addon_id'];
                $modificationKey = $modification['modification_key'];
                $modificationKey = \Cake\Helper_String::camelCaseToPascalCase($modificationKey);
                if (strpos($modificationKey, $addOnId) !== false) {
                    $modificationKey = substr($modificationKey, strlen($addOnId) + 1);
                    $parts = explode('_', $modificationKey, 2);
                    if (count($parts) == 2) {
                        list($moduleName, $modificationKey) = $parts;
                        
                        $activeModules = \Cake\Proxy::getOptionValue('modules', $addOnId);
                        
                        if (empty($activeModules[$moduleName])) {
                            unset($modifications[$key]);
                        }
                    }
                }
            }
        }
        
        return $modifications;
    }
}