<?php


use Phinx\Migration\AbstractMigration;

class AddUserAuthToken extends AbstractMigration
{
    public function change()
    {
        $this->table('user_auth_token')
             ->addColumn('users_id', 'integer', array('null' => false))
             ->addColumn('services', 'string', array('null' => false))
             ->addColumn('value', 'string', array('null' => false))
             ->addColumn('valid_from', 'datetime', array('null' => true))
             ->addColumn('valid_to', 'datetime', array('null' => true))
             ->addForeignKey('users_id', 'users', 'id')
             ->addIndex('users_id')
             ->addIndex('value')
             ->create();
    }
}
