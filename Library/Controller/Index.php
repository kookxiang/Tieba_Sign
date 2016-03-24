<?php
/**
 * KK-Framework
 * Author: kookxiang <r18@ikk.me>
 */

namespace Controller;

use Core\Template;

class Index
{
    function index()
    {
        include Template::load('Demo');
    }
}