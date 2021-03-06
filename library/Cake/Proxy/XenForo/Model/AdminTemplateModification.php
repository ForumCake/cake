<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AdminTemplateModification extends \XenForo_Model_AdminTemplateModification
    {
    }
}

class XenForo_Model_AdminTemplateModification extends XFCP_XenForo_Model_AdminTemplateModification
{
    use XenForo_Model_TemplateModificationAbstract;

    public function rebuildCakeTemplatesAfterUpgrade()
    {
        $titles = $this->getModificationTemplateTitlesForCakeAddons();

        /* @var $templateModel XenForo_Model_AdminTemplate */
        $templateModel = $this->getModelFromCache('XenForo_Model_AdminTemplate');
        $templates = $templateModel->getAdminTemplatesByTitles($titles);

        $templateIds = array();
        foreach ($templates as $template) {
            $templateIds[] = $template['template_id'];
        }

        if ($templateIds) {
            \XenForo_Application::defer('AdminTemplatePartialCompile',
                array(
                    'reparseTemplateIds' => $templateIds,
                    'recompileTemplateIds' => $templateModel->getIdsToCompileByTemplateIds($templateIds)
                ), null, true);

            return true;
        }

        return false;
    }
}