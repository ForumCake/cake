<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Template_Compiler_Admin extends \XenForo_Template_Compiler_Admin
    {
    }
}

class XenForo_Template_Compiler_Admin extends XFCP_XenForo_Template_Compiler_Admin
{

    public function compileTag($tag, array $attributes, array $children, array $options)
    {
        $statement = parent::compileTag($tag, $attributes, $children, $options);
        
        if (isset($attributes['name'][0])) {
            $name = $attributes['name'][0];
            if (is_string($name) && strlen($name) > 4 && substr($name, -5) == '_cake') {
                $statement->addStatement(
                    '$__compilerVar1 .= "<input type=\"hidden\" name=\"' . $name . '_shown\" value=\"1\" />";');
            }
        }
        
        foreach ($children as $child) {
            if (is_array($child) && isset($child['attributes']['name'][0])) {
                $name = $child['attributes']['name'][0];
                if (is_string($name) && strlen($name) > 4 && substr($name, -5) == '_cake') {
                    $statement->addStatement(
                        '$__compilerVar1 .= "<input type=\"hidden\" name=\"' . $name . '_shown\" value=\"1\" />";');
                }
            }
        }
        
        return $statement;
    }
}