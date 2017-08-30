<?php
/**
 * Volcanus libraries for PHP
 *
 * @copyright k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Volcanus\Error;

/**
 * エラーフォーマッタインタフェース
 *
 * @author k.holy74@gmail.com
 */
interface ErrorFormatterInterface
{

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int エラーレベル
     * @param string エラーメッセージ
     * @param string エラー発生元ファイル
     * @param string エラー発生元ファイルの行番号
     * @return string
     */
    public function __invoke($errno, $errstr, $errfile, $errline);

    /**
     * エラー情報を文字列に整形して返します。
     *
     * @param int エラーレベル
     * @param string エラーメッセージ
     * @param string エラー発生元ファイル
     * @param string エラー発生元ファイルの行番号
     * @return string
     */
    public function format($errno, $errstr, $errfile, $errline);

    /**
     * PHPエラーレベル別にエラーメッセージ用のヘッダを生成して返します。
     *
     * @param int エラーレベル
     * @return string
     */
    public function buildHeader($errno);

}
