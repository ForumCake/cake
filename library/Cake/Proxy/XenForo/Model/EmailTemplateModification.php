<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_EmailTemplateModification extends \XenForo_Model_EmailTemplateModification
    {
    }
}

class XenForo_Model_EmailTemplateModification extends XFCP_XenForo_Model_EmailTemplateModification
{
    use XenForo_Model_TemplateModificationAbstract;

    public function rebuildCakeTemplatesAfterUpgrade()
    {
        $titles = $this->getModificationTemplateTitlesForCakeAddons();

        /* @var $templateModel XenForo_Model_EmailTemplate */
        $templateModel = $this->getModelFromCache('XenForo_Model_EmailTemplate');
        $templates = $templateModel->getEmailTemplatesByTitles($titles);

        $templateIds = array();
        foreach ($templates as $template) {
            $templateIds[] = $template['template_id'];
        }

        if ($templateIds) {
            \XenForo_Application::defer('EmailTemplatePartialCompile',
                array(
                    'reparseTemplateIds' => $templateIds,
                    'recompileTemplateIds' => $templateIds
                ), null, true);

            return true;
        }

        return false;
    }
}