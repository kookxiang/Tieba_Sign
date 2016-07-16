<?php

namespace Controller;

use Core\Template;

class Dashboard
{
    /**
     * @Route /Dashboard/
     * @DynamicRoute /Dashboard/{any}
     */
    public function index()
    {
        Template::setView('Dashboard');
    }
}
