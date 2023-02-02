<?php
/**
 * Volcanus libraries for PHP 8.1~
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
        return match ($code) {
            400 => '400 Bad Request',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            405 => '405 Method Not Allowed',
            default => '500 Internal Server Error',
        };
    }

}
