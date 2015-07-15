<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_Cron extends \XenForo_Model_Cron
    {
    }
}

class XenForo_Model_Cron extends XFCP_XenForo_Model_Cron
{

    public function runEntry(array $entry)
    {
        if (isset($entry['cron_class'])) {
            $entry['cron_class'] = \XenForo_Application::resolveDynamicClass($entry['cron_class']);
        }

        return parent::runEntry($entry);
    }
}