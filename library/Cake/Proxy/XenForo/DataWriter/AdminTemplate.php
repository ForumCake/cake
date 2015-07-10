<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_DataWriter_AdminTemplate extends \XenForo_DataWriter_AdminTemplate
    {
    }
}

class XenForo_DataWriter_AdminTemplate extends XFCP_XenForo_DataWriter_AdminTemplate
{

    /**
     *
     * @version 1.2.0
     */
    protected function _verifyPrepareTemplate(&$template)
    {
        $templateWithModifications = $this->_getModificationModel()->applyModificationsToTemplate($this->get('title'),
            $template, $modificationStatuses);
        $standardParse = true;
        $parsed = null;

        if ($modificationStatuses) {
            try {
                $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler_Admin',
                    $templateWithModifications);
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
                $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler_Admin', $template);
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