<?php
namespace Cake;

class Admin
{

    public static function containerAdminParams(array &$params, \XenForo_Dependencies_Abstract $dependencies)
    {
        foreach ($params['adminNavigation']['sideLinks'] as $section => $sideLinks) {
            $sideLinkCount = count($sideLinks);
            foreach ($sideLinks as $adminNavigationId => $adminNavigation) {
                if ($adminNavigation['module_name_cake']) {
                    $modules = \Cake\Proxy::getOptionValue('modules', $adminNavigation['addon_id']);
                    if (!isset($modules[$adminNavigation['module_name_cake']])) {
                        unset($params['adminNavigation']['sideLinks'][$section][$adminNavigationId]);
                        $sideLinkCount--;
                    }
                }
            }
            if (!$sideLinkCount) {
                foreach ($params['adminNavigation']['sideLinks'] as $_section => $_sideLinks) {
                    foreach ($_sideLinks as $_adminNavigationId => $_adminNavigation) {
                        if ($_adminNavigationId == $section && $_adminNavigation['hide_no_children']) {
                            unset($params['adminNavigation']['sideLinks'][$_section][$_adminNavigationId]);
                        }
                    }
                }
            }
        }
    }
}