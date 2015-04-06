<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

use Volcanus\Error\TraceFormatterInterface;
use Volcanus\Error\Trace;

/**
 * スタックトレースイテレータ
 *
 * @author k.holy74@gmail.com
 */
class StackTraceIterator implements \Iterator, \Countable
{

	/**
	 * @var array スタックトレース
	 */
	private $stackTrace;

	/**
	 * @var TraceFormatterInterface トレースフォーマッタ
	 */
	private $formatter;

	/**
	 * @var int 現在のイテレーション位置
	 */
	private $position;

	/**
	 * コンストラクタ
	 *
	 * @param TraceFormatterInterface トレースフォーマッタ
	 */
	public function __construct(TraceFormatterInterface $formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * オブジェクトを初期化します。
	 *
	 * @param array スタックトレース
	 * @return $this
	 */
	public function initialize(array $stackTrace = array())
	{
		$this->position = 0;
		if (!empty($stackTrace)) {
			$this->stackTrace = $stackTrace;
		}
		return $this;
	}

	/**
	 * Iterator::rewind()
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Iterator::current()
	 *
	 * @return array
	 */
	public function current()
	{
		return new Trace($this->formatter, $this->stackTrace[$this->position]);
	}

	/**
	 * Iterator::key()
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Iterator::next()
	 */
	public function next()
	{
		$this->position++;
	}

	/**
	 * Iterator::valid()
	 *
	 * @return bool
	 */
	public function valid()
	{
		return isset($this->stackTrace[$this->position]);
	}

	/**
	 * Countable::count()
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->stackTrace);
	}

	/**
	 * スタックトレースを文字列に整形して返します。
	 *
	 * @return string
	 */
	public function __toString()
	{
		$stackTrace = array();
		foreach ($this->stackTrace as $index => $trace) {
			$stackTrace[] = sprintf('#%d %s', $index, $this->formatter->format($trace));
		}
		return implode("\n", $stackTrace);
	}

}
