<?php

namespace Model;

use Core\Database;
use Core\Model;

/** @Table Account */
class Account extends Model
{
    public $id;
    /** @var User */
    public $owner;
    public $name;
    public $cookie;
    public $cookieError = false;
    public $avatar;
    public $bindTime = TIMESTAMP;
    public $tiebaSign = true;
    public $wenkuSign = false;
    public $zhidaoSign = false;
    public $mailSetting = 1;

    protected function lazyLoad()
    {
        $this->setLazyLoad('owner', 'Model\\User::getUserByUserId');
    }

    /**
     * @param $id
     * @return Account
     */
    public static function getAccountById($id)
    {
        $statement = Database::getInstance()->prepare('SELECT * FROM `Account` WHERE id = ?');
        $statement->bindValue(1, $id);
        $statement->execute();
        return $statement->fetchObject(__CLASS__);
    }

    /**
     * @param User $user
     * @return Account
     */
    public static function getAccountsByUser(User $user)
    {
        $statement = Database::getInstance()->prepare('SELECT * FROM `Account` WHERE owner = ?');
        $statement->bindValue(1, $user->id);
        $statement->execute();
        return $statement->fetchAll(Database::FETCH_CLASS, __CLASS__);
    }
}
