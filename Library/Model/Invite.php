<?php

namespace Model;

use Core\Database;
use Core\Model;

/** @Table Invite */
class Invite extends Model
{
    public $inviteCode = '';
    public $fromUid = 0;
    public $toUid = 0;
    public $createTime = TIMESTAMP;
    public $useTime = 0;

    private static $primaryKey = 'inviteCode';

    const LOCK_TIME = 180;

    public static function create()
    {
        $invite = new self();
        $invite->fromUid = User::getCurrent()->id;
        $statement = Database::sql('INSERT INTO Invite (inviteCode, fromUid, toUid, createTime, useTime) VALUES (uuid(), :fromUid, :toUid, :createTime, :useTime)');
        $statement->bindValue(':fromUid', $invite->fromUid);
        $statement->bindValue(':toUid', $invite->toUid);
        $statement->bindValue(':createTime', $invite->createTime);
        $statement->bindValue(':useTime', $invite->useTime);
        $statement->execute();
        $invite->inviteCode = Database::getInstance()->lastInsertId();
        return $invite;
    }

    /**
     * @param string $code Invite code
     * @return Invite
     */
    public static function getInviteByCode($code = '')
    {
        $statement = Database::getInstance()->prepare('SELECT * FROM `Invite` WHERE inviteCode = ?');
        $statement->bindValue(1, $code);
        $statement->execute();
        return $statement->fetchObject(__CLASS__);
    }

    public function finishRegister(User $user)
    {
        $this->useTime = TIMESTAMP;
        $this->toUid = $user->id;
        $this->save();
    }

    /**
     * Lock invite
     * @return bool
     */
    public function tryLock()
    {
        if ($this->useTime > TIMESTAMP - self::LOCK_TIME) {
            return false;
        }
        $this->useTime = TIMESTAMP;
        $_SESSION['invite'] = $this;
        $this->save();
        return true;
    }

    /**
     * @return Invite
     */
    public static function getLockedInvite()
    {
        /** @var Invite $invite */
        $invite = $_SESSION['invite'];
        if (!$invite) {
            return null;
        }
        if ($invite->useTime < TIMESTAMP - self::LOCK_TIME) {
            // Check if the invite was locked by another user
            $invite = self::getInviteByCode($invite->inviteCode);
            if ($invite && !$invite->toUid && $invite->useTime < TIMESTAMP - self::LOCK_TIME) {
                $invite->useTime = TIMESTAMP;
                $invite->save();
                return $invite;
            } else {
                return null;
            }
        }
        return $invite;
    }
}
