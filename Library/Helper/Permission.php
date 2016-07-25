<?php

namespace Helper;

use Core\IFilter;
use Helper\Reflection as ReflectionHelper;
use Model\User;
use ReflectionMethod;

class Permission implements IFilter
{
    private static function requireLogin()
    {
        if (!User::getCurrent()) {
            Message::show('Member.Messages.RequireLogin', 'Member/Login');
        }
    }

    private static function adminOnly()
    {
        self::requireLogin();
        if (User::getCurrent()->role != User::ROLE_ADMIN) {
            Message::show('Member.Messages.RequireAdmin', 'Member/Login');
        }
    }

    public function preRoute(&$path)
    {
    }

    public function afterRoute(&$className, &$method)
    {
        // Check if method allow json output
        $reflection = new ReflectionMethod($className, $method);
        $markers = ReflectionHelper::parseDocComment($reflection);
        if ($markers['RequireLogin']) {
            self::requireLogin();
        }
        if ($markers['AdminOnly']) {
            self::adminOnly();
        }
    }

    public function preRender()
    {
    }

    public function afterRender()
    {
    }

    public function redirect(&$targetUrl)
    {
    }
}
