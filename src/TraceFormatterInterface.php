<?php
/**
 * Volcanus libraries for PHP 8.1~
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
    public function __invoke(array $stackTrace): string;

    /**
     * スタックトレースを文字列に整形して返します。
     *
     * @param array $stackTrace スタックトレース
     * @return string
     */
    public function arrayToString(array $stackTrace): string;

    /**
     * 1レコード分のトレースを文字列に整形して返します。
     *
     * @param array $trace 1レコード分のトレース
     * @return string
     */
    public function format(array $trace): string;

    /**
     * トレースのファイル情報を文字列に整形して返します。
     *
     * @param string|null $file ファイルパス
     * @param string|null $line 行番号
     * @return string
     */
    public function formatLocation(?string $file, ?string $line): string;

    /**
     * トレースの関数呼び出し情報を文字列に整形して返します。
     *
     * @param string|null $class クラス名
     * @param string|null $type 呼び出し種別
     * @param string|null $function 関数名/メソッド名
     * @return string
     */
    public function formatFunction(?string $class, ?string $type, ?string $function): string;

    /**
     * トレースの関数呼び出しの引数を文字列に整形して返します。
     *
     * @param array|null $arguments 引数の配列
     * @return string
     */
    public function formatArguments(?array $arguments): string;

}
