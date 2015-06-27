<?php
namespace Cake;

class Admin
{

    public static function containerAdminParams(array &$params, \XenForo_Dependencies_Abstract $dependencies)
    {
        foreach ($params['adminNavigation']['sideLinks'] as $section => $sideLinks) {
            foreach ($sideLinks as $adminNavigationId => $adminNavigation) {
                if ($adminNavigation['module_name_cake']) {
                    $modules = \Cake\Proxy::getOptionValue('modules', $adminNavigation['addon_id']);
                    if (!isset($modules[$adminNavigation['module_name_cake']])) {
                        unset($params['adminNavigation']['sideLinks'][$section][$adminNavigationId]);
                    }
                }
            }
        }
    }
}