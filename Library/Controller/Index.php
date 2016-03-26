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

    /**
     * This method can be call by /index/test.json
     * @JSON
     */
    function test()
    {
        return array(
            'hello' => 1,
            'world' => 2
        );
    }
}
