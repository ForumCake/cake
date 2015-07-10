<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_DataWriter_Template extends \XenForo_DataWriter_Template
    {
    }
}

class XenForo_DataWriter_Template extends XFCP_XenForo_DataWriter_Template
{

    /**
     *
     * @version 1.2.0
     */
    protected function _verifyPrepareTemplate($template)
    {
        $standardParse = true;
        $parsed = null;

        if (!$this->get('disable_modifications')) {
            $templateWithModifications = $this->_getModificationModel()->applyModificationsToTemplate(
                $this->get('title'), $template, $modificationStatuses);
        } else {
            $modificationStatuses = null;
            $templateWithModifications = $template;
        }

        if ($modificationStatuses) {
            try {
                $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler', $templateWithModifications);
                $parsed = $compiler->lexAndParse();

                if ($this->getOption(self::OPTION_TEST_COMPILE)) {
                    $compiler->setFollowExternal(false);
                    $compiler->compileParsed($parsed, $this->get('title'), 0, 0);
                }
                $standardParse = false;
            } catch (\XenForo_Template_Compiler_Exception $e) {
                foreach ($modificationStatuses as &$status) {
                    if (is_int($status)) {
                        $status = 'error_compile';
                    }
                }
            }
        }

        if ($standardParse) {
            try {
                $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler', $template);
                $parsed = $compiler->lexAndParse();

                if ($this->getOption(self::OPTION_TEST_COMPILE)) {
                    $compiler->setFollowExternal(false);
                    $compiler->compileParsed($parsed, $this->get('title'), 0, 0);
                }
            } catch (\XenForo_Template_Compiler_Exception $e) {
                $this->error($e->getMessage(), 'template');
                return false;
            }
        }

        $this->set('template_parsed', serialize($parsed));
        $this->_modificationStatuses = $modificationStatuses;
        return true;
    }
}