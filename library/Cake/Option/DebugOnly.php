<?php
namespace Cake;

class Option_DebugOnly
{

    /**
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderTextbox(\XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        if (\XenForo_Application::debugMode()) {
            return \XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal('option_list_option_textbox', $view,
                $fieldPrefix, $preparedOption, $canEdit);
        }
    }
}