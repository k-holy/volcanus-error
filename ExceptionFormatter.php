<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * 例外フォーマッタ
 *
 * @author k.holy74@gmail.com
 */
class ExceptionFormatter implements ExceptionFormatterInterface
{

	/**
	 * 例外オブジェクトを文字列に整形して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function __invoke(\Exception $e)
	{
		return $this->format($e);
	}

	/**
	 * 例外オブジェクトを文字列に整形して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function format(\Exception $e)
	{
		return sprintf("%s '%s' in %s on line %u",
			$this->buildHeader($errno),
			$e->getMessage(),
			$e->getFile(),
			$e->getLine()
		);
	}

	/**
	 * 例外からエラーメッセージ用のヘッダを生成して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function buildHeader(\Exception $e)
	{
		return sprintf("Uncaught Exception %s[%d]:",
			get_class($e),
			$e->getCode()
		);
	}

}
