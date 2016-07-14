<?php

use Phinx\Migration\AbstractMigration;

class InviteInit extends AbstractMigration
{
    public function up()
    {
        $this->query('CREATE TABLE Invite (
          inviteCode CHAR(36) PRIMARY KEY    NOT NULL,
          fromUid    INT                     NOT NULL,
          toUid      INT DEFAULT 0           NOT NULL,
          createTime INT                     NOT NULL,
          useTime    INT                     NOT NULL
        ) ENGINE = MyISAM DEFAULT CHARSET = utf8');
    }

    public function down()
    {
        $this->query('DROP TABLE Invite');
    }
}
