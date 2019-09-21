<?php


use Phinx\Migration\AbstractMigration;

class AddMediatedServiceTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('service_mediator', array('id' => false, 'primary_key' => array('parent_service_id', 'mediated_service_id')));

        $table->addColumn('parent_service_id', 'integer', array('null' => false))
              ->addColumn('mediated_service_id', 'integer', array('null' => false));

        $table->addForeignKey('parent_service_id', 'services', 'id')
              ->addForeignKey('mediated_service_id', 'services', 'id');

        $table->create();
    }
}
