<?php


use Phinx\Migration\AbstractMigration;

class AddServiceSubscribeType extends AbstractMigration
{
    public function change()
    {
        $this->table('services')->addColumn('subscribe_type', 'string', array('null' => false, 'default' => 'none'))->update();
    }
}
