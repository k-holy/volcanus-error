<?php
/**
 * PHP versions 5
 *
 * @copyright  2012 k-holy <k.holy74@gmail.com>
 * @author     k.holy74@gmail.com
 * @license    http://www.opensource.org/licenses/mit-license.php  The MIT License (MIT)
 */
namespace Volcanus\Error;

/**
 * TraceFormatter
 *
 * @author     k.holy74@gmail.com
 */
class TraceFormatter
{

	/**
	 * スタックトレースの配列を文字列に整形して返します。
	 *
	 * @param array stackTrace
	 * @return string
	 */
	public function format(array $trace)
	{
		if (count($trace) === 0) {
			return '';
		}
		$trace_formatted = array();
		foreach ($trace as $i => $t) {
			$args = '';
			if (isset($t['args']) && !empty($t['args'])) {
				$self = $this;
				$args = implode(', ', array_map(function($arg) use ($self) {
					if (is_array($arg)) {
						$vars = array();
						foreach ($arg as $key => $var) {
							$vars[] = sprintf('%s=>%s',
								$self->formatVar($key), $self->formatVar($var));
						}
						return sprintf('Array[%s]', implode(', ', $vars));
					}
					return $self->formatVar($arg);
				}, $t['args']));
			}
			$trace_formatted[] = sprintf('#%d %s(%d): %s%s%s(%s)',
				$i,
				(isset($t['file'    ])) ? $t['file'    ] : '',
				(isset($t['line'    ])) ? $t['line'    ] : '',
				(isset($t['class'   ])) ? $t['class'   ] : '',
				(isset($t['type'    ])) ? $t['type'    ] : '',
				(isset($t['function'])) ? $t['function'] : '',
				$args);
		}
		return sprintf("\nStack trace:\n%s", implode("\n", $trace_formatted));
	}

	/**
	 * 変数の型の文字列表現を返します。
	 *
	 * @param mixed
	 * @return string
	 */
	public function formatVar($var)
	{
		if (is_null($var)) {
			return 'NULL';
		}
		if (is_int($var)) {
			return sprintf('Int(%d)', $var);
		}
		if (is_float($var)) {
			return sprintf('Float(%F)', $var);
		}
		if (is_string($var)) {
			return sprintf('"%s"', $var);
		}
		if (is_bool($var)) {
			return sprintf('Bool(%s)', $var ? 'true' : 'false');
		}
		if (is_array($var)) {
			return 'Array';
		}
		if (is_object($var)) {
			return sprintf('Object(%s)', get_class($var), $var);
		}
		return sprintf('%s', gettype($var));
	}

	public function __invoke(array $trace)
	{
		return $this->format($trace);
	}

}
