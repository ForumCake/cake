<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_DataWriter_AddOn extends \XenForo_DataWriter_AddOn
    {
    }
}

class XenForo_DataWriter_AddOn extends XFCP_XenForo_DataWriter_AddOn
{

    protected function _preDelete()
    {
        parent::_preDelete();

        if ($this->get('addon_id') == 'Cake') {
            $cakeAddOns = $this->_getAddOnModel()->getCakeAddOns();

            if ($cakeAddOns) {
                $this->error(
                    new \XenForo_Phrase(
                        'cake_this_add_on_cannot_be_uninstalled_while_one_or_more_add_ons_with_cake_modules_installed'));
            }
        }
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->get('addon_id') == 'Cake' && $this->isChanged('active') && !$this->get('active')) {
            $cakeAddOns = $this->_getAddOnModel()->getCakeAddOns();
            foreach ($cakeAddOns as $addOnId) {
                if ($addOnId != $this->get('addon_id')) {
                    $dw = \XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
                    $dw->setExistingData($addOnId);
                    $dw->set('active', 0);
                    $dw->save();
                }
            }
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        if ($this->get('addon_id') == 'Cake') {
            return;
        }

        $this->_db->query('
            DELETE FROM cake_module
            WHERE addon_id = ?
        ', $this->get('addon_id'));
    }
}