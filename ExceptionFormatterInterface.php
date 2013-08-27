<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright 2011-2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * 例外フォーマッタインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface ExceptionFormatterInterface
{

	/**
	 * 例外オブジェクトを文字列に整形して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function __invoke(\Exception $e);

	/**
	 * 例外オブジェクトを文字列に整形して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function format(\Exception $e);

	/**
	 * 例外からエラーメッセージ用のヘッダを生成して返します。
	 *
	 * @param \Exception 例外オブジェクト
	 * @return string
	 */
	public function buildHeader(\Exception $e);

}
