<?php


use Phinx\Migration\AbstractMigration;

class AddServicesTable extends AbstractMigration
{
    public function change()
    {
        $this->table('services')
             ->addColumn('name', 'string', array('null' => false))
             ->addColumn('description', 'string', array('null' => true))
             ->addColumn('disabled', 'boolean', array('null' => false, 'default' => false))
             ->create();
    }
}
