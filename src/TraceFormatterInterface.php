<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * トレースフォーマッタインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface TraceFormatterInterface
{

    /**
     * スタックトレースを文字列に整形して返します。
     *
     * @param array $stackTrace スタックトレース
     * @return string
     */
    public function __invoke(array $stackTrace);

    /**
     * スタックトレースを文字列に整形して返します。
     *
     * @param array $stackTrace スタックトレース
     * @return string
     */
    public function arrayToString(array $stackTrace);

    /**
     * 1レコード分のトレースを文字列に整形して返します。
     *
     * @param array $trace 1レコード分のトレース
     * @return string
     */
    public function format(array $trace);

    /**
     * トレースのファイル情報を文字列に整形して返します。
     *
     * @param string $file ファイルパス
     * @param string $line 行番号
     * @return string
     */
    public function formatLocation($file, $line);

    /**
     * トレースの関数呼び出し情報を文字列に整形して返します。
     *
     * @param string $class クラス名
     * @param string $type 呼び出し種別
     * @param string $function 関数名/メソッド名
     * @return string
     */
    public function formatFunction($class, $type, $function);

    /**
     * トレースの関数呼び出しの引数を文字列に整形して返します。
     *
     * @param array $arguments 引数の配列
     * @return string
     */
    public function formatArguments($arguments);

}
