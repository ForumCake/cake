<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_AdminTemplate extends XenForo_Model_AdminTemplate
    {
    }
}

class XenForo_Model_AdminTemplate extends XFCP_XenForo_Model_AdminTemplate
{

    /**
     *
     * @version 1.2.1
     */
    public function compileParsedAdminTemplate($templateId, array $parsedTemplate, $title)
    {
        $isCss = (substr($title, -4) == '.css');

        $languages = $this->getModelFromCache('XenForo_Model_Language')->getAllLanguages();
        $db = $this->_getDb();

        $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler_Admin', '');

        if ($isCss) {
            $compiledTemplate = $compiler->compileParsed($parsedTemplate, $title, 0, 0);
            $db->query(
                '
				INSERT INTO xf_admin_template_compiled
					(language_id, title, template_compiled)
				VALUES
					(?, ?, ?)
				ON DUPLICATE KEY UPDATE template_compiled = VALUES(template_compiled)
			',
                array(
                    0,
                    $title,
                    $compiledTemplate
                ));
        } else {
            foreach ($languages as $language) {
                $compiledTemplate = $compiler->compileParsed($parsedTemplate, $title, 0, $language['language_id']);
                $db->query(
                    '
					INSERT INTO xf_admin_template_compiled
						(language_id, title, template_compiled)
					VALUES
						(?, ?, ?)
					ON DUPLICATE KEY UPDATE template_compiled = VALUES(template_compiled)
				',
                    array(
                        $language['language_id'],
                        $title,
                        $compiledTemplate
                    ));
            }
        }

        $ins = array();
        foreach ($compiler->getIncludedTemplates() as $includedId) {
            $ins[] = '(' . $db->quote($templateId) . ', ' . $db->quote($includedId) . ')';
            // TODO: this system doesn't handle includes for templates that
            // don't exist yet
        }
        $db->delete('xf_admin_template_include', 'source_id = ' . $db->quote($templateId));
        if ($ins) {
            $db->query(
                "
				INSERT IGNORE INTO xf_admin_template_include
					(source_id, target_id)
				VALUES
					" . implode(',', $ins));
        }

        $ins = array();
        foreach ($compiler->getIncludedPhrases() as $includedPhrase) {
            if (strlen($includedPhrase) > 75) {
                continue; // too long, can't be a valid phrase
            }

            $ins[] = '(' . $db->quote($templateId) . ', ' . $db->quote($includedPhrase) . ')';
        }
        $db->delete('xf_admin_template_phrase', 'template_id = ' . $db->quote($templateId));
        if ($ins) {
            $db->query(
                '
				INSERT IGNORE INTO xf_admin_template_phrase
					(template_id, phrase_title)
				VALUES
					' . implode(',', $ins));
        }
    }
}