<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_TemplateModification extends \XenForo_Model_TemplateModification
    {
    }
}

class XenForo_Model_TemplateModification extends XFCP_XenForo_Model_TemplateModification
{
    use XenForo_Model_TemplateModificationAbstract;

    public function rebuildCakeTemplatesAfterUpgrade()
    {
        $titles = $this->getModificationTemplateTitlesForCakeAddons();

        /* @var $templateModel XenForo_Model_Template */
        $templateModel = $this->getModelFromCache('XenForo_Model_Template');
        $templateIds = array_keys($templateModel->getTemplatesByTitles($titles));
        if ($templateIds) {
            \XenForo_Application::defer('TemplatePartialCompile',
                array(
                    'reparseTemplateIds' => $templateIds,
                    'recompileMapIds' => $templateModel->getMapIdsToCompileByTitles($titles)
                ), null, true);

            return true;
        }

        return false;
    }
}