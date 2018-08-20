<?php


use Phinx\Migration\AbstractMigration;

class AddUserTable extends AbstractMigration
{
    public function change()
    {
        $this->table('users')
             ->addColumn('username', 'string', array('null' => false))
             ->addColumn('email', 'string', array('null' => false))
             ->addIndex('username')
             ->addIndex('email')
             ->create();
    }
}
