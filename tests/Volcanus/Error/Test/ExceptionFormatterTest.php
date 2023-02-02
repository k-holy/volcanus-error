<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error\Test;

use PHPUnit\Framework\TestCase;
use Volcanus\Error\ExceptionFormatter;

/**
 * ExceptionFormatterTest
 *
 * @author k.holy74@gmail.com
 */
class ExceptionFormatterTest extends TestCase
{

    public function testInvoke()
    {
        $exception = new \RuntimeException('Some Exception');
        $exceptionFormatter = new ExceptionFormatter();
        $this->assertStringEndsWith(
            $exceptionFormatter($exception),
            ExceptionFormatter::format($exception)
        );
    }

    public function testFormat()
    {
        $exception = new \RuntimeException('Some Exception');
        $this->assertStringEndsWith(
            sprintf("%s '%s' in %s on line %u",
                ExceptionFormatter::buildHeader($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ),
            ExceptionFormatter::format($exception)
        );
    }

    public function testBuildHeader()
    {
        $code = 999;
        $exception = new \RuntimeException('Some Exception', $code);
        $this->assertStringEndsWith(
            sprintf('RuntimeException[%d]:', $code),
            ExceptionFormatter::buildHeader($exception)
        );
    }

}
