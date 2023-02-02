<?php
/**
 * Volcanus libraries for PHP 8.1~
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error\Test;

use PHPUnit\Framework\TestCase;
use Volcanus\Error\ErrorFormatter;

/**
 * ErrorFormatterTest
 *
 * @author k.holy74@gmail.com
 */
class ErrorFormatterTest extends TestCase
{

    public function testInvoke()
    {
        $errorFormatter = new ErrorFormatter();
        $errorLevel = E_USER_ERROR;
        $errorMessage = 'Test';
        $errorFile = __FILE__;
        $errorLine = __LINE__;
        $this->assertStringEndsWith(
            $errorFormatter(
                $errorLevel,
                $errorMessage,
                $errorFile,
                $errorLine
            ),
            ErrorFormatter::format(
                $errorLevel,
                $errorMessage,
                $errorFile,
                $errorLine
            )
        );
    }

    public function testFormat()
    {
        $errorLevel = E_USER_ERROR;
        $errorMessage = 'Test';
        $errorFile = __FILE__;
        $errorLine = __LINE__;
        $this->assertStringEndsWith(
            sprintf("%s '%s' in %s on line %u",
                ErrorFormatter::buildHeader($errorLevel),
                $errorMessage,
                $errorFile,
                $errorLine
            ),
            ErrorFormatter::format(
                $errorLevel,
                $errorMessage,
                $errorFile,
                $errorLine
            )
        );
    }

    public function testBuildHeader()
    {
        $errorLevels = [
            E_ERROR,
            E_WARNING,
            E_NOTICE,
            E_STRICT,
            E_RECOVERABLE_ERROR,
            E_DEPRECATED,
            E_USER_ERROR,
            E_USER_WARNING,
            E_USER_NOTICE,
            E_USER_DEPRECATED,
        ];
        foreach ($errorLevels as $errorLevel) {
            $this->assertStringEndsWith(
                sprintf('[%d]:', $errorLevel),
                ErrorFormatter::buildHeader($errorLevel)
            );
        }
        $this->assertStringEndsWith('[0]:', ErrorFormatter::buildHeader(0));
    }

}
