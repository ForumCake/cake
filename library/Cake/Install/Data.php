<?php
namespace Cake;

class Install_Data extends Install_DataAbstract
{

    public function getTableChanges()
    {
        return array(
            'xf_admin_navigation' => array(
                'module_name_cake' => 'varchar(25) NOT NULL DEFAULT \'\''
            )
        );
    }
}