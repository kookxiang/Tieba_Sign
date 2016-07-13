<?php

use Phinx\Migration\AbstractMigration;

class MemberInit extends AbstractMigration
{
    public function change()
    {
        $this->table('Member', ['comment' => '用户表'])
            ->addColumn('username', 'string', ['limit' => 32])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('email', 'string', ['limit' => 64])
            ->addColumn('role', 'string', ['limit' => 16])
            ->addColumn('registerTime', 'integer')
            ->addColumn('lastActive', 'integer')
            ->create();
    }
}
