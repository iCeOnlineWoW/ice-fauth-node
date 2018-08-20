<?php


use Phinx\Migration\AbstractMigration;

class AddUserAuthTokenServices extends AbstractMigration
{
    public function change()
    {
        $this->table('user_auth_info')
             ->addColumn('services', 'string', array('null' => false, 'default' => '', 'after' => 'value'))
             ->update();
    }
}
