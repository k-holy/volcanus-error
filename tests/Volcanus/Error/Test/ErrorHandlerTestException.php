<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error\Test;

/**
 * ErrorHandlerTest
 *
 * @author k.holy74@gmail.com
 */
class ErrorHandlerTestException extends \RuntimeException
{
    public function getHttpStatus($code): string
    {
        switch ($code) {
            case 400:
                return '400 Bad Request';
            case 403:
                return '403 Forbidden';
            case 404:
                return '404 Not Found';
            case 405:
                return '405 Method Not Allowed';
        }
        return '500 Internal Server Error';
    }

}
