<?php
namespace Cake;

class Install_Data extends Install_DataAbstract
{

    public function getTables()
    {
        return array(
            'cake_module' => array(
                'module_name' => 'varchar(25) NOT NULL',
                'addon_id' => 'varchar(50) NOT NULL',
                'version_id' => 'int(10) NOT NULL',
                'version_string' => 'varchar(20) NOT NULL'
            )
        );
    }

    public function getPrimaryKeys()
    {
        return array(
            'cake_module' => array(
                'module_name',
                'addon_id'
            )
        );
    }

    public function getTableChanges()
    {
        return array(
            'xf_admin_navigation' => array(
                'module_name_cake' => 'varchar(25) NOT NULL DEFAULT \'\''
            )
        );
    }
}