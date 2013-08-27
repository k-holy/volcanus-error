<?php
/**
 * PHP versions 5
 *
 * @copyright  2011 k-holy <k.holy74@gmail.com>
 * @author     k.holy74@gmail.com
 * @license    http://www.opensource.org/licenses/mit-license.php  The MIT License (MIT)
 */
namespace Volcanus\Error\Tests;

use Volcanus\Error\ErrorHandler;

/**
 * ErrorHandlerTest
 *
 * @author     k.holy74@gmail.com
 */
class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{

	private $error;
	private $log_results;
	private $old_error_reporting;

	public function setUp()
	{
		$this->old_error_reporting = error_reporting();
		error_reporting(-1);
		$this->error = ErrorHandler::instance();
		$this->error->init();
		$this->log_results = array();
	}

	public function tearDown()
	{
		$this->error->clearBuffer();
		error_reporting($this->old_error_reporting);
	}

	public function testCreateNewInstance()
	{
		$this->assertNotSame($this->error, ErrorHandler::instance());
	}

	public function testDefaultInit()
	{
		$this->error->init();
		$this->assertEquals($this->error->getOption('output_encoding'), mb_internal_encoding());
		$this->assertEquals($this->error->getOption('log_level'      ), ErrorHandler::LEVEL_ALL);
		$this->assertEquals($this->error->getOption('display_level'  ), ErrorHandler::LEVEL_ALL);
		$this->assertEquals($this->error->getOption('forward_level'  ), ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR);
		$this->assertTrue($this->error->getOption('display_html'));
		$this->assertFalse($this->error->getOption('display_buffering'));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testRaiseRuntimeExceptionWhenGetUnsupportedOption()
	{
		$this->error->getOption('FOO');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testRaiseRuntimeExceptionWhenSetUnsupportedOption()
	{
		$this->error->setOption('FOO', 'BAR');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRaiseInvalidArgumentExceptionWhenSetMessageFormatterIsNotCallable()
	{
		$this->error->setMessageFormatter(array());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRaiseInvalidArgumentExceptionWhenSetTraceFormatterIsNotCallable()
	{
		$this->error->setTraceFormatter(array());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRaiseInvalidArgumentExceptionWhenSetLoggerIsNotCallable()
	{
		$this->error->setLogger(array());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRaiseInvalidArgumentExceptionWhenSetDisplayIsNotCallable()
	{
		$this->error->setDisplay(array());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRaiseInvalidArgumentExceptionWhenSetForwardIsNotCallable()
	{
		$this->error->setForward(array());
	}

	public function testLogger()
	{
		$this->error->setLogger($this->logger());
		$message = 'ERR';
		$this->error->log($message);
		$this->assertEquals($this->log_results[0], $message);
	}

	public function testLogLevel()
	{
		$this->error->setLogger($this->logger());
		$message = 'ERR';

		$this->error->setOption('log_level', ErrorHandler::LEVEL_NONE);

		$this->error->log($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEmpty($this->log_results);

		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION);
		$this->error->log($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEquals($this->log_results[0], $message);
		$this->log_results = array();

		$this->error->log($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEmpty($this->log_results);

		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR);
		$this->error->log($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEquals($this->log_results[0], $message);
		$this->log_results = array();

		$this->error->log($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEmpty($this->log_results);

		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING);
		$this->error->log($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEquals($this->log_results[0], $message);
		$this->log_results = array();

		$this->error->log($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEmpty($this->log_results);

		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE);
		$this->error->log($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEquals($this->log_results[0], $message);
		$this->log_results = array();

		$this->error->log($message, ErrorHandler::LEVEL_INFO);
		$this->assertEmpty($this->log_results);

		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE | ErrorHandler::LEVEL_INFO);
		$this->error->log($message, ErrorHandler::LEVEL_INFO);
		$this->assertEquals($this->log_results[0], $message);
		$this->log_results = array();
	}

	public function testDisplayLevel()
	{
		$this->error->setDisplay($this->display());
		$message = 'ERR';

		$this->error->setOption('display_level', ErrorHandler::LEVEL_NONE);

		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION);
		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR);
		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING);
		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE);
		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_INFO);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE | ErrorHandler::LEVEL_INFO);
		ob_start();
		$this->error->display($message, ErrorHandler::LEVEL_INFO);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

	}

	public function testForwardLevel()
	{
		$this->error->setForward($this->forward());
		$message = 'ERR';

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_NONE);

		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION);
		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_EXCEPTION);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR);
		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_ERROR);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING);
		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_WARNING);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE);
		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_NOTICE);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();

		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_INFO);
		$this->assertEmpty(ob_get_contents());
		ob_end_clean();

		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION | ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE | ErrorHandler::LEVEL_INFO);
		ob_start();
		$this->error->forward($message, ErrorHandler::LEVEL_INFO);
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();
	}

	public function testDisplayBuffering()
	{
		$this->error->setOption('display_buffering', true);
		$this->error->setDisplay($this->display());
		$this->error->display('ERR', ErrorHandler::LEVEL_EXCEPTION);
		$this->assertNotEmpty($this->error->getBuffer());
		$this->error->clearBuffer();
		$this->assertEmpty($this->error->getBuffer());
	}

	public function testFlushBuffer()
	{
		$this->error->setOption('display_buffering', true);
		$this->error->setDisplay($this->display());
		$message = 'ERR';
		$this->error->display($message, ErrorHandler::LEVEL_EXCEPTION);
		ob_start();
		$this->error->flushBuffer();
		$this->assertEquals(ob_get_contents(), $message);
		ob_end_clean();
	}

	public function testBuildErrorHeader()
	{
		$error_levels = array(
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
		);
		foreach ($error_levels as $error_level) {
			$this->assertStringEndsWith(sprintf('[%d]:', $error_level),
				$this->error->buildErrorHeader($error_level));
		}
		$this->assertStringEndsWith('[0]:', $this->error->buildErrorHeader(0));
	}

	public function testBuildExceptionHeader()
	{
		$code = 999;
		$exception = new \Exception('ERR', $code);
		$this->assertStringEndsWith(sprintf('%s[%d]:', get_class($exception), $code),
			$this->error->buildExceptionHeader($exception));
	}

	public function testGetErrorHandler()
	{
		$this->assertTrue(is_callable($this->error->getErrorHandler()));
	}

	public function testGetExceptionHandler()
	{
		$this->assertTrue(is_callable($this->error->getExceptionHandler()));
	}

	public function testErrorHandler()
	{
		$this->error->setOption('log_level'    , ErrorHandler::LEVEL_NONE);
		$this->error->setOption('display_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('forward_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('display_buffering', true);
		set_error_handler($this->error->getErrorHandler());

		trigger_error('ERR', E_USER_ERROR);
		$this->assertEmpty($this->error->getBuffer());

		$this->error->setOption('display_level', ErrorHandler::LEVEL_ERROR);
		trigger_error('ERR', E_USER_ERROR);
		$this->assertNotEmpty($this->error->getBuffer());
		$this->error->clearBuffer();

		trigger_error('ERR', E_USER_WARNING);
		$this->assertEmpty($this->error->getBuffer());

		$this->error->setOption('display_level', ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING);
		trigger_error('ERR', E_USER_WARNING);
		$this->assertNotEmpty($this->error->getBuffer());
		$this->error->clearBuffer();

		trigger_error('ERR', E_USER_NOTICE);
		$this->assertEmpty($this->error->getBuffer());

		$this->error->setOption('display_level', ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE);
		trigger_error('ERR', E_USER_NOTICE);
		$this->assertNotEmpty($this->error->getBuffer());
		$this->error->clearBuffer();

		trigger_error('ERR', E_USER_DEPRECATED);
		$this->assertEmpty($this->error->getBuffer());

		$this->error->setOption('display_level', ErrorHandler::LEVEL_ERROR | ErrorHandler::LEVEL_WARNING | ErrorHandler::LEVEL_NOTICE | ErrorHandler::LEVEL_INFO);
		trigger_error('ERR', E_USER_DEPRECATED);
		$this->assertNotEmpty($this->error->getBuffer());
		$this->error->clearBuffer();

	}


	public function testLogWithException()
	{
		$log_results = array();
		$this->error->setLogger(function($message, $exception = null) use (&$log_results) {
			$log_results[] = sprintf('%s: %s', $message,
				(isset($exception) && ($exception instanceof ErrorHandlerTestException))
					? $exception->getHttpStatus($exception->getCode())
					: '500 Internal Server Error'
			);
		});
		$this->error->setOption('log_level', ErrorHandler::LEVEL_EXCEPTION);
		$this->error->log('HttpError', ErrorHandler::LEVEL_EXCEPTION,
			new ErrorHandlerTestException('HttpError', 404));
		$this->assertEquals($log_results[0], 'HttpError: 404 Not Found');
	}

	public function testDisplayWithException()
	{
		$log_results = array();
		$this->error->setDisplay(function($message, $exception = null) {
			echo sprintf('%s: %s', $message,
				(isset($exception) && ($exception instanceof ErrorHandlerTestException))
					? $exception->getHttpStatus($exception->getCode())
					: '500 Internal Server Error'
			);
		});
		$this->error->setOption('display_level', ErrorHandler::LEVEL_EXCEPTION);
		ob_start();
		$this->error->display('HttpError', ErrorHandler::LEVEL_EXCEPTION,
			new ErrorHandlerTestException('HttpError', 404));
		$this->assertEquals(ob_get_contents(), 'HttpError: 404 Not Found');
		ob_end_clean();
	}

	public function testForwardWithException()
	{
		$log_results = array();
		$this->error->setForward(function($message, $exception = null) {
			echo sprintf('%s: %s', $message,
				(isset($exception) && ($exception instanceof ErrorHandlerTestException))
					? $exception->getHttpStatus($exception->getCode())
					: '500 Internal Server Error'
			);
		});
		$this->error->setOption('forward_level', ErrorHandler::LEVEL_EXCEPTION);
		ob_start();
		$this->error->forward('HttpError', ErrorHandler::LEVEL_EXCEPTION,
			new ErrorHandlerTestException('HttpError', 404));
		$this->assertEquals(ob_get_contents(), 'HttpError: 404 Not Found');
		ob_end_clean();
	}

	public function testHandleError()
	{
		$this->error->setLogger($this->logger());
		$message = 'ERR';
		$this->error->setOption('display_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('forward_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('log_level'    , ErrorHandler::LEVEL_ALL);

		$this->error->handleError(E_NOTICE, $message, __FILE__, __LINE__, null);
		$this->assertNotEmpty($this->log_results);
	}

	public function testHandleErrorIsAffectedFromErrorReporting()
	{
		$this->error->setLogger($this->logger());
		$message = 'ERR';
		$this->error->setOption('display_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('forward_level', ErrorHandler::LEVEL_NONE);
		$this->error->setOption('log_level'    , ErrorHandler::LEVEL_ALL);

		error_reporting(E_ALL & ~E_NOTICE);
		$this->error->handleError(E_NOTICE, $message, __FILE__, __LINE__, null);
		$this->assertEmpty($this->log_results);

		error_reporting(E_ALL);
		$this->error->handleError(E_NOTICE, $message, __FILE__, __LINE__, null);
		$this->assertNotEmpty($this->log_results);
	}

	public function testFormatException()
	{
		$message_formatter = $this->messageFormatter();
		$trace_formatter = $this->traceFormatter();
		$this->error->setMessageFormatter($message_formatter);
		$this->error->setTraceFormatter($trace_formatter);
		$exception = new \Exception('Now you die!');
		$this->assertEquals(
			$this->error->formatException($exception),
			$message_formatter(
				$this->error->buildExceptionHeader($exception),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine(),
				$trace_formatter($exception->getTrace())
			)
		);
	}

	public function testFormatError()
	{
		$message_formatter = $this->messageFormatter();
		$trace_formatter = $this->traceFormatter();
		$this->error->setMessageFormatter($message_formatter);
		$this->error->setTraceFormatter($trace_formatter);
		$errno   = E_USER_WARNING;
		$errstr  = 'ERR';
		$errfile = __FILE__;
		$errline = __LINE__;
		$trace   = debug_backtrace();
		$this->assertEquals(
			$this->error->formatError(
				$errno,
				$errstr,
				$errfile,
				$errline,
				$trace
			),
			$message_formatter(
				$this->error->buildErrorHeader($errno),
				$errstr,
				$errfile,
				$errline,
				$trace_formatter($trace)
			)
		);
	}

	private function traceFormatter()
	{
		return function ($trace) {
			$formatted_trace = array();
			foreach ($trace as $i => $t) {
				$formatted_trace[] = sprintf('#%d %s(%d): %s%s%s(%s)',
					$i,
					(isset($t['file'    ])) ? $t['file'    ] : '',
					(isset($t['line'    ])) ? $t['line'    ] : '',
					(isset($t['class'   ])) ? $t['class'   ] : '',
					(isset($t['type'    ])) ? $t['type'    ] : '',
					(isset($t['function'])) ? $t['function'] : '',
					(is_object($t['args'])) ? get_class($t['args']) : gettype($t['args']));
			}
			return (count($formatted_trace) >= 1)
				? sprintf("\nStack trace:\n%s", implode("\n", $formatted_trace))
				: '';
		};
	}

	private function errorFormatter()
	{
		return function ($errno, $errstr, $errfile, $errline) {
			return sprintf('[%d] %s in %s on line %d', $errno, $errstr, $errfile, $errline);
		};
	}

	private function exceptionFormatter()
	{
		return function (\Exception $e) {
			return sprintf('[%d] %s in %s on line %d', $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		};
	}

	private function messageFormatter()
	{
		return function ($header, $message, $file, $line, $trace) {
			return sprintf('%s %s in %s on line %d%s', $header, $message, $file, $line, $trace);
		};
	}

	private function logger()
	{
		$log_results = &$this->log_results;
		return function($message) use (&$log_results) {
			$log_results[] = $message;
		};
	}

	private function display()
	{
		return function($message) {
			echo $message;
		};
	}

	private function forward()
	{
		return function($message) {
			echo $message;
		};
	}

}

class ErrorHandlerTestException extends \RuntimeException
{
	public function getHttpStatus($code)
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
