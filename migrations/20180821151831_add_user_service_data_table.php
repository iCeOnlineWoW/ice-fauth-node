<?php


use Phinx\Migration\AbstractMigration;

class AddUserServiceDataTable extends AbstractMigration
{
    public function change()
    {
        $this->table('user_service_data', array('id' => false, 'primary_key' => array('users_id', 'services_id')))
             ->addColumn('users_id', 'integer', array('null' => false))
             ->addColumn('services_id', 'integer', array('null' => false))
             ->addColumn('data', 'text', array('null' => true))
             ->addForeignKey('users_id', 'users', 'id')
             ->addForeignKey('services_id', 'services', 'id')
             ->create();
    }
}
