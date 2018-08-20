<?php


use Phinx\Migration\AbstractMigration;

class AddUserAuthInfoTable extends AbstractMigration
{
    public function change()
    {
        $this->table('user_auth_info')
             ->addColumn('users_id', 'integer', array('null' => false))
             ->addColumn('type', 'string', array('null' => false, 'default' => 'password'))
             ->addColumn('value', 'string', array('null' => false))
             ->addColumn('valid_from', 'datetime', array('null' => true))
             ->addColumn('valid_to', 'datetime', array('null' => true))
             ->addColumn('disabled', 'boolean', array('null' => false, 'default' => false))
             ->addForeignKey('users_id', 'users', 'id')
             ->addIndex(array('users_id', 'type'))
             ->create();
    }
}
