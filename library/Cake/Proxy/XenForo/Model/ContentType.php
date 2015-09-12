<?php
namespace Cake\Proxy;

if (false) {

    class XFCP_XenForo_Model_ContentType extends \XenForo_Model_ContentType
    {
    }
}

class XenForo_Model_ContentType extends XFCP_XenForo_Model_ContentType
{

    public function insertContentTypesCake(array $contentTypes)
    {
        $existingContentTypes = $this->getContentTypesForCache();

        $db = $this->_getDb();

        foreach ($contentTypes as $contentType => $contentTypeParams) {
            $existingFields = array();

            if (isset($existingContentTypes[$contentType])) {
                $existingFields = $existingContentTypes[$contentType];
            }

            $this->insertContentTypeFieldsCake(
                array(
                    $contentType => $contentTypeParams['fields']
                ));
            $existingFields = array_merge($contentTypeParams['fields'], $existingFields);

            $db->query(
                '
                    INSERT INTO xf_content_type
                    (content_type, addon_id, fields)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        addon_id = VALUES(addon_id),
                        fields = VALUES(fields)
                ',
                array(
                    $contentType,
                    $contentTypeParams['addon_id'],
                    serialize($existingFields)
                ));

            $existingContentTypes[$contentType] = $existingFields;
        }

        $this->_getDataRegistryModel()->set('contentTypes', $existingContentTypes);
        \XenForo_Application::set('contentTypes', $existingContentTypes);
    }

    public function insertContentTypeFieldsCake(array $contentTypeFields, $rebuild = false)
    {
        $db = $this->_getDb();

        foreach ($contentTypeFields as $contentType => $fields) {
            foreach ($fields as $fieldName => $fieldValue) {
                $db->query(
                    '
                        INSERT INTO xf_content_type_field
                            (content_type, field_name, field_value)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            field_value = VALUES(field_value)
                    ',
                    array(
                        $contentType,
                        $fieldName,
                        $fieldValue
                    ));
            }
        }

        if ($rebuild) {
            $this->rebuildContentTypeCache();
        }
    }

    public function deleteContentTypesCake(array $contentTypes)
    {
        $db = $this->_getDb();

        foreach ($contentTypes as $contentType => $contentTypeParams) {
            $db->query(
                '
                    DELETE FROM xf_content_type
                    WHERE content_type = ?
                        AND addon_id = ?
                ',
                array(
                    $contentType,
                    $contentTypeParams['addon_id']
                ));
            $db->query(
                '
                DELETE FROM xf_content_type_field
                WHERE content_type = ?
            ', $contentType);
        }

        $this->rebuildContentTypeCache();
    }

    public function deleteContentTypeFieldsCake(array $contentTypes, $rebuild = false)
    {
        $db = $this->_getDb();

        foreach ($contentTypes as $contentType => $contentTypeFields) {
            foreach ($contentTypeFields as $fieldName => $fieldValue) {
                $db->query(
                    '
                        DELETE FROM xf_content_type_field
                        WHERE content_type = ?
                            AND field_name = ? AND field_value = ?
                    ',
                    array(
                        $contentType,
                        $fieldName,
                        $fieldValue
                    ));
            }
        }

        if ($rebuild) {
            $this->rebuildContentTypeCache();
        }
    }
}