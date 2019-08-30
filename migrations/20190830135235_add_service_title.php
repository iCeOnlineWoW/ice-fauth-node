<?php


use Phinx\Migration\AbstractMigration;

class AddServiceTitle extends AbstractMigration
{
    public function change()
    {
        $this->table('services')->addColumn('title', 'string', array('null' => false, 'default' => 'Unnamed service', 'after' => 'name'))->update();
    }
}
