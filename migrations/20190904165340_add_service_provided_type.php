<?php


use Phinx\Migration\AbstractMigration;

class AddServiceProvidedType extends AbstractMigration
{
    public function change()
    {
        $this->table('services')->addColumn('provided_type', 'string', array('null' => false, 'default' => 'indirect'))->update();
    }
}
