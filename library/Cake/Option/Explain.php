<?php
namespace Cake;

class Option_Explain
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
    public static function render(\XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        if (\XenForo_Application::debugMode()) {
            $parts = explode('_', $preparedOption['option_id']);

            $optionName = array_pop($parts);

            $preparedOption['title'] = new \XenForo_Phrase('option_cake_' . $optionName);
            $preparedOption['explain'] = new \XenForo_Phrase('option_cake_' . $optionName . '_explain',
                array(
                    'addOnId' => $preparedOption['addon_id']
                ));

            return \XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal('cake_option_template_explain', $view,
                $fieldPrefix, $preparedOption, $canEdit);
        }
    }
}