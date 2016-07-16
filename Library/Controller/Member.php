<?php

namespace Controller;

use Core\Template;
use Helper\Message;
use Model\Invite;
use Model\User;

class Member
{

    /**
     * @Route /Member/Login
     */
    public function login()
    {
        if (User::getCurrent()) {
            Message::show('Member.Messages.AlreadyLogin', '/Dashboard');
        }
        Template::setView('Member/Login');
    }

    /**
     * @Route /Member/Register
     */
    public function register()
    {
        if (User::getCurrent()) {
            Message::show('Member.Messages.AlreadyLogin', '/Dashboard');
        }
        if (!$_GET['invite']) {
            Message::show('Member.Messages.NoInvitation');
        }
        $invite = Invite::getInviteByCode($_GET['invite']);
        if (!$invite) {
            Message::show('Member.Messages.IllegalInvitation');
        }
        if ($invite->toUid) {
            Message::show('Member.Messages.InvitationUsed');
        }
        if (!$invite->tryLock()) {
            Message::show('Member.Messages.InvitationLocked');
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
            'last_active' => $user->lastActive,
        );
    }

    /**
     * @JSON
     * @Route /Member/Register.action
     */
    public function doRegister()
    {
        if (!$_POST['invite']) {
            Message::show('Member.Messages.NoInvitation');
        }
        $invite = Invite::getLockedInvite();
        if (!$invite || $invite->inviteCode != $_POST['invite']) {
            unset($_SESSION['invite']);
            Message::show('Member.Messages.IllegalInvitation');
        }
        if (strlen($_POST['username']) < 4) {
            Message::show('Member.Messages.UsernameTooShort');
        }
        if (User::getUserByUsername($_POST['username'])) {
            Message::show('Member.Messages.UserAlreadyExists');
        }
        if (!$_POST['password']) {
            Message::show('Member.Messages.NoPassword');
        }
        if (!$_POST['email']) {
            Message::show('Member.Messages.NoEmail');
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            Message::show('Member.Messages.IllegalEmail');
        }
        if (preg_match('/(qq|foxmail)\.com$/i', $_POST['email'])) {
            Message::show('Member.Messages.NotSupportedEmail');
        }
        $user = new User();
        $user->username = $_POST['username'];
        $user->setPassword($_POST['password']);
        $user->email = $_POST['email'];
        $user->save();
        $invite->finishRegister($user);
        unset($_SESSION['invite']);
        $_SESSION['currentUser'] = $user;
        return array(
            'id' => $user->id,
            'username' => $user->username
        );
    }
}
