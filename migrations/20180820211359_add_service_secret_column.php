<?php


use Phinx\Migration\AbstractMigration;

class AddServiceSecretColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('services')
             ->addColumn('secret', 'string', array('null' => false))
             ->update();
    }
}
