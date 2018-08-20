<?php


use Phinx\Migration\AbstractMigration;

class AddUserDetailsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('user_details', array('id' => false, 'primary_key' => array('users_id')))
             ->addColumn('users_id', 'integer', array('null' => false))
             ->addColumn('first_name', 'string', array('null' => true))
             ->addColumn('last_name', 'string', array('null' => true))
             ->addForeignKey('users_id', 'users', 'id')
             ->create();
    }
}
