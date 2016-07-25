<?php

namespace Controller;

use Core\Template;
use Model\Account;
use Model\User;

class Dashboard
{
    /**
     * @RequireLogin
     * @Route /Dashboard/
     * @DynamicRoute /Dashboard/{any}
     */
    public function index()
    {
        Template::setView('Dashboard');
        Template::putContext('account', User::getCurrent()->getDefaultAccount());
        Template::putContext('accounts', Account::getAccountsByUser(User::getCurrent()));
    }
}
