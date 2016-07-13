<?php

namespace Controller;

use Core\Template;
use Helper\Message;
use Model\User;

class Member
{

    /**
     * @Route /Member/Login
     */
    public function login()
    {
        Template::setView('Member/Login');
    }

    /**
     * @Route /Member/Register
     */
    public function register()
    {
        if (!$_GET['invite']) {
            Message::show('Member.Messages.NoInvitation');
        }
        Template::putContext('invite', $_GET['invite']);
        Template::setView('Member/Register');
    }

    /**
     * @JSON
     * @Route /Member/Login.action
     */
    public function doLogin()
    {
        $user = User::getUserByUsername($_POST['username']);
        if (!$user) {
            Message::show('Member.Messages.UserNotExists');
        }
        if (!$user->verifyPassword($_POST['password'])) {
            Message::show('Member.Messages.PasswordError');
        }
        $_SESSION['currentUser'] = $user;
        return array(
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'last_active' => $user->lastActive,
        );
    }
}
