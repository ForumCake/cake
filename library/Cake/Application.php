<?php
namespace Cake;

class Application
{

    public static function getCopyrightHtml()
    {
        $args = func_get_args();

        $copyright = \XenForo_Template_Helper_Core::callHelper('cake_copyright', $args);

        if ($copyright) {
            $copyright .= ' | ';
        }

        return $copyright .
             '<a href="https://forumcake.com" class="concealed">Add-ons by Cake <span>&copy;2015 Forum Cake Ltd.</span></a>';
    }
}