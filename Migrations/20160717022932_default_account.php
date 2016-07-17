<?php

use Phinx\Migration\AbstractMigration;

class DefaultAccount extends AbstractMigration
{
    public function change()
    {
        $this->table('Member')
            ->addColumn('defaultAccount', 'integer')
            ->update();
    }
}
