<?php
/**
 * KK-Framework
 * Author: kookxiang <r18@ikk.me>
 */

namespace Helper;

use Core\Error;
use Core\IFilter;
use ReflectionMethod;

class JSON implements IFilter
{
    protected static $statusCode = 200;
    protected $handle = false;

    public static function setStatusCode($statusCode)
    {
        self::$statusCode = $statusCode;
    }

    public function preRender(&$context)
    {
        if ($this->handle) {
            header('Content-type: application/json');
            if ($context instanceof Error) {
                echo json_encode(array(
                    'code' => $context->getCode() ?: 500,
                    'data' => null,
                    'hasError' => true,
                    'message' => $context->getMessage(),
                ));
            } else {
                echo json_encode(array(
                    'code' => self::$statusCode,
                    'data' => $context,
                ));
            }
            exit();
        }
    }

    public function preRoute(&$path)
    {
        if (substr($path, -5) == '.json') {
            $path = substr($path, 0, -5);
            $this->handle = true;
        }
    }

    public function afterRoute(&$className, &$method)
    {
        if ($this->handle) {
            // Check if method allow json output
            $reflection = new ReflectionMethod($className, $method);
            $docComment = $reflection->getDocComment();
            if (strpos($docComment, '@JSON') === false) {
                throw new Error('The request URL is not available', 403);
            }
        }
    }

    public function afterRender()
    {
        // Do nothing.
    }

    public function redirect(&$targetUrl)
    {
        echo json_encode(array(
            'code' => 302,
            'data' => null,
            'hasError' => true,
            'message' => 'JSON request has been redirected',
            'target' => $targetUrl
        ));
        exit();
    }
}
