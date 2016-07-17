<?php

use Phinx\Migration\AbstractMigration;

class AccountInit extends AbstractMigration
{
    public function change()
    {
        $this->table('Account', ['comment' => '账户表'])
            ->addColumn('owner', 'integer')
            ->addColumn('name', 'string', ['limit' => 32])
            ->addColumn('cookie', 'text')
            ->addColumn('avatar', 'string', ['limit' => 128])
            ->addColumn('bindTime', 'integer')
            ->create();
    }
}
