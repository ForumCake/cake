<?php
namespace Cake;

class Helper_Deferred
{

    public static function setDeferred($deferredName, \XenForo_Deferred_Abstract $deferred)
    {
        if (\XenForo_Application::isRegistered('cakeDeferreds')) {
            $deferreds = \XenForo_Application::get('cakeDeferreds');
        } else {
            $deferreds = array();
        }

        $deferreds[$deferredName] = $deferred;

        \XenForo_Application::set('cakeDeferreds', $deferreds);
    }

    public static function getDeferred($deferredName)
    {
        if (\XenForo_Application::isRegistered('cakeDeferreds')) {
            $deferreds = \XenForo_Application::get('cakeDeferreds');
            if (isset($deferreds[$deferredName])) {
                return $deferreds[$deferredName];
            }
        }

        return false;
    }
}