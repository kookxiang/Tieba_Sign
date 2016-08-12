<?php

namespace Controller;

use Core\Database;
use Helper\Message;
use Model\Invite as InviteModel;
use Model\User;

class Invite
{
    /**
     * @ForceJSON
     * @RequireLogin
     * @Route /Invite/Generate.action
     */
    public function generate()
    {
        if (User::getCurrent()->invite == 0) {
            Message::show('您的邀请已用完, 请与管理员取得联系');
        }
        Database::getInstance()->beginTransaction();
        InviteModel::create();
        if (User::getCurrent()->invite > 0) {
            User::getCurrent()->invite--;
            User::getCurrent()->save();
        }
        Database::getInstance()->commit();
    }

    /**
     * @ForceJSON
     * @AdminOnly
     * @DynamicRoute /Invite/{any}/Share.action
     * @param $code string Invite Code
     */
    public function share($code)
    {
        $invite = InviteModel::getInviteByCode($code);
        // TODO: Send to telegram robot
    }

    /**
     * @ForceJSON
     * @RequireLogin
     * @DynamicRoute /Invite/{any}/Delete.action
     * @param $code string Invite Code
     */
    public function delete($code)
    {
        $invite = InviteModel::getInviteByCode($code);
        if (!$invite) {
            Message::show('邀请链接不存在');
        } elseif ($invite->fromUid != User::getCurrent()->id){
            Message::show('您只能删除自己的邀请链接');
        }
        Database::getInstance()->beginTransaction();
        $invite->delete();
        if (User::getCurrent()->invite >= 0) {
            User::getCurrent()->invite++;
            User::getCurrent()->save();
        }
        Database::getInstance()->commit();
    }

    /**
     * @ForceJSON
     * @RequireLogin
     * @Route /Invite/List.action
     */
    public function myInvites()
    {
        $inviteList = InviteModel::getInvitesByUser(User::getCurrent());
        $invites = array();
        foreach ($inviteList as $invite){
            $invites[] = [
                'code' => $invite->inviteCode,
                'link' => BASE_URL.'Member/Register?invite='.$invite->inviteCode,
                'used' => $invite->toUid != 0,
                'generateTime' => date('Y-m-d H:i:s', $invite->createTime),
                'usedTime' => date('Y-m-d H:i:s', $invite->useTime),
            ];
        }
        return [
            'availableInvite' => User::getCurrent()->invite,
            'inviteCount' => count($inviteList),
            'allowShare' => User::getCurrent()->id == 1,
            'invites' => $invites
        ];
    }
}
