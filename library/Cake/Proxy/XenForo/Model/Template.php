<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_Template extends \XenForo_Model_Template
    {
    }
}

class XenForo_Model_Template extends XFCP_XenForo_Model_Template
{

    /**
     *
     * @version 1.2.0
     */
    public function compileAndInsertParsedTemplate($templateMapId, $parsedTemplate, $title, $compileStyleId, 
        $doDbWrite = null)
    {
        $isCss = (substr($title, -4) == '.css');
        
        if ($doDbWrite === null) {
            $doDbWrite = ($isCss || $compileStyleId);
        }
        
        $compiler = \Cake\Template_Compiler::create('XenForo_Template_Compiler_Admin', '');
        $languages = $this->getModelFromCache('XenForo_Model_Language')->getAllLanguages();
        
        $db = $this->_getDb();
        
        $compiledCache = array();
        
        if ($isCss) {
            $compiledTemplate = $compiler->compileParsed($parsedTemplate, $title, $compileStyleId, 0);
            $compiledCache[0] = $compiledTemplate;
            if ($doDbWrite) {
                $this->_insertCompiledTemplateRecord($compileStyleId, 0, $title, $compiledTemplate);
            }
        } else {
            foreach ($languages as $language) {
                $compiledTemplate = $compiler->compileParsed($parsedTemplate, $title, $compileStyleId, 
                    $language['language_id']);
                $compiledCache[$language['language_id']] = $compiledTemplate;
                
                if ($doDbWrite) {
                    $this->_insertCompiledTemplateRecord($compileStyleId, $language['language_id'], $title, 
                        $compiledTemplate);
                }
            }
        }
        
        $mapIdQuoted = $db->quote($templateMapId);
        
        $ins = array();
        $includedTemplateIds = array();
        
        foreach ($compiler->getIncludedTemplates() as $includedMapId) {
            $ins[] = '(' . $mapIdQuoted . ', ' . $db->quote($includedMapId) . ')';
            $includedTemplateIds[] = $includedMapId;
        }
        
        if ($doDbWrite) {
            $db->delete('xf_template_include', 'source_map_id = ' . $db->quote($templateMapId));
            if ($ins) {
                $db->query(
                    "
					INSERT IGNORE INTO xf_template_include
						(source_map_id, target_map_id)
					VALUES
						" . implode(',', $ins));
            }
        }
        
        $ins = array();
        $includedPhraseTitles = array();
        
        foreach ($compiler->getIncludedPhrases() as $includedPhrase) {
            if (strlen($includedPhrase) > 75) {
                continue; // too long, can't be a valid phrase
            }
            
            $ins[] = '(' . $mapIdQuoted . ', ' . $db->quote($includedPhrase) . ')';
            $includedPhraseTitles[] = $includedPhrase;
        }
        
        if ($doDbWrite) {
            $db->delete('xf_template_phrase', 'template_map_id = ' . $db->quote($templateMapId));
            if ($ins) {
                $db->query(
                    "
					INSERT IGNORE INTO xf_template_phrase
						(template_map_id, phrase_title)
					VALUES
						" . implode(',', $ins));
            }
        }
        
        return array(
            'includedTemplateIds' => $includedTemplateIds,
            'failedTemplateIncludes' => $compiler->getFailedTemplateIncludes(),
            'includedPhraseTitles' => $includedPhraseTitles,
            'compiledCache' => $compiledCache,
            'doDbWrite' => $doDbWrite
        );
    }
}